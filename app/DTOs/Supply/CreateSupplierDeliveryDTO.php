<?php

namespace App\DTOs\Supply;

use App\DTOs\BaseDTO;

class CreateSupplierDeliveryDTO extends BaseDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $supply_date,
        public readonly ?string $description,
        public readonly int $distribution_center_id,
        public readonly bool $is_active = true,
        public readonly ?int $user_id = null,
    ) {}
}
