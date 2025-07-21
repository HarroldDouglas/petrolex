<?php

namespace App\DTOs\Customer;

use App\DTOs\User\CreateUserDTO;
use App\Enums\UserRole;

class CreateCustomerDTO extends CreateUserDTO
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $email,
        public string $phone_number,
        public string $password,
        public ?string $address = null,
        public bool $is_active = true,
        public ?float $current_balance = 0.0,
    ) {
        parent::__construct(
            first_name: $first_name,
            last_name: $last_name,
            email: $email,
            phone_number: $phone_number,
            password: $password,
            address: $address,
            is_active: $is_active,
            role: UserRole::CUSTOMER(),
        );
    }
}
