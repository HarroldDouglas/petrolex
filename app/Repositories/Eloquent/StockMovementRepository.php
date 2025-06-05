<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\StockMovementRepositoryInterface;
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
     * Calculate total entries of bottles
     */
    public function calculateTotalEntries(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int
    {
        return $this->createBaseQuery($startDate, $endDate, $centerIds)
            ->where('status', BottleStatus::IN_STOCK()->value)
            ->count(); // Assuming 'quantity' is the column that holds the number of bottles
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
            ->count(); // Assuming 'quantity' is the column that holds the number of bottles
    }

    /**
     * Calculate current stock of bottles
     */
    public function calculateCurrentStock(?array $centerIds = null): int
    {
        $totalEntries = $this->calculateTotalEntries(null, null, $centerIds); // Get all entries
        $totalExits = $this->calculateTotalExits(null, null, $centerIds); // Get all exits

        return $totalEntries - $totalExits;
    }

    /**
     * Calculate total stock of bottles
     */
    public function calculateTotalStock(?array $centerIds = null): int
    {
        return $this->createBaseQuery(null, null, $centerIds)
            ->count(); // Assuming 'quantity' is the column that holds the number of bottles
    }

}
