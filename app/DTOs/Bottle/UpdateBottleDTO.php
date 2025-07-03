<?php

namespace App\DTOs\Bottle;

use App\DTOs\BaseDTO;
use App\Enums\BottleStatus;

class UpdateBottleDTO extends BaseDTO
{
    public function __construct(
        public readonly ?int $distribution_center_id = null,
        public readonly ?string $barcode = null,
        public readonly ?int $product_id = null,
        public readonly ?BottleStatus $status = null,
        public readonly ?bool $is_filled = null,
    ) {}
}
