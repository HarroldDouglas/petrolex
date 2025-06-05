<?php

namespace App\Services\Dashboard;

use App\DTOs\Dashboard\StatsDTO;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Carbon\Carbon;

class DashboardStatsService
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getStats(?string $startDate = null, ?string $endDate = null, ?string $distributionCenterId = null): StatsDTO
    {
        $startDateCarbon = $startDate ? Carbon::parse($startDate) : null;
        $endDateCarbon = $endDate ? Carbon::parse($endDate) : null;

        $distributionCenterIds = $distributionCenterId ? [$distributionCenterId] : null;

        $revenue = $this->orderRepository->calculateRevenue($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $pendingOrders = $this->orderRepository->countPendingOrders($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $deliveredOrders = $this->orderRepository->countDeliveredOrders($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $canceledOrders = $this->orderRepository->countCanceledOrders($startDateCarbon, $endDateCarbon, $distributionCenterIds);

        return new StatsDTO(
            revenue: $revenue,
            pendingOrders: $pendingOrders,
            deliveredOrders: $deliveredOrders,
            canceledOrders: $canceledOrders
        );
    }

    public function getStatsByMultipleCenters(?string $startDate = null, ?string $endDate = null, array $distributionCenterIds = []): StatsDTO
    {
        $startDateCarbon = $startDate ? Carbon::parse($startDate) : null;
        $endDateCarbon = $endDate ? Carbon::parse($endDate) : null;

        $revenue = $this->orderRepository->calculateRevenue($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $pendingOrders = $this->orderRepository->countPendingOrders($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $deliveredOrders = $this->orderRepository->countDeliveredOrders($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $canceledOrders = $this->orderRepository->countCanceledOrders($startDateCarbon, $endDateCarbon, $distributionCenterIds);

        return new StatsDTO(
            revenue: $revenue,
            pendingOrders: $pendingOrders,
            deliveredOrders: $deliveredOrders,
            canceledOrders: $canceledOrders
        );
    }
}
