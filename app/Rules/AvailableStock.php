<?php

namespace App\Rules;

use App\Services\DistributionCenter\DistributionCenterService;
use App\Services\ProductCategoryService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AvailableStock implements ValidationRule
{
    public function __construct(
        private readonly ProductCategoryService $productCategoryService,
        private readonly DistributionCenterService $distributionCenterService,
        private readonly int $distributionCenterId,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($this->distributionCenterId)) {
            $fail('Le centre de distribution doit être sélectionné pour vérifier le stock disponible.');

            return;
        }

        // Extract index from attribute, eg., items.0.quantity
        preg_match('/items\.(\d+)\.quantity/', $attribute, $matches);

        if (! isset($matches[1])) {
            return;
        }

        $index = $matches[1];
        $items = request()->input('items');
        $item = $items[$index];

        try {
            $availableQuantity = $this->productCategoryService
                ->getProductQuantity($item['product_category_id'], $this->distributionCenterId);

            if ($availableQuantity < $item['quantity']) {
                $fail("La quantité demandée pour le produit avec l'ID {$item['product_category_id']} n'est pas disponible. Stock actuel : {$availableQuantity}.");
            }

        } catch (ModelNotFoundException) {
            $fail("Le produit avec l'ID {$item['product_category_id']} n'existe pas.");
        }
    }
}
