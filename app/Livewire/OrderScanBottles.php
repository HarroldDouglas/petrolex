<?php

namespace App\Livewire;

use App\Exceptions\BottleScanException;
use App\Models\Order;
use App\Services\Order\OrderBottleScanService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class OrderScanBottles extends Component
{
    public Order $order;
    public ?int $selectedBottleTypeId = null;
    public array $bottleTypes = [];
    public array $scannedBottles = [];
    public string $manualBarcode = '';
    public bool $showManualForm = false;
    public array $selectedBottles = [];
    public int $totalBottlesForType = 0;
    public int $scannedBottlesForType = 0;

    protected $listeners = [
        'bottleScanned' => '$refresh',
        'barcodeScanned' => 'processBarcode',
    ];

    protected $rules = [
        'manualBarcode' => 'required|string|min:3',
    ];

    protected OrderBottleScanService $bottleScanService;

    public function boot(OrderBottleScanService $bottleScanService)
    {
        $this->bottleScanService = $bottleScanService;
    }

    public function mount(Order $order)
    {
        $this->order = $order;
        $this->loadBottleTypes();

        if (! empty($this->bottleTypes)) {
            $this->selectedBottleTypeId = $this->bottleTypes[0]['id'];
            $this->updateSelectedBottleType();
        }
    }

    public function loadBottleTypes(): void
    {
        if (! isset($this->order->id)) {
            $this->bottleTypes = [];

            return;
        }

        // Utiliser le service pour récupérer les données des types de bouteilles
        $typeData = $this->bottleScanService->getBottleTypesWithScanStatus($this->order);

        // Transformer les données pour l'affichage dans la vue
        $this->bottleTypes = collect($typeData)->map(function (array $item): array {
            return [
                'id' => $item['bottle_type']->id,
                'name' => $item['bottle_type']->name,
                'total_quantity' => $item['total_quantity'],
                'scanned_quantity' => $item['scanned_quantity'],
                'is_complete' => $item['is_complete'],
            ];
        })->values()->toArray();
    }

    public function updateSelectedBottleType(): void
    {
        if (! $this->selectedBottleTypeId) {
            $this->scannedBottles = [];
            $this->totalBottlesForType = 0;
            $this->scannedBottlesForType = 0;

            return;
        }

        // Mettre à jour le comptage des bouteilles pour le type sélectionné
        foreach ($this->bottleTypes as $type) {
            if ($type['id'] == $this->selectedBottleTypeId) {
                $this->totalBottlesForType = $type['total_quantity'];
                $this->scannedBottlesForType = $type['scanned_quantity'];
                break;
            }
        }

        $this->loadScannedBottles();
    }

    public function loadScannedBottles(): void
    {
        if (! $this->selectedBottleTypeId) {
            $this->scannedBottles = [];

            return;
        }

        // Utiliser le service pour récupérer les bouteilles scannées
        $bottles = $this->bottleScanService->getScannedBottlesByType($this->order, $this->selectedBottleTypeId);

        // Transformer la collection en tableau pour l'affichage
        $this->scannedBottles = $bottles->map(function ($bottle): array {
            return [
                'id' => $bottle->id,
                'barcode' => $bottle->barcode,
                'timestamp' => $bottle->timestamp,
            ];
        })->toArray();

        $this->selectedBottles = [];
    }

    public function toggleManualForm(): void
    {
        $this->showManualForm = ! $this->showManualForm;
        $this->manualBarcode = '';
    }

    public function addManualBarcode(): void
    {
        $this->validate();
        $this->processBarcode(['barcode' => $this->manualBarcode]);
        $this->manualBarcode = '';
        $this->showManualForm = false;
    }

    public function processBarcode(array $data): void
    {
        $barcode = $data['barcode'] ?? null;

        if (! $barcode) {
            $this->dispatch('scanError', 'Code-barres vide ou invalide');

            return;
        }

        if (! $this->selectedBottleTypeId) {
            $this->dispatch('scanError', 'Veuillez d\'abord sélectionner un type de bouteille');

            return;
        }

        if ($this->scannedBottlesForType >= $this->totalBottlesForType) {
            $this->dispatch('scanError', 'Vous avez atteint la quantité maximale de bouteilles à scanner pour ce type');

            return;
        }

        try {
            // Utiliser le service pour scanner la bouteille
            $result = $this->bottleScanService->scanBottle($this->order, $barcode);

            session()->flash('message', 'Bouteille scannée avec succès');

            // Rafraîchir les données
            $this->loadBottleTypes();
            $this->updateSelectedBottleType();

            // Déclencher un événement pour l'animation
            $this->dispatch('bottleScanned', ['barcode' => $barcode]);
        } catch (BottleScanException $e) {
            // Capturer et afficher les erreurs spécifiques au scan de bouteilles
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            // Capturer les autres erreurs inattendues
            Log::error('Erreur inattendue lors du scan', [
                'exception' => $e->getMessage(),
                'order_id' => $this->order->id,
                'barcode' => $barcode,
            ]);
            session()->flash('error', 'Une erreur est survenue lors du scan de la bouteille.');
        }
    }

    public function removeSelected(): void
    {
        if (empty($this->selectedBottles)) {
            return;
        }

        try {
            // Utiliser le service pour supprimer les bouteilles sélectionnées
            $this->bottleScanService->removeBottles($this->order, $this->selectedBottles);

            session()->flash('message', 'Les bouteilles sélectionnées ont été supprimées.');

            // Rafraîchir les données
            $this->selectedBottles = [];
            $this->loadBottleTypes();
            $this->updateSelectedBottleType();
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression des bouteilles', [
                'exception' => $e->getMessage(),
                'order_id' => $this->order->id,
                'bottle_ids' => $this->selectedBottles,
            ]);
            session()->flash('error', 'Erreur lors de la suppression des bouteilles: '.$e->getMessage());
        }
    }

    public function selectAll(): void
    {
        if (count($this->selectedBottles) === count($this->scannedBottles)) {
            $this->selectedBottles = [];
        } else {
            $this->selectedBottles = collect($this->scannedBottles)->pluck('id')->toArray();
        }
    }

    public function render()
    {
        return view('livewire.order-scan-bottles');
    }
}
