<?php

namespace App\DTOs\Order;

use Spatie\LaravelData\Data;

class OrderItemDTO extends Data
{
    public function __construct(
        public int $product_category_id,
        public ?string $option,
        public int $quantity,
    ) {}
}
