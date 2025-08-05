<?php

namespace App\DTOs\Order;

use App\DTOs\BaseDTO;
use App\Enums\OrderStatus;

class UpdateOrderDTO extends BaseDTO
{
    public function __construct(
        public ?OrderStatus $status,
    ) {}
}
