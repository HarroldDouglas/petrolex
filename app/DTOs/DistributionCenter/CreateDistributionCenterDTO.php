<?php

namespace App\DTOs\DistributionCenter;

use Spatie\LaravelData\Data;

class CreateDistributionCenterDTO extends Data
{
    public function __construct(
        public string $name,
        public string $country,
        public string $city,
        public ?string $neighborhood,
        public string $address,
        public ?string $description,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $phone,
        public ?string $email,
        public bool $is_active = true,
        public ?int $storage_capacity = null,
    ) {}
}
