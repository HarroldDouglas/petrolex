<?php

namespace App\Livewire\Components;

use App\Models\DistributionCenter;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class FilterComponent extends Component
{
    public string $scope = '';
    public string $selectedPeriod = '1week'; // Valeur par défaut
    public bool $showCustomDate = false;
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $warehouseId = '';
    public $centers = [];
    public string $eventName = 'filters-changed';

    public function mount(string $scope): void
    {
        $this->scope = $scope;
        $this->loadUserCenters();
        $this->initializeDateRange();
        $this->dispatchFiltersChanged();
    }

    private function loadUserCenters(): void
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user?->isGlobal()) {
            $this->centers = DistributionCenter::all();
        } elseif ($user) {
            $centerIds = $user->distributionCenters()->pluck('distribution_center_id')->toArray();
            $this->centers = DistributionCenter::whereIn('id', $centerIds)->get();
        } else {
            $this->centers = collect([]);
        }
    }

    private function initializeDateRange(): void
    {
        Log::debug('FilterComponent: Initializing date range', ['selectedPeriod' => $this->selectedPeriod]);
        $this->calculateDateRange();
    }

    public function updatedSelectedPeriod(): void
    {
        $this->handlePeriodChange();
    }

    public function updatedWarehouseId(): void
    {
        $this->dispatchFiltersChanged();
    }

    public function updatedStartDate($value): void
    {
        Log::debug('FilterComponent: startDate updated', ['value' => $value]);

        if ($this->showCustomDate) {
            $this->dispatchFiltersChanged();
        }
    }

    public function updatedEndDate($value): void
    {
        Log::debug('FilterComponent: endDate updated', ['value' => $value]);

        if ($this->showCustomDate) {
            $this->dispatchFiltersChanged();
        }
    }

    private function handlePeriodChange(): void
    {
        $this->showCustomDate = ($this->selectedPeriod === 'custom');

        if (! $this->showCustomDate) {
            $this->calculateDateRange();
        }

        $this->dispatchFiltersChanged();
    }

    private function calculateDateRange(): void
    {
        $now = Carbon::now();

        $this->startDate = match ($this->selectedPeriod) {
            '1week' => $now->copy()->subWeek()->format('Y-m-d'),
            '2weeks' => $now->copy()->subWeeks(2)->format('Y-m-d'),
            '1month' => $now->copy()->subMonth()->format('Y-m-d'),
            '2months' => $now->copy()->subMonths(2)->format('Y-m-d'),
            '3months' => $now->copy()->subMonths(3)->format('Y-m-d'),
            default => null,
        };

        $this->endDate = $now->format('Y-m-d');
    }

    private function dispatchFiltersChanged(): void
    {
        Log::debug('FilterComponent: Emitting event', [
            'eventName' => "filters-changed-{$this->scope}",
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'warehouseId' => $this->warehouseId,
        ]);

        $this->dispatch(
            "filters-changed-{$this->scope}",
            startDate: $this->startDate,
            endDate: $this->endDate,
            warehouseId: $this->warehouseId,
        );
    }

    public function render()
    {
        return view('livewire.components.filter-component');
    }
}
