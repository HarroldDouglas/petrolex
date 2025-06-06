<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\StockMovementRepositoryInterface;
use App\Enums\BottleStatus;
use App\Models\Bottle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class StockMovementRepository implements StockMovementRepositoryInterface
{
    /**
     * Create a base query builder with common filters
     */
    private function createBaseQuery(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): Builder
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
     * Calculate total exits of bottles
     */
    public function calculateTotalExits(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int
    {
        return $this->createBaseQuery($startDate, $endDate, $centerIds)
            ->where('status', BottleStatus::WITH_CLIENT()->value)
            ->count(); // Assuming 'quantity' is the column that holds the number of bottles
    }

    /**
     * Calculate total exchanges of bottles
     */
    public function calculateTotalExchanges(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int
    {
        return $this->createBaseQuery($startDate, $endDate, $centerIds)
            ->where('status', [
                BottleStatus::WITH_DELIVERY_PERSON()->value,
                BottleStatus::WITH_CLIENT()->value,
            ])
            ->count();
    }

    /**
     * Calculate the total number of full bottles.
     */
    public function calculateFullBottles(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int
    {
        return $this->createBaseQuery($startDate, $endDate, $centerIds)
            ->where('is_filled', true)
            ->count();
    }

    /**
     * Calculate the total number of empty bottles.
     */
    public function calculateEmptyBottles(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int
    {
        return $this->createBaseQuery($startDate, $endDate, $centerIds)
            ->where('is_filled', false)
            ->count();
    }

}
