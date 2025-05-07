<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Components\BaseFilterComponent;
use App\Models\Warehouse;

class DashboardFilterComponent extends BaseFilterComponent
{
    protected function getWarehouses()
    {
        return Warehouse::active()->get();
    }
}
