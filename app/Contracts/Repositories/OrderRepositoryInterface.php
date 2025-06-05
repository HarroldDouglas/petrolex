<?php

namespace App\Contracts\Repositories;

use App\Enums\OrderStatus;
use App\Exceptions\OrderNotFoundException;
use App\Models\Order;
use Carbon\Carbon;

interface OrderRepositoryInterface
{
    /**
     * Get order with all necessary relationships loaded
     */
    public function getWithDetails(int $orderId): ?Order;

    /**
     * Get an order by its ID
     *
     * @param  int  $id  The order ID
     * @return Order The Order model
     *
     * @throws OrderNotFoundException If the order is not found
     */
    public function getById(int $id): Order;

    /**
     * Update the status of an order
     *
     * @param  Order  $order  The order to update
     * @param  OrderStatus  $status  The new status
     * @return bool Success status
     */
    public function updateStatus(Order $order, OrderStatus $status): bool;

    /**
     * Calculate total revenue from delivered orders
     */
    public function calculateRevenue(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): string;

    /**
     * Count pending orders
     */
    public function countPendingOrders(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): int;

    /**
     * Count delivered orders
     */
    public function countDeliveredOrders(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): int;

    /**
     * Count canceled orders
     */
    public function countCanceledOrders(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): int;
}
