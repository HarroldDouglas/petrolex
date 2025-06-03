<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\OrderStatus;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * Create a base query builder with common filters
     */
    private function createBaseQuery(?Carbon $startDate = null, ?Carbon $endDate = null, ?string $distributionCenterId = null): Builder
    {
        $query = Order::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                $startDate->startOfDay(),
                $endDate->endOfDay(),
            ]);
        }

        if ($distributionCenterId) {
            $query->where('distribution_center_id', $distributionCenterId);
        }

        return $query;
    }

    /**
     * Calculate total revenue from delivered orders
     */
    public function calculateRevenue(?Carbon $startDate = null, ?Carbon $endDate = null, ?string $distributionCenterId = null): string
    {
        $revenue = $this->createBaseQuery($startDate, $endDate, $distributionCenterId)
            ->where('status', OrderStatus::DELIVERED()->value)
            ->sum('total_amount');

        return number_format($revenue, 0, ',', ' ');
    }

    /**
     * Count pending orders
     */
    public function countPendingOrders(?Carbon $startDate = null, ?Carbon $endDate = null, ?string $distributionCenterId = null): int
    {
        return $this->createBaseQuery($startDate, $endDate, $distributionCenterId)
            ->whereIn('status', [
                OrderStatus::CONFIRMED()->value,
                OrderStatus::PROCESSING()->value,
            ])
            ->count();
    }

    /**
     * Count delivered orders
     */
    public function countDeliveredOrders(?Carbon $startDate = null, ?Carbon $endDate = null, ?string $distributionCenterId = null): int
    {
        return $this->createBaseQuery($startDate, $endDate, $distributionCenterId)
            ->where('status', OrderStatus::DELIVERED()->value)
            ->count();
    }

    /**
     * Count canceled orders
     */
    public function countCanceledOrders(?Carbon $startDate = null, ?Carbon $endDate = null, ?string $distributionCenterId = null): int
    {
        return $this->createBaseQuery($startDate, $endDate, $distributionCenterId)
            ->where('status', OrderStatus::CANCELLED()->value)
            ->count();
    }
}
