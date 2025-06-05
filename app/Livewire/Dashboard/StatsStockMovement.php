<?php

namespace App\Livewire\Dashboard;

use App\Contracts\Repositories\StockMovementRepositoryInterface; // Renamed from StockRepositoryInterface for clarity
use App\Services\Dashboard\StockMovementStatsService;
use App\DTOs\Dashboard\StockMovementStatsDTO; // Ensure this DTO is created
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; // Needed if you filter by user's centers
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\User; // Needed if you filter by user's centers

class StatsStockMovement extends Component
{
    // Public properties that will be passed to the Blade view
    public int $totalEntries = 0;
    public int $totalExits = 0;
    public int $totalExchanges = 0;
    public int $currentStock = 0;
    public int $totalStock = 0; // Assuming you want to track total stock as well

    private StockMovementStatsService $stockStatsService;

    // Dependency Injection: Laravel provides the service instance
    public function boot(StockMovementStatsService $stockStatsService): void
    {
        $this->stockStatsService = $stockStatsService;
    }

    // Lifecycle hook: runs once when the component is initially rendered
    public function mount(): void
    {
        $this->loadStockStats();
    }

    // Livewire event listener: listens for filter changes on the stock-movement
    #[On('filters-changed-stock-movement')] // Adjusted event name to be more specific
    public function handleFiltersChanged(
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $distributionCenterId = null
    ): void {
        // Call the private method to load stock stats with the provided filters
        $this->loadStockStats($startDate, $endDate, $distributionCenterId);
    }

    // Private method to encapsulate the data loading logic
    private function loadStockStats(
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $distributionCenterId = null
    ): void {
        $stats = null; // Initialize to null

        if ($distributionCenterId === null || $distributionCenterId === '') {
            /** @var User|null $user */
            $user = Auth::user();
            if ($user) {
                // Assuming 'distributionCenters' is a relationship on your User model
                // and it returns a collection that has a 'distribution_center_id' attribute
                $centerIds = $user->distributionCenters()->pluck('distribution_center_id')->toArray();

                if (!empty($centerIds)) {
                    $stats = $this->stockStatsService->getStockStatsByMultipleCenters($startDate, $endDate, $centerIds);
                }
            }
        }

        // If stats were not loaded via multiple centers or if distributionCenterId was provided
        if ($stats === null) {
            $stats = $this->stockStatsService->getStockStats($startDate, $endDate, $distributionCenterId);
        }

        // Assign fetched data from the DTO to the public properties
        $this->totalEntries = $stats->totalEntries;
        $this->totalExits = $stats->totalExits;
        $this->totalExchanges = $stats->totalExchanges;
        $this->currentStock = $stats->currentStock;
        $this->totalStock = $stats->totalStock; // Assuming total stock is the same as current stock
    }

    public function render()
    {
        return view('livewire.dashboard.stats-stock-movement');
    }
}