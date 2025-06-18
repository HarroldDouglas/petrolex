<?php

namespace App\Livewire\Dashboard;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Services\Dashboard\DashboardGraphService;
use App\Enums\PeriodFilterStats;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 

class DashboardGraphs extends Component
{
    public array $revenueGraphData = [
        'labels' => [],
        'datasets' => [
            [
                'label' => 'Nombre de Commandes',
                'data' => [],
                'backgroundColor' => '#FE2C55',
                'borderColor' => '#FE2C55',
                'borderWidth' => 2,
                'tension' => 0.4,
                'fill' => false,
                ]
            ]
    ];
    public array $ordersGraphData = [
        'labels' => [],
        'datasets' => [
            [
                'label' => 'Chiffre d\'affaire (CFA)',
                'data' => [],
                'backgroundColor' => '#25F4EE',
                'borderColor' => '#25F4EE',
                'borderWidth' => 2,
                'tension' => 0.4,
                'fill' => false,
            ]
        ]
    ];

    private DashboardGraphService $graphService;

    public function boot(DashboardGraphService $graphService): void
    {
        $this->graphService = $graphService;
    }

    public function mount(): void
    {
        $this->loadGraphDataByFilters(PeriodFilterStats::default());
    }

    private function loadGraphDataByFilters(string $period, ?string $startDate = null, ?string $endDate = null, ?string $distributionCenterId = null): void
    {
        $now = Carbon::now();
        $finalStartDate = $startDate;
        $finalEndDate = $endDate;

        if ($period === 'custom' && $startDate && $endDate) {
        } else {
            $finalStartDate = match ($period) {
                '1week' => $now->copy()->subWeek()->format('Y-m-d'),
                '2weeks' => $now->copy()->subWeeks(2)->format('Y-m-d'),
                '1month' => $now->copy()->subMonth()->format('Y-m-d'),
                '2months' => $now->copy()->subMonths(2)->format('Y-m-d'),
                '3months' => $now->copy()->subMonths(3)->format('Y-m-d'),
                default => $now->copy()->subMonth()->format('Y-m-d'), 
            };
            $finalEndDate = $now->format('Y-m-d');
        }

        if (is_null($finalStartDate)) {
            $finalStartDate = $now->copy()->subMonth()->format('Y-m-d');
        }
        if (is_null($finalEndDate)) {
            $finalEndDate = $now->format('Y-m-d');
        }

        $revenueStats = (object)['labels' => [], 'data' => []];
        $ordersStats = (object)['labels' => [], 'data' => []];

        if ($distributionCenterId === null || $distributionCenterId === '') {
            $user = Auth::user();
            if ($user && method_exists($user, 'isGlobal') && $user->isGlobal()) {
                $revenueStats = $this->graphService->getRevenueByDay($finalStartDate, $finalEndDate, null);
                $ordersStats = $this->graphService->getOrdersByDay($finalStartDate, $finalEndDate, null);
            } elseif ($user) {
                $centerIds = $user->distributionCenters()->pluck('distribution_center_id')->toArray();
                if (!empty($centerIds)) {
                    $revenueStats = $this->graphService->getRevenueByDay($finalStartDate, $finalEndDate, $centerIds);
                    $ordersStats = $this->graphService->getOrdersByDay($finalStartDate, $finalEndDate, $centerIds);
                } else {
                    $revenueStats = (object)['labels' => [], 'data' => []];
                    $ordersStats = (object)['labels' => [], 'data' => []];
                }
            } else {
                $revenueStats = (object)['labels' => [], 'data' => []];
                $ordersStats = (object)['labels' => [], 'data' => []];
            }
        } else {
            $revenueStats = $this->graphService->getRevenueByDay($finalStartDate, $finalEndDate, $distributionCenterId);
            $ordersStats = $this->graphService->getOrdersByDay($finalStartDate, $finalEndDate, $distributionCenterId);
        }

        $this->revenueGraphData = [
            'labels' => $revenueStats->labels,
            'datasets' => [[
                'label' => 'Chiffre d\'affaires',
                'data' => $revenueStats->data,
                'borderColor' => '#4e73df',
                'tension' => 0.4,
                'fill' => false
            ]]
        ];

        $this->ordersGraphData = [
            'labels' => $ordersStats->labels,
            'datasets' => [[
                'label' => 'Commandes',
                'data' => $ordersStats->data,
                'backgroundColor' => '#36b9cc'
            ]]
        ];
    }

    #[On('filters-changed-dashboard')]
    public function handleFiltersChanged(?string $startDate = null, ?string $endDate = null, ?string $distributionCenterId = null, string $selectedPeriod = null): void
    {
        $this->loadGraphDataByFilters($selectedPeriod, $startDate, $endDate, $distributionCenterId);

        $this->dispatch('update-dashboard-charts', ordersData: $this->ordersGraphData, revenueData: $this->revenueGraphData);
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-graphs');
    }
}