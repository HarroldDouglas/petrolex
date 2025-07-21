<?php

namespace App\DTOs\ProductCategory;

use App\DTOs\BaseDTO;

class ProductCategoryCityPriceDTO extends BaseDTO
{
    public function __construct(
        public readonly ?int $product_category_id,
        public readonly int $city_id,
        public readonly float $content_price,
        public readonly float $content_with_bottle_price,
    ) {}
}
