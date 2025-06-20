<?php

namespace App\Livewire;

use App\Exceptions\BottleScanException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemBottle;
use App\Services\Order\OrderBottleScanService;
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

        $orderItems = $this->bottleScanService->getOrderItemsGroupedByBottleType($this->order);

        $this->bottleTypes = $orderItems->map(function (OrderItem $orderItem): array {
            return [
                'id' => $orderItem->product->bottle->bottle_type_id,
                'name' => $orderItem->product->bottle->bottleType->name,
                'total_quantity' => $orderItem->quantity,
                'scanned_quantity' => $orderItem->orderItemBottles->count(),
                'is_complete' => $orderItem->orderItemBottles->count() >= $orderItem->quantity,
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

        $orderItemBottles = $this->bottleScanService->getScannedBottlesByType($this->order, $this->selectedBottleTypeId);

        $this->scannedBottles = $orderItemBottles->map(function (OrderItemBottle $orderItemBottle): array {
            return [
                'id' => $orderItemBottle->bottle->id,
                'barcode' => $orderItemBottle->bottle->barcode,
                'timestamp' => $orderItemBottle->created_at,
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
            $this->dispatch('scanError', ['message' => 'Code-barres vide ou invalide']);

            return;
        }

        if (! $this->selectedBottleTypeId) {
            $this->dispatch('scanError', ['message' => 'Veuillez d\'abord sélectionner un type de bouteille']);

            return;
        }

        if ($this->scannedBottlesForType >= $this->totalBottlesForType) {
            $this->dispatch('scanError', ['message' => 'Vous avez atteint la quantité maximale de bouteilles à scanner pour ce type']);

            return;
        }

        try {
            $orderItem = $this->bottleScanService->scanBottle($this->order, $barcode);
            session()->flash('message', 'Bouteille scannée avec succès');

            $this->loadBottleTypes();
            $this->updateSelectedBottleType();

            $this->dispatch('bottleScanned', ['barcode' => $barcode]);
        } catch (BottleScanException $e) {
            session()->flash('error', $e->getMessage());
            $this->dispatch('scanError', ['message' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error('Erreur inattendue lors du scan', [
                'exception' => $e->getMessage(),
                'order_id' => $this->order->id,
                'barcode' => $barcode,
            ]);
            session()->flash('error', 'Une erreur est survenue lors du scan de la bouteille.');
            $this->dispatch('scanError', ['message' => 'Une erreur est survenue lors du scan de la bouteille.']);
        }
    }

    public function removeSelected(): void
    {
        if (empty($this->selectedBottles)) {
            return;
        }

        try {
            $this->bottleScanService->removeBottles($this->order, $this->selectedBottles);
            session()->flash('message', 'Les bouteilles sélectionnées ont été supprimées.');

            $this->selectedBottles = [];
            $this->loadScannedBottles();
            $this->loadBottleTypes();
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
        return view('livewire.order.order-scan-bottles');
    }
}
