<?php

namespace App\DTOs\BottleType;

use App\DTOs\BaseDTO;

class UpdateBottleTypeDTO extends BaseDTO
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $name = null,
        /** @var ProductCategoryCityPriceDTO[]|null */
        public readonly ?array $bottleTypeCityPrices = null,
        public readonly ?string $capacity = null,
        public readonly ?float $content_price = null,
        public readonly ?float $bottle_with_content_price = null,
        public readonly ?bool $is_active = null,
        public readonly ?string $description = null,
        public readonly ?float $weight = null,
    ) {}
}
