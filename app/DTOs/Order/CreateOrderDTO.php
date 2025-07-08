<?php

namespace App\DTOs\Order;

use App\DTOs\BaseDTO;
use App\Enums\OrderStatus;
use App\Models\OrderItem;

class CreateOrderDTO extends BaseDTO
{
    public function __construct(
        public int $customer_id,
        public string $order_date,
        public OrderStatus $status,
        /** @var OrderItem[] */
        public array $items
    ) {}
}
