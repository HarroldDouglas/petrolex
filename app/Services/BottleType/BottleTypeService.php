<?php

namespace App\Services\BottleType;

use App\DTOs\BottleType\CreateBottleTypeDTO;
use App\Models\BottleType;
use App\Repositories\Contracts\BottleTypeRepositoryInterface;

class BottleTypeService
{
    public function __construct(
        private BottleTypeRepositoryInterface $bottleRepository
    ) {}

    public function create(CreateBottleTypeDTO $data): BottleType
    {
        return $this->bottleRepository->create($data->toArray());
    }
}
