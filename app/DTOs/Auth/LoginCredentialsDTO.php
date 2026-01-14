<?php

namespace App\DTOs\Auth;

class LoginCredentialsDTO
{
    public function __construct(
        public readonly string $login,
        public readonly string $password,
        public readonly ?string $countryCode = null,
        public readonly ?string $appType = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            login: $data['login'] ?? '',
            password: $data['password'] ?? '',
            countryCode: $data['country_code'] ?? null,
            appType: $data['app_type'] ?? null
        );
    }
}
