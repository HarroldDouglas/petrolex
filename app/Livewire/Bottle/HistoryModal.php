<?php

namespace App\Livewire\Bottle;

use App\Models\Bottle;
use App\Models\BottleMovement;
use Livewire\Component;

class HistoryModal extends Component
{
    public $showModal = false;
    public $bottle = null;
    public $bottleHistory = [];
    public $bottleId = null;

    protected $listeners = [
        'showBottleHistory' => 'showHistory',
        'closeModal' => 'closeModal',
    ];

    public function showHistory($bottleId)
    {
        $this->bottleId = $bottleId;
        $this->bottle = Bottle::find($bottleId);

        if ($this->bottle) {
            $this->bottleHistory = $this->loadBottleHistory($bottleId);
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->bottle = null;
        $this->bottleHistory = [];
        $this->bottleId = null;
    }

    private function loadBottleHistory($bottleId)
    {
        return BottleMovement::where('bottle_id', $bottleId)
            ->with(['bottle', 'distributionCenter', 'deliveryPerson.user',
                'customer', 'user'])
            ->select('bottle_id', 'distribution_center_id', 'delivery_person_id',
                'customer_id', 'user_id', 'movement_date', 'notes', 'type', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

    }

    public function render()
    {
        return view('livewire.bottle.history-modal');
    }
}
