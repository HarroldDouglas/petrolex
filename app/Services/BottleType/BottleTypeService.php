<?php

namespace App\Services\BottleType;

use App\DTOs\BottleType\CreateBottleTypeDTO;
use App\DTOs\BottleType\UpdateBottleTypeDTO;
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
            $bottleType->productCategoryCityPrices()->createMany(
                array_map(fn ($dto) => $dto->toArray(), $data->bottleTypeCityPrices)
            );
        }

        return $bottleType;
    }

    public function update(int $bottleTypeId, UpdateBottleTypeDTO $data): BottleType
    {
        /** @var BottleType $bottleType */
        $bottleType = $this->bottleRepository->find($bottleTypeId);
        $this->bottleRepository->update($bottleType,
            $data->toArray());

        $bottleType->cityPrices()?->delete();

        if (! empty($data->bottleTypeCityPrices)) {
            $bottleType->productCategoryCityPrices()->delete();
            $bottleType->productCategoryCityPrices()->createMany(
                array_map(fn ($dto) => $dto->toArray(), $data->bottleTypeCityPrices)
            );
        }

        return $bottleType;
    }
}
