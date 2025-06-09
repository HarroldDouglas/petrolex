<?php

namespace App\Livewire\Bottle;

use App\Models\User;
use App\Services\Bottle\BottleService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class StatsOverview extends Component
{
    public int $inStock = 0;
    public int $withDeliveryPerson = 0;
    public int $withClient = 0;
    public int $lostStolen = 0;

    private BottleService $bottleService;

    public function boot(BottleService $bottleService): void
    {
        $this->bottleService = $bottleService;
    }

    public function mount(): void
    {
        $this->loadStats();
    }

    #[On('filters-changed-bottle')]
    public function handleFiltersChanged(?string $startDate = null, ?string $endDate = null, ?string $distributionCenterId = null): void
    {
        $this->loadStats($startDate, $endDate, $distributionCenterId);
    }

    private function loadStats(?string $startDate = null, ?string $endDate = null, ?string $distributionCenterId = null): void
    {
        $distributionCenterIds = [];

        if ($distributionCenterId === null || $distributionCenterId === '' || $distributionCenterId === 'all') {
            /** @var User|null $user */
            $user = Auth::user();
            if ($user) {
                $distributionCenterIds = $user->accessibleDistributionCenters()->pluck('distribution_center_id')->toArray();
                if (empty($distributionCenterIds)) {
                    $distributionCenterIds = [-1];
                }
            }
        } else {
            $distributionCenterIds = [$distributionCenterId];
        }

        $stats = $this->bottleService->getStats($startDate, $endDate, $distributionCenterIds);

        $this->inStock = $stats->inStock;
        $this->withDeliveryPerson = $stats->withDeliveryPerson;
        $this->withClient = $stats->withClient;
        $this->lostStolen = $stats->lostStolen;
    }

    public function render()
    {
        return view('livewire.bottle.stats-overview');
    }
}
