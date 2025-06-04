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

    // Livewire event listener: listens for filter changes on the dashboard
    #[On('filters-changed-dashboard')] // Using the same event as StatsOverview, or define a new one if filters are distinct
    public function handleFiltersChanged(
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $distributionCenterId = null
    ): void {
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




/*
namespace App\Livewire\Dashboard;

use App\Services\Dashboard\StockMovementStatsService;
use App\DTOs\Dashboard\StockMovementStatsDTO; // Make sure this is imported
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\User;

class StatsStockMovement extends Component
{
    // Single public property to hold the DTO, which contains all the stats
    public StockMovementStatsDTO $stats;

    private StockMovementStatsService $stockStatsService;

    public function boot(StockMovementStatsService $stockStatsService): void
    {
        $this->stockStatsService = $stockStatsService;
    }

    public function mount(): void
    {
        $this->loadStockStats();
    }

    #[On('filters-changed-dashboard')]
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
        $loadedStats = null; // Use a temporary variable to hold the DTO

        if ($distributionCenterId === null || $distributionCenterId === '') {
            /** @var User|null $user */
            /*$user = Auth::user();
            if ($user) {
                $centerIds = $user->distributionCenters()->pluck('distribution_center_id')->toArray();

                if (!empty($centerIds)) {
                    $loadedStats = $this->stockStatsService->getStockStatsByMultipleCenters($startDate, $endDate, $centerIds);
                }
            }
        }

        // If stats were not loaded via multiple centers or if distributionCenterId was provided
        if ($loadedStats === null) {
            $loadedStats = $this->stockStatsService->getStockStats($startDate, $endDate, $distributionCenterId);
        }

        // Assign the loaded DTO to the public $stats property
        $this->stats = $loadedStats;
    }

    public function render()
    {
        // No need to pass 'stats' explicitly if it's a public property.
        // Livewire automatically makes public properties available to the view.
        return view('livewire.dashboard.stats-stock-movement');
    }
}*/