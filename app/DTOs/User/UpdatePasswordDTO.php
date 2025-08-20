<?php

declare(strict_types=1);

namespace App\DTOs\User;

use App\DTOs\BaseDTO;

final class UpdatePasswordDTO extends BaseDTO
{
    public function __construct(
        public readonly string $old_password,
        public readonly string $new_password,
    ) {}
}
