<?php

declare(strict_types=1);

namespace App\DTOs\Order;

use App\DTOs\BaseDTO;

final class ScanEmptyBottleDTO extends BaseDTO
{
    public function __construct(
        public readonly string $barcode,
        public readonly int $orderItemId,
    ) {}
}
