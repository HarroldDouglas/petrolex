<?php

namespace App\DTOs\User;

use App\DTOs\BaseDTO;
use App\Enums\UserRole;
use Illuminate\Http\UploadedFile;

class UpdateUserDTO extends BaseDTO
{
    public function __construct(
        public ?int $id,
        public ?string $first_name,
        public ?string $last_name,
        public ?string $email,
        public ?string $phone_number,
        public ?string $password,
        public ?bool $is_active,
        public ?UserRole $role,
        /** @var array<int> $distribution_center_ids */
        public ?array $distribution_center_ids = [],
        public ?UploadedFile $image = null,
        public ?string $address = null,
    ) {}
}
