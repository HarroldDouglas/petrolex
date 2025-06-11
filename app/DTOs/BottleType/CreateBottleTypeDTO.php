<?php

namespace App\DTOs\BottleType;

use App\DTOs\BaseDTO;

class CreateBottleTypeDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public ?array $bottleTypeCityPrices,
        public string $capacity,
        public float $content_price,
        public float $bottle_with_content_price,
        public bool $is_active,
        public ?string $description,
        public ?float $weight,
    ) {}
}
