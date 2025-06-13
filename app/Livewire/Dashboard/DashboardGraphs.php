<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use Livewire\Component;
use Livewire\Attributes\On;
use Carbon\Carbon;

class DashboardGraphs extends Component
{
    public array $ordersGraphData = [
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

    public array $revenueGraphData = [
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

    public ?string $currentStartDate = null;
    public ?string $currentEndDate = null;
    public ?string $currentDistributionCenterId = null;

    public function mount()
    {
        $this->loadGraphData($this->currentStartDate, $this->currentEndDate, $this->currentDistributionCenterId);
    }

    #[On('filters-changed-dashboard')]
    public function updateGraphs(
        ?string $startDate,
        ?string $endDate,
        ?string $distributionCenterId
    ): void {
        $this->currentStartDate = $startDate;
        $this->currentEndDate = $endDate;
        $this->currentDistributionCenterId = $distributionCenterId;

        $this->loadGraphData($startDate, $endDate, $distributionCenterId);
    }

    private function loadGraphData(?string $startDate, ?string $endDate, ?string $distributionCenterId): void
    {
        $query = Order::query();

        if ($startDate && $endDate) {
            $query->whereBetween('order_date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        } elseif ($endDate) { 
             $query->whereDate('order_date', '<=', Carbon::parse($endDate)->endOfDay());
        }

        if ($distributionCenterId) {
            $query->where('distribution_center_id', $distributionCenterId);
        }

        $results = $query
            ->selectRaw('DATE(order_date) as date, COUNT(*) as orders_count, SUM(total_amount) as revenue_total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Prepare data for Chart.js
        $labels = [];
        $orders = [];
        $revenues = [];

        if ($startDate && $endDate) {
            $period = Carbon::parse($startDate)->toPeriod(Carbon::parse($endDate));
            $dateMap = $results->keyBy('date');

            foreach ($period as $date) {
                $formattedDate = $date->format('Y-m-d');
                $labels[] = $date->format('d/m');
                $orders[] = $dateMap->has($formattedDate) ? (int) $dateMap[$formattedDate]->orders_count : 0;
                $revenues[] = $dateMap->has($formattedDate) ? (float) $dateMap[$formattedDate]->revenue_total : 0.0;
            }
        } else {
            foreach ($results as $row) {
                $labels[] = Carbon::parse($row->date)->format('d/m');
                $orders[] = (int) $row->orders_count;
                $revenues[] = (float) $row->revenue_total;
            }
        }


        $this->ordersGraphData['labels'] = $labels;
        $this->ordersGraphData['datasets'][0]['data'] = $orders;

        $this->revenueGraphData['labels'] = $labels;
        $this->revenueGraphData['datasets'][0]['data'] = $revenues;

        $this->dispatch('update-dashboard-charts',
            ordersData: $this->ordersGraphData,
            revenueData: $this->revenueGraphData
        );
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-graphs');
    }
}