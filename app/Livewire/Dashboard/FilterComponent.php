<?php

namespace App\Livewire\Dashboard;

use Carbon\Carbon;
use Livewire\Component;

class FilterComponent extends Component
{
    public $selectedPeriod = '';
    public $showCustomDate = false;
    public $startDate;
    public $endDate;
    public $warehouseId;

    public function updatedSelectedPeriod()
    {
        $this->showCustomDate = ($this->selectedPeriod === 'custom');

        if (! $this->showCustomDate) {
            switch ($this->selectedPeriod) {
                case '1week':
                    $this->startDate = Carbon::now()->subWeek()->format('Y-m-d');
                    break;
                case '2weeks':
                    $this->startDate = Carbon::now()->subWeeks(2)->format('Y-m-d');
                    break;
                case '1month':
                    $this->startDate = Carbon::now()->subMonth()->format('Y-m-d');
                    break;
                case '2months':
                    $this->startDate = Carbon::now()->subMonths(2)->format('Y-m-d');
                    break;
                case '3months':
                    $this->startDate = Carbon::now()->subMonths(3)->format('Y-m-d');
                    break;
                default:
                    $this->startDate = null;
            }
            $this->endDate = Carbon::now()->format('Y-m-d');
        }

        $this->dispatch('dateUpdated', [
            'start' => $this->startDate,
            'end' => $this->endDate,
        ]);
    }

    public function render()
    {
        return view('livewire.dashboard.filter-component');
    }
}
