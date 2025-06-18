<?php

namespace App\Livewire\Supply;

use App\Enums\ProductType;
use App\Models\SupplierDelivery;
use App\Models\SupplierDeliveryProductType;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ProductsList extends Component
{
    public $products = [];
    public $productToDelete = null;
    public $supplierDelivery;

    protected $listeners = [
        'product-registered' => 'refreshProducts',
    ];

    public function mount(?SupplierDelivery $supplierDelivery = null)
    {
        $this->supplierDelivery = $supplierDelivery;
        $this->formatProductsForView();
    }

    /**
     * Transformer les objets SupplierDeliveryProductType en format attendu par la vue
     */
    protected function formatProductsForView()
    {
        $this->products = [];

        if (! $this->supplierDelivery) {
            return;
        }

        foreach ($this->supplierDelivery->productTypes as $product) {
            $formattedProduct = [
                'id' => $product->id,
                'type' => $product->product_type->value,
            ];

            if ($product->product_type->value === ProductType::BOTTLE()->value) {
                $formattedProduct['detail'] = $product->bottle_type_id;
                $formattedProduct['detail_name'] = $product->bottleType ? $product->bottleType->name : 'Type inconnu';
                $formattedProduct['incomingQuantity'] = $product->expected_quantity;
                $formattedProduct['outgoingQuantity'] = $product->bottles_out_quantity;
                $formattedProduct['quantity'] = $product->expected_quantity;
            } else {
                $formattedProduct['detail'] = $product->accessory_type_id;
                $formattedProduct['detail_name'] = $product->accessoryType ? $product->accessoryType->name : 'Type inconnu';
                $formattedProduct['quantity'] = $product->expected_quantity;
                $formattedProduct['incomingQuantity'] = $product->expected_quantity;
                $formattedProduct['outgoingQuantity'] = 0;
            }

            $this->products[] = $formattedProduct;
        }
    }

    /**
     * Rafraîchir les produits à partir de la base de données
     */
    public function refreshProducts()
    {
        static $refreshing = false;
        if ($refreshing) {
            return;
        }

        $refreshing = true;

        try {
            $this->supplierDelivery = $this->supplierDelivery->fresh(['productTypes.bottleType', 'productTypes.accessoryType']);
            $this->formatProductsForView();
        } finally {
            $refreshing = false;
        }
    }

    public function editProduct(int $id)
    {
        $productIndex = $this->findProductIndex($id);
        if ($productIndex !== false) {
            $product = $this->products[$productIndex];

            if ($product['type'] === ProductType::BOTTLE()->value) {
                $this->dispatch('set-gas-bottle-form', $id, $product);
            } else {
                $this->dispatch('set-accessory-form', $id, $product);
            }
        }
    }

    public function confirmDeleteProduct($id)
    {
        $this->productToDelete = $id;
        $this->dispatch('show-delete-modal');
    }

    public function deleteProduct()
    {
        if ($this->productToDelete) {
            $this->removeProduct($this->productToDelete);
            $this->productToDelete = null;
            $this->dispatch('hide-delete-modal');
        }
    }

    public function removeProduct($productId)
    {
        try {
            $deleted = SupplierDeliveryProductType::find($productId)?->delete();

            if ($deleted) {
                $this->dispatch('product-registered');
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de la suppression du produit : {$e->getMessage()}");
            session()->flash('error', 'Erreur lors de la suppression du produit : '.$e->getMessage());
        }
    }

    public function scanBottles($id)
    {
        $productIndex = $this->findProductIndex($id);
        if ($productIndex !== false) {
            $product = $this->products[$productIndex];
            $bottleType = $product['detail'];

            return redirect()->route('supplies.scan-bottles', [
                'supply_id' => $this->supplierDelivery->id,
                'type_id' => $bottleType,
            ]);
        }
    }

    private function findProductIndex($id)
    {
        foreach ($this->products as $index => $product) {
            if ($product['id'] == $id) {
                return $index;
            }
        }

        return false;
    }

    public function render()
    {
        return view('livewire.supply.products-list');
    }
}
