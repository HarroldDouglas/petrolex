<?php

namespace App\DTOs\Auth;

use App\Models\User;

class TokenDTO
{
    private const DEFAULT_TOKEN_TYPE = 'Bearer';

    public function __construct(
        public readonly string $accessToken,
        public readonly string $tokenType,
        public readonly User $user,
    ) {}
}
