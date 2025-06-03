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
    public int $pendingOrders = 0;
    public int $deliveredOrders = 0;
    public int $canceledOrders = 0;

    private DashboardStatsService $statsService;

    public function boot(DashboardStatsService $statsService): void
    {
        $this->statsService = $statsService;
    }

    public function mount(): void
    {
        $this->loadStats();
    }

    #[On('filters-changed-dashboard')]
    public function handleFiltersChanged(?string $startDate = null, ?string $endDate = null, ?string $distributionCenterId = null): void
    {
        $this->loadStats($startDate, $endDate, $distributionCenterId);
    }

    private function loadStats(?string $startDate = null, ?string $endDate = null, ?string $distributionCenterId = null): void
    {
        if ($distributionCenterId === null || $distributionCenterId === '') {
            /** @var User|null $user */
            $user = Auth::user();
            if ($user) {
                $centerIds = $user->distributionCenters()->pluck('distribution_center_id')->toArray();

                if (! empty($centerIds)) {
                    $stats = $this->statsService->getStatsByMultipleCenters($startDate, $endDate, $centerIds);

                    $this->revenue = $stats->revenue;
                    $this->pendingOrders = $stats->pendingOrders;
                    $this->deliveredOrders = $stats->deliveredOrders;
                    $this->canceledOrders = $stats->canceledOrders;

                    return;
                }
            }
        }

        $stats = $this->statsService->getStats($startDate, $endDate, $distributionCenterId);

        $this->revenue = $stats->revenue;
        $this->pendingOrders = $stats->pendingOrders;
        $this->deliveredOrders = $stats->deliveredOrders;
        $this->canceledOrders = $stats->canceledOrders;
    }

    public function render()
    {
        return view('livewire.dashboard.stats-overview');
    }
}
