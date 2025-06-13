<?php

namespace App\DTOs\BottleType;

use App\DTOs\BaseDTO;

class BottleTypeCityPriceDTO extends BaseDTO
{
    public function __construct(
        public readonly ?int $bottle_type_id,
        public readonly string $city,
        public readonly float $content_price,
        public readonly float $content_with_bottle_price,
    ) {}
}
