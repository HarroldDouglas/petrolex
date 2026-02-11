<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use App\Services\Dashboard\DashboardStatsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class StatsOverview extends Component
{
    public string $revenue = '0';
    public int $paidOrders = 0;
    public int $processingOrders = 0;
    public int $pendingOrders = 0;
    public int $deliveredOrders = 0;
    public int $canceledOrders = 0;

    public ?string $currentStartDate = null;
    public ?string $currentEndDate = null;
    public ?string $currentDistributionCenterId = null;

    private DashboardStatsService $statsService;

    public function boot(DashboardStatsService $statsService): void
    {
        $this->statsService = $statsService;
    }

    public function mount(): void
    {
        $this->setDefaultFilters();
        $this->loadStats();
    }

    #[On('filters-changed-dashboard')]
    public function handleFiltersChanged(?string $startDate = null, ?string $endDate = null, ?string $distributionCenterId = null): void
    {
        $this->currentStartDate = $startDate ?: $this->getDefaultStartDate();
        $this->currentEndDate = $endDate ?: $this->getDefaultEndDate();
        $this->currentDistributionCenterId = $distributionCenterId;

        $this->loadStats();
    }

    private function setDefaultFilters(): void
    {
        $this->currentStartDate = $this->getDefaultStartDate();
        $this->currentEndDate = $this->getDefaultEndDate();
        $this->currentDistributionCenterId = null;
    }

    private function getDefaultStartDate(): string
    {
        return now()->subDays(7)->format('Y-m-d');
    }

    private function getDefaultEndDate(): string
    {
        return now()->format('Y-m-d');
    }

    private function loadStats(): void
    {
        $distributionCenterIds = $this->resolveDistributionCenterIds();

        $stats = $this->statsService->getStats(
            $this->currentStartDate,
            $this->currentEndDate,
            $distributionCenterIds
        );

        $this->updateStats($stats);
    }

    private function resolveDistributionCenterIds(): string|array|null
    {
        if ($this->currentDistributionCenterId && $this->currentDistributionCenterId !== '') {
            return $this->currentDistributionCenterId;
        }
        /** @var User|null $user */
        $user = Auth::user();

        return $user
            ?->distributionCenters()
            ->pluck('distribution_center_id')
            ->toArray() ?: null;
    }

    private function updateStats($stats): void
    {
        $this->revenue = $stats->revenue;
        $this->paidOrders = $stats->paidOrders;
        $this->processingOrders = $stats->processingOrders;
        $this->pendingOrders = $stats->pendingOrders;
        $this->deliveredOrders = $stats->deliveredOrders;
        $this->canceledOrders = $stats->canceledOrders;
    }

    public function getRevenueUrlProperty(): string
    {
        return '/orders?'.http_build_query([
            'table-filters' => [
                'centre_de_distribution' => $this->currentDistributionCenterId ?? '',
                'statut' => ['delivered'],
                'période_de_date_de_commande' => [
                    'minDate' => $this->currentStartDate,
                    'maxDate' => $this->currentEndDate,
                ],
            ],
        ]);
    }

    public function getPaidOrdersUrlProperty(): string
    {
        return '/orders?'.http_build_query([
            'table-filters' => [
                'centre_de_distribution' => $this->currentDistributionCenterId ?? '',
                'statut' => ['paid'],
                'période_de_date_de_commande' => [
                    'minDate' => $this->currentStartDate,
                    'maxDate' => $this->currentEndDate,
                ],
            ],
        ]);
    }

    public function getProcessingOrdersUrlProperty(): string
    {
        return '/orders?'.http_build_query([
            'table-filters' => [
                'centre_de_distribution' => $this->currentDistributionCenterId ?? '',
                'statut' => ['processing'],
                'période_de_date_de_commande' => [
                    'minDate' => $this->currentStartDate,
                    'maxDate' => $this->currentEndDate,
                ],
            ],
        ]);
    }

    public function getPendingOrdersUrlProperty(): string
    {
        return '/orders?'.http_build_query([
            'table-filters' => [
                'centre_de_distribution' => $this->currentDistributionCenterId ?? '',
                'statut' => ['pending'],
                'période_de_date_de_commande' => [
                    'minDate' => $this->currentStartDate,
                    'maxDate' => $this->currentEndDate,
                ],
            ],
        ]);
    }

    public function getDeliveredOrdersUrlProperty(): string
    {
        return '/orders?'.http_build_query([
            'table-filters' => [
                'centre_de_distribution' => $this->currentDistributionCenterId ?? '',
                'statut' => ['delivered'],
                'période_de_date_de_commande' => [
                    'minDate' => $this->currentStartDate,
                    'maxDate' => $this->currentEndDate,
                ],
            ],
        ]);
    }

    public function getCanceledOrdersUrlProperty(): string
    {
        return '/orders?'.http_build_query([
            'table-filters' => [
                'centre_de_distribution' => $this->currentDistributionCenterId ?? '',
                'statut' => ['cancelled'],
                'période_de_date_de_commande' => [
                    'minDate' => $this->currentStartDate,
                    'maxDate' => $this->currentEndDate,
                ],
            ],
        ]);
    }

    public function render()
    {
        return view('livewire.dashboard.stats-overview');
    }
}
