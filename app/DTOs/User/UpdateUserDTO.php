<?php

namespace App\DTOs\User;

use App\DTOs\BaseDTO;
use App\Enums\UserRole;
use App\Transformers\EnumValueTransformer;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\WithTransformer;

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
        #[WithTransformer(EnumValueTransformer::class)]
        public ?UserRole $role,
        /** @var array<int> $distribution_center_ids */
        public ?array $distribution_center_ids = [],
        public ?UploadedFile $image = null,
        public ?string $address = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            first_name: $data['first_name'] ?? null,
            last_name: $data['last_name'] ?? null,
            email: $data['email'] ?? null,
            phone_number: $data['phone_number'] ?? null,
            password: $data['password'] ?? null,
            is_active: $data['is_active'] ?? null,
            role: UserRole::from($data['role']) ?? null,
            distribution_center_ids: $data['distribution_center_ids'] ?? [],
            image: $data['image'] ?? null,
            address: $data['address'] ?? null,
        );
    }
}
