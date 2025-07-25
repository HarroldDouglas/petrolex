<?php

namespace App\DTOs\Order;

use App\DTOs\BaseDTO;

class CancelOrderDTO extends BaseDTO
{
    public function __construct(
        public readonly string $cancelled_reason,
        public readonly ?int $cancelled_by,
    ) {}
}
