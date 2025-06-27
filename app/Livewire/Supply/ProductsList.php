<?php

namespace App\Livewire\Supply;

use App\Enums\ProductType;
use App\Models\SupplierDelivery;
use App\Models\SupplierDeliveryProductType;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

// TODO: Move database request to repository and call service instead of repository
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
        $this->loadProducts();
    }

    /**
     * Load and format products for display
     */
    public function loadProducts(): void
    {
        $this->products = collect();

        if (! $this->supplierDelivery) {
            return;
        }

        $this->products = $this->supplierDelivery->productTypes
            ->map(fn ($product) => $this->formatProduct($product))
            ->all();

        Log::info('Loaded products:', $this->products);
    }

    /**
     * Format a product based on its type
     */
    private function formatProduct(SupplierDeliveryProductType $product): array
    {
        $baseProduct = [
            'id' => $product->id,
            'product_type' => $product->productCategory->product_type->value,
            'expected_quantity' => $product->expected_quantity,
        ];

        return match ($product->productCategory->product_type->value) {
            ProductType::BOTTLE()->value => $this->formatBottleProduct($product, $baseProduct),
            ProductType::ACCESSORY()->value => $this->formatAccessoryProduct($product, $baseProduct),
            default => $baseProduct
        };
    }

    /**
     * Format a bottle product
     */
    private function formatBottleProduct(SupplierDeliveryProductType $product, array $baseProduct): array
    {
        return array_merge($baseProduct, [
            'product_category_id' => $product->product_category_id,
            'bottles_out_quantity' => $product->bottles_out_quantity,
            'name' => $product->productCategory->name ?? 'Unknown bottle type',
        ]);
    }

    /**
     * Format an accessory product
     */
    private function formatAccessoryProduct(SupplierDeliveryProductType $product, array $baseProduct): array
    {
        return array_merge($baseProduct, [
            'product_category_id' => $product->product_category_id,
            'name' => $product->productCategory->name,
        ]);
    }

    /**
     * Refresh the product list
     */
    public function refreshProducts(): void
    {
        // Avoid recursive calls
        if ($this->isRefreshing()) {
            return;
        }

        $this->startRefresh();

        try {
            $this->supplierDelivery = $this->supplierDelivery
                ->fresh(['productTypes.productCategory.productType']);
            $this->loadProducts();
            $this->dispatch('products-updated', $this->products);
        } finally {
            $this->endRefresh();
        }
    }

    // Locking system to avoid multiple refreshes
    private static $refreshing = false;

    private function isRefreshing(): bool
    {
        return self::$refreshing;
    }

    private function startRefresh(): void
    {
        self::$refreshing = true;
    }

    private function endRefresh(): void
    {
        self::$refreshing = false;
    }

    /**
     * Edit a product
     */
    public function editProduct(int $productId): void
    {
        $product = $this->findProduct($productId);

        if (! $product) {
            session()->flash('error', 'Product not found');

            return;
        }

        $eventName = match ($product['product_type']) {
            ProductType::BOTTLE()->value => 'set-gas-bottle-form',
            ProductType::ACCESSORY()->value => 'set-accessory-form',
            default => null
        };

        if ($eventName) {
            $this->dispatch($eventName, $productId, $product);
        }
    }

    /**
     * Confirm product deletion
     */
    public function confirmDeleteProduct(int $productId): void
    {
        $this->productToDelete = $productId;
        $this->dispatch('show-delete-modal');
    }

    /**
     * Delete the confirmed product
     */
    public function deleteProduct(): void
    {
        if (! $this->productToDelete) {
            return;
        }

        try {
            $deleted = SupplierDeliveryProductType::find($this->productToDelete)?->delete();

            if ($deleted) {
                session()->flash('success', 'Product successfully deleted');
                $this->refreshProducts();
            } else {
                session()->flash('error', 'Unable to delete product');
            }
        } catch (\Exception $e) {
            Log::error("Error deleting product ID {$this->productToDelete}: {$e->getMessage()}");
            session()->flash('error', 'Error during deletion');
        } finally {
            $this->productToDelete = null;
            $this->dispatch('hide-delete-modal');
        }
    }

    /**
     * Scan bottles for a product
     */
    public function scanBottles(int $productId)
    {
        $product = $this->findProduct($productId);

        if (! $product || $product['product_type'] !== ProductType::BOTTLE()->value) {
            session()->flash('error', 'Bottle product not found');

            return;
        }

        return redirect()->route('supplies.scan-bottles', [
            'supply_id' => $this->supplierDelivery->id,
            'type_id' => $product['product_category_id'],
        ]);
    }

    /**
     * Find a product by its ID
     */
    private function findProduct(int $productId): ?array
    {
        return collect($this->products)->firstWhere('id', $productId);
    }

    /**
     * Get product display name
     */
    public function getProductDisplayName(array $product): string
    {
        return $product['name'];
    }

    /**
     * Get product type display value
     */
    public function getProductTypeDisplay(array $product): string
    {
        return match ($product['product_type'] ?? null) {
            ProductType::BOTTLE()->value => 'bottle',
            ProductType::ACCESSORY()->value => 'accessory',
            default => 'unknown'
        };
    }

    public function render()
    {
        return view('livewire.supply.products-list');
    }
}
