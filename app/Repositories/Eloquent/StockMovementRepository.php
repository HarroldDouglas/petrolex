<?php

namespace App\Repositories\Eloquent;

use App\Enums\BottleOrderType;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\SupplierDelivery;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use Carbon\Carbon;

class StockMovementRepository implements StockMovementRepositoryInterface
{
    /**
     * Calculate total sold bottles with content (full bottles sold)
     */
    public function calculateTotalSoldBottles(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int
    {
        $query = Order::query();

        if ($startDate && $endDate) {
            $query->whereBetween('order_date', [
                $startDate->startOfDay(),
                $endDate->endOfDay(),
            ]);
        }

        if ($centerIds && count($centerIds) > 0) {
            $query->whereIn('distribution_center_id', $centerIds);
        }

        // Get orders and count the bottles sold with content
        return $query->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.bottle_type', BottleOrderType::BOTTLE_WITH_CONTENT()->value)
            ->sum('order_items.quantity');
    }

    /**
     * Calculate total refills/exchanges (content only)
     */
    public function calculateTotalExchanges(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int
    {
        $query = Order::query();

        if ($startDate && $endDate) {
            $query->whereBetween('order_date', [
                $startDate->startOfDay(),
                $endDate->endOfDay(),
            ]);
        }

        if ($centerIds && count($centerIds) > 0) {
            $query->whereIn('distribution_center_id', $centerIds);
        }

        // Get content only exchanges/refills
        return $query->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.bottle_type', BottleOrderType::CONTENT()->value)
            ->sum('order_items.quantity');
    }

    /**
     * Calculate the total number of full bottles in current stock.
     */
    public function calculateFullBottles(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int
    {
        $total = 0;

        $query = DistributionCenter::query();

        if ($centerIds && count($centerIds) > 0) {
            $query->whereIn('id', $centerIds);
        }

        $centers = $query->get();

        foreach ($centers as $center) {
            $total += $center->getTotalFilledBottlesAttribute();
        }

        return $total;
    }

    /**
     * Calculate the total number of empty bottles in current stock.
     */
    public function calculateEmptyBottles(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int
    {
        $total = 0;

        $query = DistributionCenter::query();

        if ($centerIds && count($centerIds) > 0) {
            $query->whereIn('id', $centerIds);
        }

        $centers = $query->get();

        foreach ($centers as $center) {
            $total += $center->getTotalEmptyBottlesAttribute();
        }

        return $total;
    }

    /**
     * Calculate the total number of bottles supplied (entries)
     */
    public function calculateTotalSupplied(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int
    {
        $query = SupplierDelivery::query();

        if ($startDate && $endDate) {
            $query->whereBetween('supply_date', [
                $startDate->startOfDay(),
                $endDate->endOfDay(),
            ]);
        }

        if ($centerIds && count($centerIds) > 0) {
            $query->whereIn('distribution_center_id', $centerIds);
        }

        return $query->join('supplier_delivery_product_types', 'supplier_deliveries.id', '=', 'supplier_delivery_product_types.supplier_delivery_id')
            ->sum('supplier_delivery_product_types.expected_quantity');
    }
}
