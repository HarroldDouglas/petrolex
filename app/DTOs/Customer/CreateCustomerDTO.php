<?php

namespace App\DTOs\Customer;

use App\DTOs\BaseDTO;

class CreateCustomerDTO extends BaseDTO
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $email,
        public string $phone_number,
        public string $password,
        public int $country_id,
        public ?string $language = null,
        public ?string $address = null,
        public ?float $current_balance = 0.0,
    ) {}
}
