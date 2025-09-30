<?php

namespace App\Services\Bottle;

use App\DTOs\Bottle\BottleStatsDTO;
use App\Enums\BottleStatus;
use App\Events\BottleStatusUpdatedEvent;
use App\Models\Bottle;
use App\Repositories\Contracts\BottleMovementRepositoryInterface;
use App\Repositories\Contracts\BottleRepositoryInterface;
use App\Repositories\Contracts\OrderBottleScanRepositoryInterface;
use App\Services\BaseServiceWithMedia;
use App\Services\Shared\Media\MediaServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BottleService extends BaseServiceWithMedia
{
    public function __construct(
        private BottleRepositoryInterface $bottleRepository,
        private BottleMovementRepositoryInterface $bottleMovementRepository,
        protected MediaServiceInterface $mediaService,
        private OrderBottleScanRepositoryInterface $orderBottleScanRepository
    ) {
        parent::__construct($bottleRepository, $mediaService);
    }

    public function checkBottleStatusByBarcode(string $barcode): array
    {
        // TODO Remove comment after mobile test
        return ['authentic' => true];

        $bottle = $this->bottleRepository->findByBarcodeAndStatus(
            $barcode,
            [BottleStatus::WITH_DELIVERY_PERSON()]
        );

        if (! $bottle || ! $bottle->is_filled) {
            return ['authentic' => false];
        }

        $orderBottleScan = $this->orderBottleScanRepository
            ->getLatestOrderBottleScanForBottle($bottle->id);

        $order = $orderBottleScan->orderItem->order ?? null;

        return [
            'authentic' => $order &&
                        $bottle->distribution_center_id === $order->distribution_center_id,
        ];
    }

    public function getBottleHistory($bottleId): Collection
    {
        return $this->bottleRepository->getBottleHistory($bottleId);
    }

    /**
     * Update a bottle with the provided attributes
     *
     * @param  Bottle  $bottle  The bottle to update
     * @param  array  $attributes  The attributes to update
     * @return Bottle The updated bottle
     */
    public function update(Model $bottle, array $attributes): Model
    {
        if (! $bottle instanceof Bottle) {
            throw new \InvalidArgumentException('Expected Bottle model');
        }

        if (empty($attributes)) {
            return $bottle;
        }

        $originalStatus = $bottle->status;
        $hasStatusChange = isset($attributes['status']) && $originalStatus != $attributes['status'];

        DB::beginTransaction();

        try {
            if (isset($attributes['image']) && $attributes['image'] instanceof \Illuminate\Http\UploadedFile) {
                /** @var Bottle $updatedBottle */
                $updatedBottle = parent::updateWithMedia($bottle, $attributes);
            } else {
                /** @var Bottle $updatedBottle */
                $updatedBottle = parent::update($bottle, $attributes);
            }

            if ($hasStatusChange && $updatedBottle) {
                event(new BottleStatusUpdatedEvent(
                    bottle: $updatedBottle,
                    status: $updatedBottle->status,
                    userId: auth()->id()
                ));
            }

            DB::commit();

            return $updatedBottle;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Bottle update failed', [
                'message' => $e->getMessage(),
                'bottle_id' => $bottle->id,
                'attributes' => $attributes,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Get bottle statistics
     *
     * @param  string|null  $startDate  Start date for filtering
     * @param  string|null  $endDate  End date for filtering
     * @param  array  $distributionCenterIds  Distribution center IDs to filter by
     */
    public function getStats(?string $startDate = null, ?string $endDate = null, array $distributionCenterIds = []): BottleStatsDTO
    {
        $startDateCarbon = $startDate ? Carbon::parse($startDate) : null;
        $endDateCarbon = $endDate ? Carbon::parse($endDate) : null;

        $inStock = $this->bottleRepository->countInStockBottles($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $withDeliveryPerson = $this->bottleRepository->countWithDeliveryPersonBottles($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $withClient = $this->bottleRepository->countWithClientBottles($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $lostStolen = $this->bottleRepository->countLostStolenBottles($startDateCarbon, $endDateCarbon, $distributionCenterIds);

        return new BottleStatsDTO(
            inStock: $inStock,
            withDeliveryPerson: $withDeliveryPerson,
            withClient: $withClient,
            lostStolen: $lostStolen
        );
    }

    protected function getMediaFields(): array
    {
        return ['image'];
    }

    protected function getModel(): string
    {
        return Bottle::class;
    }
}
