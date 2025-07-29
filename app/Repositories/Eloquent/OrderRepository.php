<?php

namespace App\Repositories\Eloquent;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\OrderNotFoundException;
use App\Models\Order;
use App\Models\Refund;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderRepository extends BaseEloquentRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    /**
     * Get order with all necessary relationships loaded
     */
    public function getWithDetails(int $orderId): ?Order
    {
        $order = $this->model::with([
            'customer',
            'deliveryAddress',
            'distributionCenter',
            'deliveryPerson',
            'items.productCategory',
            'payment',
        ])->find($orderId);

        /** @var Order|null $order */
        return $order;
    }

    /**
     * Get the most recent paid refund for an order
     */
    public function getLatestPaidRefund(Order $order): ?Refund
    {
        $refund = $order->refunds()
            ->where('status', PaymentStatus::PAID())
            ->latest()
            ->first();

        /** @var Refund|null $refund */
        return $refund;
    }

    /**
     * Create a base query builder with common filters
     */
    private function createBaseStatsQuery(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): Builder
    {
        $query = Order::query();

        if ($startDate && $endDate) {
            $query->whereBetween('order_date', [
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
     * Calculate total revenue from delivered orders
     */
    public function calculateRevenue(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): float
    {
        $revenue = $this->createBaseStatsQuery($startDate, $endDate, $distributionCenterIds)
            ->where('status', OrderStatus::DELIVERED()->value)
            ->sum('total_amount');

        return $revenue;
    }

    /**
     * Count pending orders
     */
    public function countPendingOrders(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): int
    {
        $query = $this->createBaseStatsQuery($startDate, $endDate, $distributionCenterIds)
            ->whereIn('status', [
                OrderStatus::CONFIRMED()->value,
                OrderStatus::PROCESSING()->value,
                OrderStatus::PENDING()->value,
            ]);

        return $query->count();
    }

    /**
     * Count delivered orders
     */
    public function countDeliveredOrders(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): int
    {
        return $this->createBaseStatsQuery($startDate, $endDate, $distributionCenterIds)
            ->where('status', OrderStatus::DELIVERED()->value)
            ->count();
    }

    /**
     * Count canceled orders
     */
    public function countCanceledOrders(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): int
    {
        return $this->createBaseStatsQuery($startDate, $endDate, $distributionCenterIds)
            ->where('status', OrderStatus::CANCELLED()->value)
            ->count();
    }

    /**
     * {@inheritDoc}
     */
    public function getById(int $id): Order
    {
        $order = Order::find($id);

        if (! $order) {
            throw OrderNotFoundException::forId($id);
        }

        return $order;
    }

    /**
     * {@inheritDoc}
     */
    public function updateStatus(Order $order, OrderStatus $status): bool
    {
        $order->status = $status;

        return $order->save();
    }

    /**
     * Récupère les données agrégées d'ordres par jour pour une période donnée.
     *
     * @param  string  $startDate  La date de début (format Y-m-d).
     * @param  string  $endDate  La date de fin (format Y-m-d).
     * @param  string|array|null  $distributionCenterId  L'ID du centre de distribution, un tableau d'IDs, ou null pour tous.
     * @param  string  $aggregationColumn  La colonne à agréger (ex: 'total_amount', '*').
     * @param  string  $aggregationType  Le type d'agrégation (ex: 'SUM', 'COUNT').
     * @return \Illuminate\Support\Collection Collection de résultats (chaque élément: ['date' => 'Y-m-d', 'value_total' => float/int]).
     */
    public function getAggregatedOrdersByDay(
        string $startDate,
        string $endDate,
        string|array|null $distributionCenterId,
        string $aggregationColumn,
        string $aggregationType
    ): Collection {
        $query = $this->createBaseStatsQuery(
            Carbon::parse($startDate),
            Carbon::parse($endDate),
            is_string($distributionCenterId) ? [$distributionCenterId] : $distributionCenterId
        );

        $selectClause = DB::raw("DATE(order_date) as date, {$aggregationType}({$aggregationColumn}) as value_total");

        $results = $query
            ->select($selectClause)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $results;
    }

    public function assignDeliveryPerson(Order $order, int $deliveryPersonId, ?string $reason): bool
    {
        $order->delivery_person_id = $deliveryPersonId;
        $order->delivery_person_update_reason = $reason;

        return $order->save();
    }
}
