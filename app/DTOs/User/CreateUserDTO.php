<?php

namespace App\DTOs\User;

use App\DTOs\BaseDTO;
use App\Enums\UserRole;
use Illuminate\Http\UploadedFile;
use Spatie\Enum\Laravel\Casts\EnumCast;
use Spatie\LaravelData\Attributes\WithCast;

class CreateUserDTO extends BaseDTO
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $email,
        public string $phone_number,
        public string $password,
        public ?string $address,
        public bool $is_active,
        #[WithCast(EnumCast::class)]
        public UserRole $role,
        /** @var array<int> $distribution_center_ids */
        public ?array $distribution_center_ids = [],
        public readonly ?UploadedFile $image = null,
    ) {}

    public function toArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'password' => $this->password,
            'address' => $this->address,
            'is_active' => $this->is_active,
            'role' => $this->role->value,
            'distribution_center_ids' => $this->distribution_center_ids,
            'image' => $this->image ? $this->image->getClientOriginalName() : null,
        ];
    }

    // TODO remove this function
    public function toUserArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'password' => $this->password,
            'address' => $this->address,
            'is_active' => $this->is_active,
        ];
    }
}
