<?php

namespace App\DTOs\User;

use App\DTOs\BaseDTO;

class UpdateUserDTO extends BaseDTO
{
    public function __construct(
        public ?string $first_name = null,
        public ?string $last_name = null,
        public ?string $email = null,
        public ?string $phone_number = null,
        public ?string $address = null,
        public ?bool $is_active = null,
    ) {}
}
