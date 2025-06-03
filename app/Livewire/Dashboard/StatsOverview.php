<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\DashboardStatsService;
use Illuminate\Support\Facades\Log;
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
    public function handleFiltersChanged(?string $startDate = null, ?string $endDate = null, ?string $warehouseId = null): void
    {
        Log::debug('StatsOverview received filters-changed-dashboard event', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'warehouseId' => $warehouseId,
        ]);

        $this->loadStats($startDate, $endDate, $warehouseId);
    }

    private function loadStats(?string $startDate = null, ?string $endDate = null, ?string $warehouseId = null): void
    {
        Log::debug('StatsOverview loading stats with parameters', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'warehouseId' => $warehouseId,
        ]);

        $stats = $this->statsService->getStats($startDate, $endDate, $warehouseId);
        Log::debug('StatsOverview received stats from service', $stats);

        $this->revenue = $stats['revenue'];
        $this->pendingOrders = $stats['pendingOrders'];
        $this->deliveredOrders = $stats['deliveredOrders'];
        $this->canceledOrders = $stats['canceledOrders'];
    }

    public function render()
    {
        return view('livewire.dashboard.stats-overview');
    }
}
