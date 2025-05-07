<?php

namespace App\Livewire\Components;

use Carbon\Carbon;
use Livewire\Component;

abstract class BaseFilterComponent extends Component
{
    public $selectedPeriod = '';
    public $showCustomDate = false;
    public $startDate;
    public $endDate;
    public $warehouseId;

    protected $listeners = ['dateUpdated'];

    public function updatedSelectedPeriod()
    {
        $this->showCustomDate = ($this->selectedPeriod === 'custom');

        if (! $this->showCustomDate) {
            $this->calculateDates();
        }

        $this->emitDateUpdated();
    }

    protected function calculateDates()
    {
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

    protected function emitDateUpdated()
    {
        $this->dispatch('dateUpdated', [
            'start' => $this->startDate,
            'end' => $this->endDate,
            'warehouse' => $this->warehouseId,
        ]);
    }

    protected function getWarehouses()
    {
        return collect([
            ['id' => 1, 'name' => 'Point YDE 123'],
            ['id' => 2, 'name' => 'Point DLA 456'],
            ['id' => 3, 'name' => 'Point BAF 789'],
            ['id' => 4, 'name' => 'Point YDE 321'],
            ['id' => 5, 'name' => 'Point DLA 654'],
        ]);
    }

    public function render()
    {
        return view('livewire.components.filter-component', [
            'warehouses' => $this->getWarehouses(),
        ]);
    }
}
