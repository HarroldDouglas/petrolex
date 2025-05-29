<?php

namespace App\Livewire\Components;

use App\Models\DistributionCenter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FilterComponent extends Component
{
    public $selectedPeriod = '';
    public $showCustomDate = false;
    public $startDate;
    public $endDate;
    public $warehouseId;
    public $centers = [];

    public function mount()
    {
        $user = Auth::user();

        // replace all db request with repository called
        if ($user && $user->isGlobal()) {
            $this->centers = DistributionCenter::all();
        } elseif ($user) {
            $centerIds = $user->centerPermissions()->pluck('distribution_center_id')->toArray();
            $this->centers = DistributionCenter::whereIn('id', $centerIds)->get();
        } else {
            $this->centers = collect([]);
        }
    }

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
        return view('livewire.components.filter-component');
    }
}
