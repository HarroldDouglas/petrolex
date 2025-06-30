<?php

namespace App\Livewire\Supply\ProductForm;

use App\Enums\ProductType;
use App\Http\Requests\Supply\RegisterAccessoryRequest;
use App\Models\ProductCategory;
use App\Models\SupplierDelivery;
use App\Models\SupplierDeliveryProductType;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

// TODO: Move database request to repository and call service instead of repository
class AccessoryForm extends Component
{
    public $selectedAccessoryType = '';
    public $accessoryQuantity = '';
    public $showForm = false;
    public $accessoryTypes = [];
    public $usedAccessoryTypes = [];
    public $isEditing = false;
    public $editProductId = null;
    public SupplierDelivery $supplierDelivery;
    public $editProductData = null;

    protected $listeners = [
        'products-updated' => 'updateUsedTypes',
    ];

    public function boot(ProductCategoryRepositoryInterface $productCategoryRepository)
    {
        $productCategories = $productCategoryRepository->getAllActiveByType(ProductType::ACCESSORY());

        $this->accessoryTypes = $productCategories
            ->map(function (ProductCategory $category) {
                $typeInstance = $category->productTypeInstance;

                return [
                    'id' => $category->id,
                    'name' => $typeInstance?->name,
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

    protected function rules()
    {
        return $this->customRequest()->rules();
    }

    protected function messages()
    {
        return $this->customRequest()->messages();
    }

    protected function customRequest(): RegisterAccessoryRequest
    {
        return new RegisterAccessoryRequest;
    }

    #[On('close-accessory-form')]
    public function closeForm()
    {
        $this->showForm = false;
    }

    public function toggleForm()
    {
        $this->showForm = ! $this->showForm;
        $this->dispatch('accessory-form-toggled', $this->showForm);
    }

    private function processEditData($productId, $productData)
    {
        $this->resetValidation();
        $this->resetErrorBag();

        $this->isEditing = true;
        $this->editProductId = $productId;
        $this->accessoryQuantity = $productData['expected_quantity'] ?? '';

        if (isset($productData['product_category_id'])) {
            $this->selectedAccessoryType = (string) $productData['product_category_id'];
        }

        $this->showForm = true;
    }

    #[On('edit-accessory')]
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
                    'product_category_id' => $validatedData['selectedAccessoryType'],
                    'expected_quantity' => $validatedData['accessoryQuantity'],
                ]);

                session()->flash('success', 'Accessoire mis à jour avec succès!');
            } else {
                SupplierDeliveryProductType::create([
                    'supplier_delivery_id' => $this->supplierDelivery->id,
                    'product_category_id' => $validatedData['selectedAccessoryType'],
                    'expected_quantity' => $validatedData['accessoryQuantity'],
                    'bottles_out_quantity' => 0,
                ]);

                session()->flash('success', 'Accessoire ajouté avec succès!');
            }

            $this->updateUsedTypes();
            $this->resetForm();
            $this->dispatch('product-registered');

        } catch (\Exception $e) {
            Log::error('Error saving accessory product: '.$e->getMessage());
            session()->flash('error', 'Erreur lors de l\'enregistrement: '.$e->getMessage());
        }
    }

    public function updateUsedTypes($products = null)
    {
        $this->usedAccessoryTypes = $this->supplierDelivery->productTypes()
            ->whereHas('productCategory', function (\Illuminate\Database\Eloquent\Builder $query) {
                $query->where('product_type', ProductType::ACCESSORY());
            })
            ->pluck('product_category_id')
            ->toArray();
    }

    private function resetForm()
    {
        $this->selectedAccessoryType = '';
        $this->accessoryQuantity = '';
        $this->resetErrorBag();
        $this->showForm = false;
        $this->isEditing = false;
        $this->editProductId = null;
    }

    public function render()
    {
        return view('livewire.supply.product-form.accessory-form');
    }
}
