<?php

namespace App\DTOs\Order;

use App\Enums\BottleOrderType;
use Spatie\LaravelData\Data;

class OrderItemDTO extends Data
{
    public function __construct(
        public int $product_category_id,
        public ?string $option,
        public int $quantity,
        public ?float $unit_price,
        public ?float $total_price,
        public ?BottleOrderType $bottle_type
    ) {}
}
