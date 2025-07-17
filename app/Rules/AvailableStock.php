<?php

namespace App\Rules;

use App\Models\ProductCategory;
use App\Services\DistributionCenter\DistributionCenterService;
use App\Services\ProductCategoryService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AvailableStock implements ValidationRule
{
    public function __construct(
        private ProductCategoryService $productCategoryService,
        private DistributionCenterService $distributionCenterService,
        private int $distributionCenterId
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Extract index from attribute, eg., items.0.quantity
        preg_match('/items\.(\d+)\.quantity/', $attribute, $matches);

        if (! isset($matches[1])) {
            return;
        }

        $index = $matches[1];
        $items = request()->input('items');
        $item = $items[$index];

        /** @var ProductCategory $productCategory */
        $productCategory = $this->productCategoryService->find($item['product_category_id']);

        $distributionCenter = $this->distributionCenterService->find($this->distributionCenterId);

        if (! $productCategory || ! $distributionCenter) {
            $fail('Categorie de produits ou centre de distribution invalide.');

            return;
        }

        $availableQuantity = $this->productCategoryService
            ->getProductQuantity($productCategory, $distributionCenter->id);

        if ($availableQuantity < $item['quantity']) {
            $fail("Il n'y a que {$availableQuantity} unités disponibles pour {$productCategory->name}.");
        }
    }
}
