<?php

namespace App\Livewire\Components;

use App\Enums\PeriodFilterStats;
use App\Models\User;
use App\Repositories\Contracts\DistributionCenterRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FilterComponent extends Component
{
    public string $scope = '';
    public string $selectedPeriod = '';
    public bool $showCustomDate = false;
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $distributionCenterId = '';
    public $centers = [];

    private DistributionCenterRepositoryInterface $distributionCenterRepository;

    public function boot(DistributionCenterRepositoryInterface $distributionCenterRepository)
    {
        $this->distributionCenterRepository = $distributionCenterRepository;
    }

    public function mount(string $scope): void
    {
        $this->scope = $scope;
        $this->selectedPeriod = PeriodFilterStats::default();
        $this->loadUserCenters();
        $this->initializeDateRange();
        $this->dispatchFiltersChanged();
    }

    private function loadUserCenters(): void
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user?->isGlobal()) {
            $this->centers = $this->distributionCenterRepository->all();
        } elseif ($user) {
            $centerIds = $user->distributionCenters()->pluck('distribution_center_id')->toArray();
            $this->centers = $this->distributionCenterRepository->getByIds($centerIds);
        } else {
            $this->centers = collect([]);
        }
    }

    private function initializeDateRange(): void
    {
        $this->calculateDateRange();
    }

    public function updatedSelectedPeriod(): void
    {
        $this->handlePeriodChange();
    }

    public function updatedDistributionCenterId(): void
    {
        $this->dispatchFiltersChanged();
    }

    public function updatedStartDate(): void
    {
        if ($this->showCustomDate) {
            $this->dispatchFiltersChanged();
        }
    }

    public function updatedEndDate(): void
    {
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
        $this->dispatch(
            "filters-changed-{$this->scope}",
            startDate: $this->startDate,
            endDate: $this->endDate,
            distributionCenterId: $this->distributionCenterId,
            selectedPeriod: $this->selectedPeriod,
        );
    }

    public function render()
    {
        return view('livewire.components.filter-component');
    }
}
