<?php

namespace App\Livewire\Bottle;

use App\Models\Bottle;
use App\Models\BottleMovement;
use Livewire\Component;

class HistoryModal extends Component
{
    public $modalId = 'bottleHistoryModal';
    public $title = 'Historique de la bouteille';
    public $bottle = null;
    public $bottleHistory = [];

    protected $listeners = ['showBottleHistory'];

    // Add parameter name to match the named parameter in the dispatch
    public function showBottleHistory($bottleId)
    {
        $this->bottle = Bottle::find($bottleId);

        if ($this->bottle) {
            $this->bottleHistory = BottleMovement::where('bottle_id', $bottleId)
                ->with(['distributionCenter'])
                ->orderBy('movement_date', 'desc')
                ->get();

            $this->dispatch('show-bottle-history');
        }
    }

    public function render()
    {
        return view('livewire.bottle.history-modal');
    }
}
