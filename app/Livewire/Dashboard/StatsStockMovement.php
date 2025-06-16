<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use App\Services\Dashboard\StockMovementStatsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class StatsStockMovement extends Component
{
    public int $totalSoldBottles = 0;
    public int $totalExchanges = 0;
    public int $emptyBottles = 0;
    public int $fullBottles = 0;
    public int $totalSupplied = 0;

    public ?string $currentStartDate = null;
    public ?string $currentEndDate = null;
    public ?string $currentDistributionCenterId = null;

    private StockMovementStatsService $stockStatsService;

    public function boot(StockMovementStatsService $stockStatsService): void
    {
        $this->stockStatsService = $stockStatsService;
    }

    public function mount(): void
    {
        $this->setDefaultFilters();
        $this->loadStockStats();
    }

    #[On('filters-changed-dashboard')]
    public function handleFiltersChanged(
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $distributionCenterId = null
    ): void {
        $this->currentStartDate = $startDate ?: $this->getDefaultStartDate();
        $this->currentEndDate = $endDate ?: $this->getDefaultEndDate();
        $this->currentDistributionCenterId = $distributionCenterId;

        $this->loadStockStats();
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

    private function loadStockStats(): void
    {
        $effectiveDistributionCenterIds = $this->resolveDistributionCenterIds();

        $stats = $this->stockStatsService->getStockStats(
            startDate: $this->currentStartDate,
            endDate: $this->currentEndDate,
            distributionCenterIds: $effectiveDistributionCenterIds
        );

        $this->updateStats($stats);
    }

    private function resolveDistributionCenterIds(): ?array
    {
        if ($this->currentDistributionCenterId && $this->currentDistributionCenterId !== '') {
            return [$this->currentDistributionCenterId];
        }

        /** @var User|null $user */
        $user = Auth::user();
        if ($user) {
            $userCenterIds = $user->distributionCenters()->pluck('distribution_center_id')->toArray();
            if (! empty($userCenterIds)) {
                return $userCenterIds;
            }
        }

        return null;
    }

    private function updateStats($stats): void
    {
        $this->totalSoldBottles = $stats->totalSoldBottles;
        $this->totalExchanges = $stats->totalExchanges;
        $this->fullBottles = $stats->fullBottles;
        $this->emptyBottles = $stats->emptyBottles;
        $this->totalSupplied = $stats->totalSupplied;
    }

    public function getTotalSuppliedUrlProperty(): string
    {
        return '/supplies?'.http_build_query([
            'table-filters' => [
                'centre_de_distribution' => $this->currentDistributionCenterId ?? '',
                'type_de_livraison' => 'has_bottles',
                'statut' => 'COMPLETED',
                'période_de_date_de_livraison' => [
                    'minDate' => $this->currentStartDate,
                    'maxDate' => $this->currentEndDate,
                ],
            ],
        ]);
    }

    public function getTotalSoldBottlesUrlProperty(): string
    {
        return '/orders?'.http_build_query([
            'table-filters' => [
                'centre_de_distribution' => $this->currentDistributionCenterId ?? '',
                'statut' => ['delivered'],
                'période_de_date_de_commande' => [
                    'minDate' => $this->currentStartDate,
                    'maxDate' => $this->currentEndDate,
                ],
                'type_de_bouteille' => 'full',
            ],
        ]);
    }

    public function getTotalExchangesUrlProperty(): string
    {
        return '/orders?'.http_build_query([
            'table-filters' => [
                'centre_de_distribution' => $this->currentDistributionCenterId ?? '',
                'statut' => ['delivered'],
                'période_de_date_de_commande' => [
                    'minDate' => $this->currentStartDate,
                    'maxDate' => $this->currentEndDate,
                ],
                'type_de_bouteille' => 'recharge',
            ],
        ]);
    }

    public function getCurrentStockUrlProperty(): string
    {
        return '/bottles?'.http_build_query([
            'table-filters' => [
                'etat' => ['in_stock_filled', 'in_stock_empty'],
                'centre_de_distribution' => $this->currentDistributionCenterId ?? '',
            ],
        ]);
    }

    public function getFullBottlesUrlProperty(): string
    {
        return '/bottles?'.http_build_query([
            'table-filters' => [
                'etat' => ['in_stock_filled'],
                'centre_de_distribution' => $this->currentDistributionCenterId ?? '',
            ],
        ]);
    }

    public function getEmptyBottlesUrlProperty(): string
    {
        return '/bottles?'.http_build_query([
            'table-filters' => [
                'etat' => ['in_stock_empty'],
                'centre_de_distribution' => $this->currentDistributionCenterId ?? '',
            ],
        ]);
    }

    public function render()
    {
        return view('livewire.dashboard.stats-stock-movement');
    }
}
