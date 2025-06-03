<?php

namespace App\Services\Dashboard;

use App\Contracts\Repositories\OrderRepositoryInterface;
use Carbon\Carbon;

class DashboardStatsService
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getStats(?string $startDate = null, ?string $endDate = null, ?string $warehouseId = null): array
    {
        $startDateCarbon = $startDate ? Carbon::parse($startDate) : null;
        $endDateCarbon = $endDate ? Carbon::parse($endDate) : null;

        return [
            'revenue' => $this->orderRepository->calculateRevenue($startDateCarbon, $endDateCarbon, $warehouseId),
            'pendingOrders' => $this->orderRepository->countPendingOrders($startDateCarbon, $endDateCarbon, $warehouseId),
            'deliveredOrders' => $this->orderRepository->countDeliveredOrders($startDateCarbon, $endDateCarbon, $warehouseId),
            'canceledOrders' => $this->orderRepository->countCanceledOrders($startDateCarbon, $endDateCarbon, $warehouseId),
        ];
    }
}
