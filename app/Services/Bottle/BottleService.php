<?php

namespace App\Services\Bottle;

use App\DTOs\Bottle\BottleStatsDTO;
use App\Enums\BottleStatus;
use App\Events\BottleStatusUpdatedEvent;
use App\Models\Bottle;
use App\Repositories\Contracts\BottleMovementRepositoryInterface;
use App\Repositories\Contracts\BottleRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class BottleService
{
    public function __construct(
        private BottleRepositoryInterface $bottleRepository,
        private BottleMovementRepositoryInterface $bottleMovementRepository,
    ) {}

    /**
     * Find a bottle by its ID
     */
    public function find(int $bottleId): ?Bottle
    {
        /** @var Bottle|null */
        return $this->bottleRepository->find($bottleId);
    }

    public function getBottleHistory($bottleId): Collection
    {
        return $this->bottleRepository->getBottleHistory($bottleId);
    }

    public function updateStatus($bottleId, BottleStatus $status): void
    {
        $this->bottleRepository->updateStatus($bottleId, $status);
        $bottle = $this->bottleRepository->find($bottleId);
        if ($bottle) {
            event(new BottleStatusUpdatedEvent(
                bottle: $bottle,
                status: $status,
                userId: auth()->id()
            ));
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
}
