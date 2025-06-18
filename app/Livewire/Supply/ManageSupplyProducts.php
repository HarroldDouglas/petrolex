<?php

namespace App\Livewire\Supply;

use App\Enums\ProductType;
use App\Repositories\Contracts\SupplierDeliveryRepositoryInterface;
use Livewire\Attributes\On;
use Livewire\Component;

class ManageSupplyProducts extends Component
{
    public $selectedFormType;
    public $supplyId;
    public $supplierDelivery;
    public $editProductId = null;
    public $editProductData = null;

    protected SupplierDeliveryRepositoryInterface $supplierDeliveryRepository;

    public function boot(SupplierDeliveryRepositoryInterface $supplierDeliveryRepository)
    {
        $this->supplierDeliveryRepository = $supplierDeliveryRepository;
    }

    public function mount(int $supplyId)
    {
        $this->supplyId = $supplyId;
        $this->loadSupplierDelivery();
        $this->selectedFormType = ProductType::BOTTLE()->value;
    }

    protected function loadSupplierDelivery()
    {
        $this->supplierDelivery = $this->supplierDeliveryRepository->getWithProducts($this->supplyId);

        if (! $this->supplierDelivery) {
            abort(404, 'Approvisionnement non trouvé');
        }
    }

    #[On('set-gas-bottle-form')]
    public function handleGasBottleEdit($productId, $productData)
    {
        $this->editProductId = $productId;
        $this->editProductData = $productData;

        if ($this->selectedFormType !== ProductType::BOTTLE()->value) {
            $this->selectedFormType = ProductType::BOTTLE()->value;
        } else {
            $this->dispatch('edit-gas-bottle', $productId, $productData);
        }
    }

    #[On('set-accessory-form')]
    public function handleAccessoryEdit($productId, $productData)
    {
        $this->editProductId = $productId;
        $this->editProductData = $productData;

        if ($this->selectedFormType !== ProductType::ACCESSORY()->value) {
            $this->selectedFormType = ProductType::ACCESSORY()->value;
        } else {
            $this->dispatch('edit-accessory', $productId, $productData);
        }
    }

    public function updatedSelectedFormType($value)
    {
        $this->reset('editProductId', 'editProductData');

        if ($value === ProductType::BOTTLE()->value) {
            $this->dispatch('close-accessory-form');
        } else {
            $this->dispatch('close-gas-form');
        }
    }

    public function render()
    {
        return view('livewire.supply.manage-supply-products', [
            'supplierDelivery' => $this->supplierDelivery,
        ]);
    }
}
