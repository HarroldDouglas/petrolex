<?php

namespace App\Services\BottleType;

use App\DTOs\BottleType\CreateBottleTypeDTO;
use App\DTOs\BottleType\UpdateBottleTypeDTO;
use App\Models\BottleType;
use App\Models\ProductCategoryCityPrice;
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
            ProductCategoryCityPrice::insert(
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

        // Récupérer la catégorie de produit associée au type de bouteille
        $productCategory = \App\Models\ProductCategory::where('product_type', \App\Enums\ProductType::BOTTLE())
            ->where('product_type_id', $bottleType->id)
            ->first();

        if ($productCategory) {
            ProductCategoryCityPrice::where('product_category_id', $productCategory->id)->delete();

            if (! empty($data->bottleTypeCityPrices)) {
                ProductCategoryCityPrice::insert(
                    array_map(fn ($dto) => $dto->toArray(), $data->bottleTypeCityPrices)
                );
            }
        }

        return $bottleType;
    }
}
