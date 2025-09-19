<?php

namespace App\DTOs\Customer;

use App\DTOs\BaseDTO;

class CustomerDeliveryAddressDTO extends BaseDTO
{
    public function __construct(
        public string $label,
        public string $address,
        public int $neighborhood_id,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $phone,
        public ?string $phone_country_code,
        public ?string $contact_firstname,
        public ?string $contact_lastname,
        public ?string $email,
        public ?string $address_precision,
        public bool $is_default = false,
    ) {}
}
