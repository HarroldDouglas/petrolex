<?php

namespace App\Services\BottleType;

use App\DTOs\BottleType\CreateBottleTypeDTO;
use App\Models\BottleType;
use App\Repositories\Contracts\BottleTypeRepositoryInterface;
use Illuminate\Support\Facades\Log;

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
            Log::info('Creating city prices for bottle type', [
                'bottle_type_id' => $bottleType->id,
                'city_prices' => $data->bottleTypeCityPrices,
            ]);
            $bottleType->cityPrices()->createMany(
                array_map(fn ($dto) => $dto->toArray(), $data->bottleTypeCityPrices)
            );
        }

        return $bottleType;
    }
}
