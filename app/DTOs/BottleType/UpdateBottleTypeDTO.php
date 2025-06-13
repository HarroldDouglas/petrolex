<?php

namespace App\DTOs\BottleType;

use App\DTOs\BaseDTO;

class UpdateBottleTypeDTO extends BaseDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        /** @var BottleTypeCityPriceDTO[]|null */
        public readonly ?array $bottleTypeCityPrices,
        public readonly string $capacity,
        public readonly float $content_price,
        public readonly float $bottle_with_content_price,
        public readonly bool $is_active,
        public readonly ?string $description,
        public readonly ?float $weight,
    ) {}
}
