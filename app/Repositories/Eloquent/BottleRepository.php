<?php

namespace App\Repositories\Eloquent;

use App\Enums\BottleStatus;
use App\Models\Bottle;
use App\Models\BottleMovement;
use App\Repositories\Contracts\BottleRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BottleRepository extends BaseEloquentRepository implements BottleRepositoryInterface
{
    public function __construct(Bottle $bottle)
    {
        $this->model = $bottle;
    }

    public function getBottleHistory($bottleId): Collection
    {
        return BottleMovement::where('bottle_id', $bottleId)
            ->with(['bottle', 'distributionCenter', 'deliveryPerson.user',
                'customer', 'user'])
            ->select('bottle_id', 'distribution_center_id', 'delivery_person_id',
                'customer_id', 'user_id', 'movement_date', 'notes', 'type', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updateStatus($bottleId, BottleStatus $status): void
    {
        $bottle = Bottle::find($bottleId);

        if (! $bottle) {
            throw new ModelNotFoundException("Bottle with ID {$bottleId} not found.");
        }
        $bottle->update(['status' => $status->value]);
    }

    /**
     * Create a base query builder with common filters
     */
    private function createBaseStatsQuery(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): Builder
    {
        $query = Bottle::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                $startDate->startOfDay(),
                $endDate->endOfDay(),
            ]);
        }

        if ($distributionCenterIds && count($distributionCenterIds) > 0) {
            $query->whereIn('distribution_center_id', $distributionCenterIds);
        }

        return $query;
    }

    /**
     * Count bottles with in_stock status
     * This count is independent of date range as it reflects current status
     */
    public function countInStockBottles(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): int
    {
        $query = Bottle::query();

        if (! empty($distributionCenterIds)) {
            $query->whereIn('distribution_center_id', $distributionCenterIds);
        }

        return $query->where('status', BottleStatus::IN_STOCK()->value)->count();
    }

    /**
     * Count bottles with with_delivery_person status
     */
    public function countWithDeliveryPersonBottles(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): int
    {
        return $this->createBaseStatsQuery($startDate, $endDate, $distributionCenterIds)
            ->where('status', BottleStatus::WITH_DELIVERY_PERSON()->value)
            ->count();
    }

    /**
     * Count bottles with with_client status
     */
    public function countWithClientBottles(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): int
    {
        return $this->createBaseStatsQuery($startDate, $endDate, $distributionCenterIds)
            ->where('status', BottleStatus::WITH_CLIENT()->value)
            ->count();
    }

    /**
     * Count bottles with lost_stolen status
     */
    public function countLostStolenBottles(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): int
    {
        return $this->createBaseStatsQuery($startDate, $endDate, $distributionCenterIds)
            ->where('status', BottleStatus::LOST_STOLEN()->value)
            ->count();
    }
}
