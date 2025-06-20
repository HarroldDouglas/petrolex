<?php

namespace App\Livewire\Order;

use App\Enums\ProductType;
use App\Models\Bottle;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemBottle;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class OrderScanBottles extends Component
{
    public Order $order;
    public $selectedBottleTypeId = null;
    public $bottleTypes = [];
    public $scannedBottles = [];
    public $manualBarcode = '';
    public $showManualForm = false;
    public $selectedBottles = [];
    public $totalBottlesForType = 0;
    public $scannedBottlesForType = 0;

    protected $listeners = [
        'bottleScanned' => '$refresh',
        'barcodeScanned' => 'processBarcode',
    ];

    protected $rules = [
        'manualBarcode' => 'required|string|min:3',
    ];

    public function mount(Order $order)
    {
        $this->order = $order;
        $this->loadBottleTypes();

        if (! empty($this->bottleTypes)) {
            $this->selectedBottleTypeId = $this->bottleTypes[0]['id'];
            $this->updateSelectedBottleType();
        }
    }

    public function loadBottleTypes()
    {
        if (! $this->order) {
            $this->bottleTypes = [];

            return;
        }

        $bottleItems = $this->order->items()
            ->whereHas('product', function ($query) {
                $query->where('product_type', ProductType::BOTTLE());
            })
            ->with(['product.bottle.bottleType'])
            ->get();

        // Regrouper par type de bouteille
        $groupedItems = [];
        foreach ($bottleItems as $item) {
            $bottleType = $item->product->bottle->bottleType;
            $bottleTypeId = $bottleType->id;

            if (! isset($groupedItems[$bottleTypeId])) {
                $groupedItems[$bottleTypeId] = [
                    'bottle_type' => $bottleType,
                    'total_quantity' => 0,
                    'scanned_quantity' => 0,
                ];
            }

            $groupedItems[$bottleTypeId]['total_quantity'] += $item->quantity;
            $groupedItems[$bottleTypeId]['scanned_quantity'] += $item->orderItemBottles()->count();
        }

        // Transformer en tableau pour la vue
        $this->bottleTypes = collect($groupedItems)->map(function ($item) {
            $isComplete = $item['scanned_quantity'] >= $item['total_quantity'];

            return [
                'id' => $item['bottle_type']->id,
                'name' => $item['bottle_type']->name,
                'total_quantity' => $item['total_quantity'],
                'scanned_quantity' => $item['scanned_quantity'],
                'is_complete' => $isComplete,
            ];
        })->values()->toArray();
    }

    public function updateSelectedBottleType()
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

    public function loadScannedBottles()
    {
        if (! $this->selectedBottleTypeId) {
            $this->scannedBottles = [];

            return;
        }

        // Récupérer toutes les bouteilles scannées pour ce type de bouteille
        $orderItems = $this->order->items()->whereHas('product.bottle', function ($query) {
            $query->where('bottle_type_id', $this->selectedBottleTypeId);
        })->pluck('id')->toArray();

        $orderItemBottles = OrderItemBottle::whereIn('order_item_id', $orderItems)
            ->with('bottle')
            ->get();

        $this->scannedBottles = $orderItemBottles->map(function ($orderItemBottle) {
            return [
                'id' => $orderItemBottle->bottle->id,
                'barcode' => $orderItemBottle->bottle->barcode,
                'timestamp' => $orderItemBottle->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        $this->selectedBottles = [];
    }

    public function toggleManualForm()
    {
        $this->showManualForm = ! $this->showManualForm;
        $this->manualBarcode = '';
    }

    public function addManualBarcode()
    {
        $this->validate();
        $this->processBarcode(['barcode' => $this->manualBarcode]);
        $this->manualBarcode = '';
        $this->showManualForm = false;
    }

    public function processBarcode($data)
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
            DB::beginTransaction();

            // Vérifier si la bouteille existe déjà
            $bottle = Bottle::where('barcode', $barcode)->first();

            if (! $bottle) {
                session()->flash('error', "Bouteille avec code {$barcode} non trouvée dans le système.");
                DB::rollBack();

                return;
            }

            // Vérifier si le type de bouteille correspond
            if ($bottle->bottle_type_id !== (int) $this->selectedBottleTypeId) {
                session()->flash('error', 'Cette bouteille est de type incorrect pour cet élément de commande.');
                DB::rollBack();

                return;
            }

            // Vérifier si la bouteille a déjà été scannée pour cette commande
            $alreadyScanned = OrderItemBottle::whereHas('orderItem', function ($query) {
                $query->where('order_id', $this->order->id);
            })->whereHas('bottle', function ($query) use ($bottle) {
                $query->where('id', $bottle->id);
            })->exists();

            if ($alreadyScanned) {
                session()->flash('warning', 'Cette bouteille a déjà été scannée pour cette commande.');
                DB::rollBack();

                return;
            }

            // Trouver un OrderItem approprié pour cette bouteille
            $orderItem = $this->order->items()
                ->whereHas('product.bottle', function ($query) use ($bottle) {
                    $query->where('bottle_type_id', $bottle->bottle_type_id);
                })
                ->get()
                ->filter(function ($item) {
                    return $item->scanned_bottles_count < $item->quantity;
                })
                ->first();

            if (! $orderItem) {
                session()->flash('error', 'Aucun élément de commande disponible pour ce type de bouteille.');
                DB::rollBack();

                return;
            }

            // Associer la bouteille à l'élément de commande
            OrderItemBottle::create([
                'order_item_id' => $orderItem->id,
                'bottle_id' => $bottle->id,
            ]);

            DB::commit();

            $this->loadBottleTypes();
            $this->updateSelectedBottleType();

            session()->flash('message', "Bouteille {$barcode} ajoutée avec succès.");

            // Dispatch un événement pour l'animation
            $this->dispatch('bottleScanned', ['barcode' => $barcode]);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', "Erreur lors de l'ajout de la bouteille: ".$e->getMessage());
        }
    }

    public function removeSelected()
    {
        if (empty($this->selectedBottles)) {
            return;
        }

        try {
            DB::beginTransaction();

            foreach ($this->selectedBottles as $bottleId) {
                OrderItemBottle::whereHas('orderItem', function ($query) {
                    $query->where('order_id', $this->order->id);
                })->whereHas('bottle', function ($query) use ($bottleId) {
                    $query->where('id', $bottleId);
                })->delete();
            }

            DB::commit();

            $this->selectedBottles = [];
            $this->loadBottleTypes();
            $this->updateSelectedBottleType();

            session()->flash('message', 'Les bouteilles sélectionnées ont été supprimées.');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la suppression: '.$e->getMessage());
        }
    }

    public function selectAll()
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
