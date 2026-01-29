<?php

namespace App\DTOs\BottleType;

use App\DTOs\BaseDTO;
use App\DTOs\ProductCategory\ProductCategoryCityPriceDTO;
use Illuminate\Http\UploadedFile;

class CreateBottleTypeDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $name_en = null,
        /** @var ProductCategoryCityPriceDTO[]|null */
        public readonly ?array $bottleTypeCityPrices = null,
        public readonly string $capacity = '',
        public readonly ?float $height = null,
        public readonly ?float $radius = null,
        public readonly float $content_price = 0,
        public readonly float $full_price = 0,
        public readonly bool $is_active = true,
        public readonly ?string $description = null,
        public readonly ?string $description_en = null,
        public readonly ?float $weight = null,
        /** @var UploadedFile[]|null */
        public readonly ?array $images = null,
    ) {}
}
