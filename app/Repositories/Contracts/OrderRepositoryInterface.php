<?php

namespace App\Repositories\Contracts;

use App\Enums\OrderStatus;
use App\Exceptions\OrderNotFoundException;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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

    /**
     * Récupère les données agrégées d'ordres par jour pour une période donnée.
     *
     * @param string $startDate La date de début (format Y-m-d).
     * @param string $endDate La date de fin (format Y-m-d).
     * @param string|array|null $distributionCenterId L'ID du centre de distribution, un tableau d'IDs, ou null pour tous.
     * @param string $aggregationColumn La colonne à agréger (ex: 'total_amount', '*').
     * @param string $aggregationType Le type d'agrégation (ex: 'SUM', 'COUNT').
     * @return \Illuminate\Support\Collection Collection de résultats (chaque élément: ['date' => 'Y-m-d', 'value_total' => float/int]).
     */
    public function getAggregatedOrdersByDay(
        string $startDate,
        string $endDate,
        string|array|null $distributionCenterId,
        string $aggregationColumn,
        string $aggregationType
    ): Collection;
}
