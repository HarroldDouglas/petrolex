<?php

namespace App\Livewire\Dashboard;

use App\Repositories\Contracts\StockMovementRepositoryInterface;
use App\Services\Dashboard\StockMovementStatsService;
use App\DTOs\Dashboard\StockMovementStatsDTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\User;

class StatsStockMovement extends Component
{
    public int $totalExits = 0;
    public int $totalExchanges = 0;
    public int $emptyBottles = 0;
    public int $fullBottles = 0;

    private StockMovementStatsService $stockStatsService;

    public function boot(StockMovementStatsService $stockStatsService): void
    {
        $this->stockStatsService = $stockStatsService;
    }

    public function mount(): void
    {
        $this->loadStockStats();
    }

    #[On('filters-changed-stock-movement')]
    public function handleFiltersChanged(
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $distributionCenterId = null 
    ): void {
        $this->loadStockStats($startDate, $endDate, $distributionCenterId);
    }

    private function loadStockStats(
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $distributionCenterId = null
    ): void {
        $effectiveDistributionCenterIds = null;

        if ($distributionCenterId !== null && $distributionCenterId !== '') {
            $effectiveDistributionCenterIds = [$distributionCenterId];
        } else {
            /** @var User|null $user */
            $user = Auth::user();
            if ($user) {
                $userCenterIds = $user->distributionCenters()->pluck('distribution_center_id')->toArray();
                if (!empty($userCenterIds)) {
                    $effectiveDistributionCenterIds = $userCenterIds;
                }
            }
        }
        
        $stats = $this->stockStatsService->getStockStats(
            startDate: $startDate,
            endDate: $endDate,
            distributionCenterIds: $effectiveDistributionCenterIds
        );

        $this->totalExits = $stats->totalExits;
        $this->totalExchanges = $stats->totalExchanges;
        $this->fullBottles = $stats->fullBottles;
        $this->emptyBottles = $stats->emptyBottles;
    }

    public function render()
    {
        return view('livewire.dashboard.stats-stock-movement');
    }
}