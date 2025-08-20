<?php

namespace App\DTOs\ProductCategory;

use App\DTOs\BaseDTO;
use App\Enums\ProductType;

class ProductCategoryDTO extends BaseDTO
{
    public function __construct(
        public readonly ProductType $product_type,
        public readonly ?int $product_type_id = null,
    ) {}
}
