<?php

namespace App\DTOs\DistributionCenter;

use App\DTOs\BaseDTO;

class UpdateDistributionCenterDTO extends BaseDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $country = null,
        public ?string $city = null,
        public ?string $neighborhood = null,
        public ?string $address = null,
        public ?string $description = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?bool $is_active = null,
        public ?int $storage_capacity = null,
    ) {}
}
