<?php

declare(strict_types=1);

namespace App\DTOs\Order;

use App\DTOs\BaseDTO;

final class AddCustomerCommentToOrderDTO extends BaseDTO
{
    public function __construct(
        public readonly string $comment,
        public readonly float $rating,
    ) {}
}
