<?php

namespace App\Livewire\Bottle;

use App\Services\Bottle\BottleService;
use Livewire\Component;

class HistoryModal extends Component
{
    public string $modalId = 'historyModal';
    public string $title = 'Historique de la bouteille';
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
        $bottleService = app(BottleService::class);
        $this->bottleId = $bottleId;
        $this->bottle = $bottleService->find($bottleId);

        if ($this->bottle) {
            $this->bottleHistory = $bottleService->getBottleHistory($bottleId);
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

    public function render()
    {
        return view('livewire.bottle.history-modal');
    }
}
