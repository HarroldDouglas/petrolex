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
        /** @var BottleType $bottleType */
        $bottleType = $this->bottleRepository->create($data->toArray());

        if ($data->bottleTypeCityPrices) {
            $bottleType->cityPrices()->createMany($data->bottleTypeCityPrices);
        }

        return $bottleType;
    }
}
