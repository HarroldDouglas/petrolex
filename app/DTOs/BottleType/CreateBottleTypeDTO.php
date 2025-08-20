<?php

namespace App\DTOs\BottleType;

use App\DTOs\BaseDTO;
use App\DTOs\ProductCategory\ProductCategoryCityPriceDTO;
use Illuminate\Http\UploadedFile;

class CreateBottleTypeDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        /** @var ProductCategoryCityPriceDTO[]|null */
        public readonly ?array $bottleTypeCityPrices,
        public readonly string $capacity,
        public readonly float $content_price,
        public readonly float $bottle_with_content_price,
        public readonly bool $is_active,
        public readonly ?string $description,
        public readonly ?float $weight,
        /** @var UploadedFile[]|null */
        public readonly ?array $images = null,
    ) {}
}
