<?php

namespace App\DTOs\Customer;

use App\DTOs\BaseDTO;

class CustomerDeliveryAddressDTO extends BaseDTO
{
    public function __construct(
        public string $label,
        public string $address,
        public int $neighborhood_id,
    ) {}
}
