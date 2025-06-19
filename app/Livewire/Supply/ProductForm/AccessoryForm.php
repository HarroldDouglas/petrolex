<?php

namespace App\Livewire\Supply\ProductForm;

use App\Enums\ProductType;
use App\Http\Requests\Supply\RegisterAccessoryRequest;
use App\Models\AccessoryType;
use App\Models\SupplierDelivery;
use App\Models\SupplierDeliveryProductType;
use App\Repositories\Contracts\AccessoryRepositoryInterface;
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

    public function boot(AccessoryRepositoryInterface $accessoryRepository)
    {
        $this->accessoryTypes = $accessoryRepository->getActiveProducts()
            ->map(function (AccessoryType $type) {
                return ['id' => $type->id, 'name' => $type->name];
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

        if (isset($productData['accessory_type_id'])) {
            $this->selectedAccessoryType = (string) $productData['accessory_type_id'];
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
                    'accessory_type_id' => $validatedData['selectedAccessoryType'],
                    'expected_quantity' => $validatedData['accessoryQuantity'],
                ]);

                session()->flash('success', 'Accessoire mis à jour avec succès!');
            } else {
                SupplierDeliveryProductType::create([
                    'supplier_delivery_id' => $this->supplierDelivery->id,
                    'product_type' => ProductType::ACCESSORY()->value,
                    'bottle_type_id' => null,
                    'accessory_type_id' => $validatedData['selectedAccessoryType'],
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
            ->where('product_type', ProductType::ACCESSORY()->value)
            ->pluck('accessory_type_id')
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
