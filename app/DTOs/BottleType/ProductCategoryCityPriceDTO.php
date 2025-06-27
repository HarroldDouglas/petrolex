<?php

namespace App\DTOs\BottleType;

use App\DTOs\BaseDTO;

class ProductCategoryCityPriceDTO extends BaseDTO
{
    /**
     * @param  int|null  $bottle_type_id  ID du type de bouteille (pour rétrocompatibilité)
     * @param  int|null  $product_category_id  ID de la catégorie de produit
     * @param  string  $city  Ville
     * @param  float  $content_price  Prix du contenu
     * @param  float  $content_with_bottle_price  Prix du contenu avec bouteille
     */
    public function __construct(
        public readonly ?int $bottle_type_id, // TODO: this should be removed as product_category_id is sufficient
        public readonly ?int $product_category_id,
        public readonly string $city,
        public readonly float $content_price,
        public readonly float $content_with_bottle_price,
    ) {}
}
