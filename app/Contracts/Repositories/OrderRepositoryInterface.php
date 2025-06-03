<?php

namespace App\Contracts\Repositories;

use Carbon\Carbon;

interface OrderRepositoryInterface
{
    /**
     * Calculate total revenue from delivered orders
     */
    public function calculateRevenue(?Carbon $startDate = null, ?Carbon $endDate = null, ?string $warehouseId = null): string;

    /**
     * Count pending orders
     */
    public function countPendingOrders(?Carbon $startDate = null, ?Carbon $endDate = null, ?string $warehouseId = null): int;

    /**
     * Count delivered orders
     */
    public function countDeliveredOrders(?Carbon $startDate = null, ?Carbon $endDate = null, ?string $warehouseId = null): int;

    /**
     * Count canceled orders
     */
    public function countCanceledOrders(?Carbon $startDate = null, ?Carbon $endDate = null, ?string $warehouseId = null): int;
}
