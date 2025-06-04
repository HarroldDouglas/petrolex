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


/*namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\StockMovementRepositoryInterface;
use App\Enums\BottleStatus;
use App\Models\Bottle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Or use your Eloquent Models

class StockMovementRepository implements StockMovementRepositoryInterface
{
    // Assuming you have models/tables for stock entries, exits, exchanges, and current stock.
    // For example, if you have an 'stock_entries' table and a 'stock_exits' table etc.

    public function calculateTotalEntries(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int
    {
        $query = DB::table('stock_entries'); // Adjust table name as per your schema
        // Apply date filters
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        // Apply distribution center filter if provided
        if ($centerIds !== null && count($centerIds) > 0) {
            $query->whereIn('distribution_center_id', $centerIds); // Assuming this column exists
        }
        return $query->sum('quantity'); // Assuming 'quantity' column holds the number of bottles
    }

    public function calculateTotalExits(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int
    {
        $query = DB::table('stock_exits'); // Adjust table name
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        if ($centerIds !== null && count($centerIds) > 0) {
            $query->whereIn('distribution_center_id', $centerIds);
        }
        return $query->sum('quantity');
    }

    public function calculateTotalExchanges(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int
    {
        $query = DB::table('stock_exchanges'); // Adjust table name
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        if ($centerIds !== null && count($centerIds) > 0) {
            $query->whereIn('distribution_center_id', $centerIds);
        }
        return $query->sum('quantity');
    }

    public function calculateCurrentStock(?array $centerIds = null): int
    {
        // This is a more complex calculation, often current stock = total entries - total exits
        // Or you might have a dedicated 'inventory' table that tracks current stock.
        // For simplicity, let's assume it's calculated from entries and exits for now.
        // **IMPORTANT:** You'll need to implement this correctly based on your actual database schema.
        // It might be a direct lookup or a sum from an 'inventory' table.

        $totalEntries = $this->calculateTotalEntries(null, null, $centerIds); // Get all entries
        $totalExits = $this->calculateTotalExits(null, null, $centerIds); // Get all exits

        return $totalEntries - $totalExits; // This is a simplified calculation

        // Alternative (if you have an inventory table with current stock):
        /*
        $query = DB::table('inventory'); // Assuming an inventory table
        if ($centerIds !== null && count($centerIds) > 0) {
            $query->whereIn('distribution_center_id', $centerIds);
        }
        return $query->sum('current_quantity'); // Assuming a 'current_quantity' column
        
    }
}*/