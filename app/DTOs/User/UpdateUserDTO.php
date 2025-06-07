<?php

namespace App\DTOs\User;

use Spatie\LaravelData\Data;

class UpdateUserDTO extends Data
{
    public function __construct(
        public ?string $first_name = null,
        public ?string $last_name = null,
        public ?string $email = null,
        public ?string $phone_number = null,
        public ?string $address = null,
        public ?bool $is_active = null,
    ) {}

    public function toArrayFiltered(): array
    {
        return array_filter($this->toArray(), function ($value, $key) {
            return $value !== null && $value !== '';
        }, ARRAY_FILTER_USE_BOTH);
    }
}
