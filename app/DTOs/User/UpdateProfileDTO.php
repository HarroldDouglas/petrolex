<?php

namespace App\DTOs\User;

use App\DTOs\BaseDTO;
use Illuminate\Http\UploadedFile;

class UpdateProfileDTO extends BaseDTO
{
    public function __construct(
        public ?int $id = null,
        public ?string $first_name = null,
        public ?string $last_name = null,
        public ?string $email = null,
        public ?string $phone_number = null,
        public ?string $password = null,
        public ?UploadedFile $image = null,
    ) {}
}
