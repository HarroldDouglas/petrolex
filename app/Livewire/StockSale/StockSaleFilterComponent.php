<?php

namespace App\Livewire\StockSale;

use App\Livewire\Components\BaseFilterComponent;
use App\Models\Warehouse;

class StockSaleFilterComponent extends BaseFilterComponent
{
    protected function getWarehouses()
    {
        // Vous pouvez personnaliser la requête ici si nécessaire
        return Warehouse::active()->get();
    }
}
