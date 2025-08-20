<?php

namespace App\DTOs\Order;

use App\Casts\SpatieEnumCast;
use App\Enums\BottleOrderType;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class OrderItemDTO extends Data
{
    public function __construct(
        public int $product_category_id,
        public int $quantity,
        public ?float $unit_price,
        public ?float $total_price,
        #[WithCast(SpatieEnumCast::class, BottleOrderType::class)]
        public ?BottleOrderType $option
    ) {}
}
