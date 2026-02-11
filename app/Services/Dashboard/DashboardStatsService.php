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

    public function getStats(
        ?string $startDate = null,
        ?string $endDate = null,
        string|array|null $distributionCenterIds = null
    ): StatsDTO {
        $startDateCarbon = $startDate ? Carbon::parse($startDate) : null;
        $endDateCarbon = $endDate ? Carbon::parse($endDate) : null;

        $centerIds = $this->normalizeCenterIds($distributionCenterIds);

        $revenue = $this->orderRepository->calculateRevenue($startDateCarbon, $endDateCarbon, $centerIds);
        $paidOrders = $this->orderRepository->countPaidOrders($startDateCarbon, $endDateCarbon, $centerIds);
        $processingOrders = $this->orderRepository->countProcessingOrders($startDateCarbon, $endDateCarbon, $centerIds);
        $pendingOrders = $this->orderRepository->countPendingOrders($startDateCarbon, $endDateCarbon, $centerIds);
        $deliveredOrders = $this->orderRepository->countDeliveredOrders($startDateCarbon, $endDateCarbon, $centerIds);
        $canceledOrders = $this->orderRepository->countCanceledOrders($startDateCarbon, $endDateCarbon, $centerIds);

        return new StatsDTO(
            revenue: $revenue,
            paidOrders: $paidOrders,
            processingOrders: $processingOrders,
            pendingOrders: $pendingOrders,
            deliveredOrders: $deliveredOrders,
            canceledOrders: $canceledOrders
        );
    }

    /**
     * Normalise les IDs de centres de distribution
     */
    private function normalizeCenterIds(string|array|null $distributionCenterIds): ?array
    {
        if ($distributionCenterIds === null || $distributionCenterIds === '') {
            return null;
        }

        if (is_string($distributionCenterIds)) {
            return [$distributionCenterIds];
        }

        if (is_array($distributionCenterIds) && empty($distributionCenterIds)) {
            return null;
        }

        return $distributionCenterIds;
    }
}
