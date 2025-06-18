<?php

namespace App\DTOs\Supply;

use App\DTOs\BaseDTO;

class UpdateSupplierDeliveryDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $title,
        public readonly ?string $supply_date,
        public readonly ?string $description,
        public readonly ?int $distribution_center_id,
    ) {}
}
