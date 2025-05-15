<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class ScanBottles extends Component
{
    public $supplyId;
    public $productId;
    public $bottleType;
    public $quantity;
    public $scanned = 0;
    public $bottles = [];
    public $manualBarcode = '';
    public $showManualForm = false;
    public $selectedBottles = [];
    public $supplyTitle;
    public $supplyDate;

    public $isIncomingMode = true;  // Default to incoming mode
    public $scannedIncoming = 0;
    public $scannedOutgoing = 0;
    public $outgoingQuantity = 10; // Default outgoing quantity
    public $incomingBottles = [];
    public $outgoingBottles = [];

    public function mount($supplyId, $productId, $bottleType, $quantity, $outgoingQuantity = 10, $supplyTitle = null, $supplyDate = null)
    {
        $this->supplyId = $supplyId;
        $this->productId = $productId;
        $this->bottleType = $bottleType;
        $this->quantity = $quantity;
        $this->outgoingQuantity = $outgoingQuantity;
        $this->supplyTitle = $supplyTitle ?? 'Approvisionnement';
        $this->supplyDate = $supplyDate;

        // Initialize tracking arrays
        $this->incomingBottles = [];
        $this->outgoingBottles = [];
        $this->updateActiveBottles();
    }

    public function toggleManualForm()
    {
        $this->showManualForm = ! $this->showManualForm;
    }

    public function addManualBarcode()
    {
        $this->validate([
            'manualBarcode' => 'required|string|min:3',
        ]);

        $this->addBottle($this->manualBarcode);
        $this->manualBarcode = '';
        $this->showManualForm = false;
    }

    public function toggleMode()
    {
        $this->isIncomingMode = ! $this->isIncomingMode;
        $this->selectedBottles = []; // Reset selection when toggling mode
        $this->updateActiveBottles();
    }

    private function updateActiveBottles()
    {
        // Update the active bottles based on the current mode
        if ($this->isIncomingMode) {
            $this->bottles = $this->incomingBottles;
            $this->scanned = $this->scannedIncoming;
        } else {
            $this->bottles = $this->outgoingBottles;
            $this->scanned = $this->scannedOutgoing;
        }
    }

    #[On('barcode-scanned')]
    public function handleScannedBarcode($data)
    {
        $this->addBottle($data['barcode']);
    }

    public function addBottle($barcode)
    {
        if ($this->isIncomingMode) {
            // Add to incoming bottles
            if ($this->scannedIncoming < $this->quantity) {
                $this->incomingBottles[] = [
                    'id' => count($this->incomingBottles) + 1,
                    'barcode' => $barcode,
                    'timestamp' => now()->format('Y-m-d H:i:s'),
                ];

                $this->scannedIncoming++;
                $this->bottles = $this->incomingBottles;
                $this->scanned = $this->scannedIncoming;
                $this->dispatch('bottleAdded');
            } else {
                session()->flash('warning', 'Vous avez atteint la quantité maximale de bouteilles entrantes à scanner.');
            }
        } else {
            // Add to outgoing bottles
            if ($this->scannedOutgoing < $this->outgoingQuantity) {
                $this->outgoingBottles[] = [
                    'id' => count($this->outgoingBottles) + 1,
                    'barcode' => $barcode,
                    'timestamp' => now()->format('Y-m-d H:i:s'),
                ];

                $this->scannedOutgoing++;
                $this->bottles = $this->outgoingBottles;
                $this->scanned = $this->scannedOutgoing;
                $this->dispatch('bottleAdded');
            } else {
                session()->flash('warning', 'Vous avez atteint la quantité maximale de bouteilles sortantes à scanner.');
            }
        }
    }

    public function toggleSelect($id)
    {
        if (in_array($id, $this->selectedBottles)) {
            $this->selectedBottles = array_diff($this->selectedBottles, [$id]);
        } else {
            $this->selectedBottles[] = $id;
        }
    }

    public function selectAll()
    {
        // Get IDs of bottles in the current mode
        $bottleIds = array_column($this->bottles, 'id');

        // Check if all bottles are already selected
        $allSelected = count(array_intersect($bottleIds, $this->selectedBottles)) === count($bottleIds);

        if ($allSelected) {
            // Deselect all
            $this->selectedBottles = array_values(array_diff($this->selectedBottles, $bottleIds));
        } else {
            // Select all (make sure we don't have duplicates)
            $this->selectedBottles = array_values(array_unique(array_merge($this->selectedBottles, $bottleIds)));
        }
    }

    public function removeSelected()
    {
        if (empty($this->selectedBottles)) {
            return;
        }

        if ($this->isIncomingMode) {
            $this->incomingBottles = array_filter($this->incomingBottles, function ($bottle) {
                return ! in_array($bottle['id'], $this->selectedBottles);
            });
            $this->scannedIncoming = count($this->incomingBottles);
            $this->bottles = $this->incomingBottles;
            $this->scanned = $this->scannedIncoming;
        } else {
            $this->outgoingBottles = array_filter($this->outgoingBottles, function ($bottle) {
                return ! in_array($bottle['id'], $this->selectedBottles);
            });
            $this->scannedOutgoing = count($this->outgoingBottles);
            $this->bottles = $this->outgoingBottles;
            $this->scanned = $this->scannedOutgoing;
        }

        $this->selectedBottles = [];
        session()->flash('message', 'Les bouteilles sélectionnées ont été supprimées.');
    }

    public function render()
    {
        return view('livewire.scan-bottles');
    }
}
