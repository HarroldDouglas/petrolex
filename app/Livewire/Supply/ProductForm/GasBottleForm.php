<?php

namespace App\Livewire\Supply\ProductForm;

use App\Enums\ProductType;
use App\Http\Requests\Supply\RegisterGasBottleRequest;
use App\Models\ProductCategory;
use App\Models\SupplierDelivery;
use App\Models\SupplierDeliveryProductType;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

// TODO: Move database request to repository and call service instead of repository
class GasBottleForm extends Component
{
    public $showForm = false;
    public $selectedBottleType = '';
    public $incomingQuantity = '';
    public $outgoingQuantity = '';
    public $usedBottleTypes = [];
    public $bottleTypes = [];
    public $isEditing = false;
    public $editProductId = null;
    public SupplierDelivery $supplierDelivery;
    public $editProductData = null;

    protected $listeners = ['products-updated' => 'updateUsedTypes'];

    public function boot(ProductCategoryRepositoryInterface $productCategoryRepository)
    {
        $productCategories = $productCategoryRepository->getAllActiveByType(ProductType::BOTTLE());

        $this->bottleTypes = $productCategories
            ->map(function (ProductCategory $category) {
                $typeInstance = $category->productTypeInstance;

                return [
                    'id' => $category->id,
                    'name' => $typeInstance ? $typeInstance->name : 'Type inconnu',
                ];
            })
            ->toArray();
    }

    public function mount(SupplierDelivery $supplierDelivery, $editProductId = null, $editProductData = null)
    {
        $this->supplierDelivery = $supplierDelivery;

        $this->updateUsedTypes();

        if ($editProductId && $editProductData) {
            $this->processEditData($editProductId, $editProductData);
        }
    }

    #[On('close-gas-form')]
    public function closeForm()
    {
        $this->showForm = false;
    }

    protected function rules()
    {
        return $this->customRequest()->rules();
    }

    protected function messages()
    {
        return $this->customRequest()->messages();
    }

    protected function customRequest()
    {
        return new RegisterGasBottleRequest;
    }

    private function processEditData($productId, $productData)
    {
        $this->resetValidation();
        $this->resetErrorBag();

        $this->isEditing = true;
        $this->editProductId = $productId;

        $this->incomingQuantity = $productData['expected_quantity'] ?? '';
        $this->outgoingQuantity = $productData['bottles_out_quantity'] ?? 0;

        if (isset($productData['product_category_id'])) {
            $this->selectedBottleType = (string) $productData['product_category_id'];
        }

        $this->showForm = true;
    }

    #[On('edit-gas-bottle')]
    public function editProduct($productId, $productData)
    {
        try {
            $this->processEditData($productId, $productData);
        } catch (\Exception $e) {
            Log::error("Exception in editProduct: {$e->getMessage()}");
        }
    }

    public function save()
    {
        $validatedData = $this->validate();

        try {
            if (! $this->supplierDelivery) {
                throw new \Exception('Aucun approvisionnement trouvé');
            }

            if ($this->isEditing && $this->editProductId) {
                $product = SupplierDeliveryProductType::findOrFail($this->editProductId);
                $product->update([
                    'product_category_id' => $validatedData['selectedBottleType'],
                    'expected_quantity' => $validatedData['incomingQuantity'],
                    'bottles_out_quantity' => $validatedData['outgoingQuantity'] ?? 0,
                ]);

                session()->flash('success', 'Produit mis à jour avec succès!');
            } else {
                SupplierDeliveryProductType::create([
                    'supplier_delivery_id' => $this->supplierDelivery->id,
                    'product_category_id' => $validatedData['selectedBottleType'],
                    'expected_quantity' => $validatedData['incomingQuantity'],
                    'bottles_out_quantity' => $validatedData['outgoingQuantity'] ?? 0,
                ]);

                session()->flash('success', 'Produit ajouté avec succès!');
            }

            $this->updateUsedTypes();
            $this->resetForm();
            $this->dispatch('product-registered');

        } catch (\Exception $e) {
            Log::error('Error saving gas bottle product: '.$e->getMessage());
            session()->flash('error', 'Erreur lors de l\'enregistrement: '.$e->getMessage());
        }
    }

    public function updateUsedTypes($products = null)
    {
        $this->usedBottleTypes = $this->supplierDelivery->productTypes()
            ->whereHas('productCategory', function (\Illuminate\Database\Eloquent\Builder $query) {
                $query->where('product_type', ProductType::BOTTLE());
            })
            ->pluck('product_category_id')
            ->toArray();
    }

    private function resetForm()
    {
        $this->selectedBottleType = '';
        $this->incomingQuantity = '';
        $this->outgoingQuantity = '';
        $this->resetErrorBag();
        $this->showForm = false;
        $this->isEditing = false;
        $this->editProductId = null;
    }

    public function render()
    {
        return view('livewire.supply.product-form.gas-bottle-form');
    }
}
