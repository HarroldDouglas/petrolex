<?php

namespace App\Livewire\Supply;

use App\Enums\BottleStatus;
use App\Enums\ProductType;
use App\Enums\SupplierDeliveryBottleMovementType;
use App\Models\Bottle;
use App\Models\ProductCategory;
use App\Models\SupplierDelivery;
use App\Models\SupplierDeliveryBottle;
use App\Models\SupplierDeliveryProductType;
use App\Services\Supply\SupplyDeliveryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class ScanBottles extends Component
{
    public SupplierDelivery $supply;
    public ?SupplierDeliveryProductType $selectedProductType = null;

    public $supplyId;
    public $selectedProductId = null;
    public $productId;
    public $bottleType;
    public $quantity = 0;
    public $outgoingQuantity = 0;
    public $manualBarcode = '';
    public $showManualForm = false;
    public $selectedBottles = [];
    public $supplyTitle;
    public $supplyDate;

    public $isIncomingMode = true;
    public $availableProducts = [];
    public $bottles = [];
    public $scanned = 0;

    protected $listeners = [
        'bottleAdded' => '$refresh',
        'barcodeScanned' => 'processBarcode',
    ];
    protected $supplyDeliveryService;

    protected $rules = [
        'manualBarcode' => 'required|string|min:3',
    ];

    public function boot(SupplyDeliveryService $supplyDeliveryService)
    {
        $this->supplyDeliveryService = $supplyDeliveryService;
    }

    public function mount(SupplierDelivery $supply)
    {
        $this->supply = $supply;
        $this->supplyId = $supply->id;
        $this->supplyTitle = $supply->delivery_number ?? 'Approvisionnement';
        $this->supplyDate = $supply->supply_date;

        $this->loadAvailableProducts();

        if (! empty($this->availableProducts)) {
            $this->selectedProductId = $this->availableProducts[0]['id'];
            $this->updateSelectedProduct();
        }
    }

    public function loadAvailableProducts()
    {
        if (! $this->supply) {
            $this->availableProducts = [];

            return;
        }

        $this->availableProducts = $this->supply->productTypes()
            ->with(['productCategory', 'deliveryBottles'])
            ->whereHas('productCategory', function (\Illuminate\Database\Eloquent\Builder $query) {
                $query->where('product_type', ProductType::BOTTLE());
            })
            ->get()
            ->map(function (SupplierDeliveryProductType $product) {
                /** @var ProductCategory */
                $productCategory = $product->productCategory;

                return [
                    'id' => $product->id,
                    'product_category_id' => $productCategory->id,
                    'bottle_type_name' => $productCategory->name,
                    'quantity' => $product->expected_quantity,
                    'incoming_scanned' => $product->incoming_scanned_count,
                    'outgoing_quantity' => $product->bottles_out_quantity,
                    'outgoing_scanned' => $product->outgoing_scanned_count,
                    'incoming_done' => $product->incoming_done,
                    'outgoing_done' => $product->outgoing_done,
                ];
            })->toArray();
    }

    public function updateSelectedProduct()
    {
        if (! $this->selectedProductId) {
            $this->selectedProductType = null;
            $this->productId = null;
            $this->bottleType = null;
            $this->quantity = 0;
            $this->outgoingQuantity = 0;
            $this->bottles = [];

            return;
        }

        $this->selectedProductType = SupplierDeliveryProductType::find($this->selectedProductId);

        foreach ($this->availableProducts as $product) {
            if ($product['id'] == $this->selectedProductId) {
                $this->productId = $product['id'];
                $this->bottleType = $product['bottle_type_name'];
                $this->quantity = $product['quantity'];
                $this->outgoingQuantity = $product['outgoing_quantity'];

                $this->loadBottles();
                break;
            }
        }
    }

    public function loadBottles()
    {
        if (! $this->selectedProductType) {
            $this->bottles = [];
            $this->scanned = 0;

            return;
        }

        $query = SupplierDeliveryBottle::where('supplier_delivery_product_type_id', $this->selectedProductType->id);

        if ($this->isIncomingMode) {
            $query->incoming();
        } else {
            $query->outgoing();
        }

        $bottleRecords = $query->with('bottle')->get();

        $this->bottles = $bottleRecords->map(function ($record) {
            return [
                'id' => $record->id,
                'barcode' => $record->bottle->barcode,
                'timestamp' => $record->created_at,
            ];
        })->toArray();

        $this->scanned = count($this->bottles);
        $this->selectedBottles = [];
    }

    public function processBarcode($data)
    {
        $barcode = $data['barcode'] ?? null;

        if (! $barcode) {
            $this->dispatch('scanError', 'Code-barres vide ou invalide');

            return;
        }

        if (! $this->productId) {
            $this->dispatch('scanError', 'Veuillez d\'abord sélectionner un type de bouteille');

            return;
        }

        try {
            $bottle = new Bottle([
                'product_category_id' => $this->selectedProductType->product_category_id,
                'barcode' => $barcode,
            ]);

            /** @var Bottle $bottle */
            $this->bottles[] = [
                'id' => $bottle->id,
                'barcode' => $barcode,
                'timestamp' => now(),
            ];

            $this->scanned = count($this->bottles);

            $this->dispatch('bottleAdded', ['barcode' => $barcode]);

        } catch (\Exception $e) {
            $this->dispatch('scanError', 'Erreur lors du traitement du code-barres: '.$e->getMessage());
        }
    }

    #[On('barcode-scanned')]
    public function handleScannedBarcode($barcode)
    {
        $this->addBottle($barcode);
    }

    public function toggleManualForm()
    {
        $this->showManualForm = ! $this->showManualForm;
        $this->manualBarcode = '';
    }

    public function addManualBarcode()
    {
        $this->validate();
        Log::info('addManualBarcode called', ['barcode' => $this->manualBarcode, 'productId' => $this->selectedProductId]);
        $result = $this->addBottle($this->manualBarcode);
        if ($result) {
            $this->manualBarcode = '';
            $this->showManualForm = false;
        }
    }

    public function updatedIsIncomingMode()
    {
        $this->selectedBottles = [];
        $this->loadBottles();

        Log::debug('Mode changed via updatedIsIncomingMode', [
            'isIncomingMode' => $this->isIncomingMode,
            'bottleCount' => count($this->bottles),
        ]);
    }

    public function addBottle($barcode): bool
    {
        if (! $this->selectedProductType) {
            Log::warning('addBottle: no selectedProductType');
            session()->flash('error', 'Veuillez d\'abord sélectionner un type de bouteille.');

            return false;
        }

        $movementType = $this->isIncomingMode ?
            SupplierDeliveryBottleMovementType::INCOMING() :
            SupplierDeliveryBottleMovementType::OUTGOING();

        $maxAllowed = $this->isIncomingMode ? $this->quantity : $this->outgoingQuantity;
        $currentCount = $this->isIncomingMode ?
            $this->selectedProductType->incoming_scanned_count :
            $this->selectedProductType->outgoing_scanned_count;

        Log::info('addBottle', ['barcode' => $barcode, 'incoming' => $this->isIncomingMode, 'current' => $currentCount, 'max' => $maxAllowed]);

        if ($currentCount >= $maxAllowed) {
            $direction = $this->isIncomingMode ? 'entrantes' : 'sortantes';
            session()->flash('warning', "Vous avez atteint la quantité maximale de bouteilles {$direction} à scanner.");

            return false;
        }

        try {
            DB::beginTransaction();

            $bottle = Bottle::where('barcode', $barcode)->first();

            if (! $bottle && $this->isIncomingMode) {
                $product = \App\Models\Product::firstOrCreate([
                    'product_category_id' => $this->selectedProductType->product_category_id,
                ]);

                $bottle = Bottle::create([
                    'barcode' => $barcode,
                    'product_id' => $product->id,
                    'distribution_center_id' => $this->supply->distribution_center_id,
                    'is_filled' => true,
                    'status' => BottleStatus::PENDING_RECEPTION(),
                ]);
                Log::info('addBottle: new bottle created', ['bottle_id' => $bottle->id]);
            } elseif (! $bottle) {
                DB::rollBack();
                session()->flash('error', "Bouteille avec code {$barcode} non trouvée dans le système.");

                return false;
            }

            $existingBottle = SupplierDeliveryBottle::withTrashed()
                ->where('supplier_delivery_product_type_id', $this->selectedProductType->id)
                ->where('bottle_id', $bottle->id)
                ->first();

            if ($existingBottle) {
                if ($existingBottle->trashed()) {
                    $existingBottle->restore();
                    $existingBottle->update(['movement_type' => $movementType]);
                } else {
                    DB::rollBack();
                    session()->flash('warning', "Cette bouteille a déjà été scannée pour cet approvisionnement.");

                    return false;
                }
            } else {
                if ($this->isIncomingMode && ! $bottle->status->equals(BottleStatus::PENDING_RECEPTION())) {
                    $bottle->update([
                        'status' => BottleStatus::PENDING_RECEPTION(),
                        'is_filled' => true,
                        'distribution_center_id' => $this->supply->distribution_center_id,
                    ]);
                }

                SupplierDeliveryBottle::create([
                    'supplier_delivery_product_type_id' => $this->selectedProductType->id,
                    'bottle_id' => $bottle->id,
                    'movement_type' => $movementType,
                ]);
            }

            DB::commit();

            $this->loadBottles();
            $this->loadAvailableProducts();

            session()->flash('message', "Bouteille {$barcode} ajoutée avec succès.");
            Log::info('addBottle: success', ['barcode' => $barcode]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('addBottle exception', ['error' => $e->getMessage()]);
            session()->flash('error', 'Erreur lors de l\'ajout de la bouteille: '.$e->getMessage());

            return false;
        }
    }

    public function removeSelected()
    {
        if (empty($this->selectedBottles)) {
            return;
        }

        try {
            DB::beginTransaction();

            SupplierDeliveryBottle::whereIn('id', $this->selectedBottles)->delete();

            DB::commit();

            $this->selectedBottles = [];
            $this->loadBottles();
            $this->loadAvailableProducts();

            session()->flash('message', 'Les bouteilles sélectionnées ont été supprimées.');

        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'Erreur lors de la suppression: '.$e->getMessage());
        }
    }

    public function selectAll()
    {
        if (count($this->selectedBottles) === count($this->bottles)) {
            $this->selectedBottles = [];
        } else {
            $this->selectedBottles = collect($this->bottles)->pluck('id')->toArray();
        }
    }

    public function render()
    {
        return view('livewire.supply.scan-bottles');
    }
}
