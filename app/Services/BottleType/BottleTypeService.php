<?php

namespace App\Services\BottleType;

use App\DTOs\BottleType\CreateBottleTypeDTO;
use App\DTOs\BottleType\UpdateBottleTypeDTO;
use App\Enums\ProductType;
use App\Models\BottleType;
use App\Models\ProductCategory;
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

        // Créer une catégorie de produit associée au type de bouteille
        $productCategory = ProductCategory::create([
            'name' => $data->name,
            'product_type' => ProductType::BOTTLE(),
            'product_type_id' => $bottleType->id,
            'description' => $data->description,
            'is_active' => $data->is_active,
        ]);

        if ($data->bottleTypeCityPrices && $productCategory) {
            // Mettre à jour les DTOs avec le product_category_id
            $cityPrices = [];
            foreach ($data->bottleTypeCityPrices as $cityPriceDTO) {
                $cityPriceData = $cityPriceDTO->toArray();
                $cityPriceData['product_category_id'] = $productCategory->id;
                $cityPrices[] = $cityPriceData;
            }

            // Insérer les prix par ville avec le product_category_id
            ProductCategoryCityPrice::insert($cityPrices);
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
        $productCategory = ProductCategory::where('product_type', ProductType::BOTTLE())
            ->where('product_type_id', $bottleType->id)
            ->first();

        if ($productCategory) {
            ProductCategoryCityPrice::where('product_category_id', $productCategory->id)->delete();

            if (! empty($data->bottleTypeCityPrices)) {
                // Mettre à jour les DTOs avec le product_category_id
                $cityPrices = [];
                foreach ($data->bottleTypeCityPrices as $cityPriceDTO) {
                    $cityPriceData = $cityPriceDTO->toArray();
                    $cityPriceData['product_category_id'] = $productCategory->id;
                    $cityPrices[] = $cityPriceData;
                }

                // Insérer les prix par ville avec le product_category_id
                ProductCategoryCityPrice::insert($cityPrices);
            }
        }

        return $bottleType;
    }
}
