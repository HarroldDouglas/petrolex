<?php

namespace App\Services\BottleType;

use App\DTOs\BottleType\CreateBottleTypeDTO;
use App\DTOs\BottleType\UpdateBottleTypeDTO;
use App\DTOs\ProductCategory\ProductCategoryCityPriceDTO;
use App\DTOs\ProductCategory\ProductCategoryDTO;
use App\Enums\ProductType;
use App\Models\BottleType;
use App\Repositories\Contracts\BottleTypeRepositoryInterface;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use App\Services\BaseServiceWithMedia;
use App\Services\Shared\Media\MediaServiceInterface;
use Illuminate\Database\Eloquent\Model;

class BottleTypeService extends BaseServiceWithMedia
{
    public function __construct(
        protected BottleTypeRepositoryInterface $bottleTypeRepository,
        protected MediaServiceInterface $mediaService,
        private ProductCategoryRepositoryInterface $productCategoryRepository
    ) {
        parent::__construct($bottleTypeRepository, $mediaService);
    }

    public function create(array $data): BottleType
    {
        return $this->executeInTransaction(function () use ($data) {
            $createBottleTypeDTO = CreateBottleTypeDTO::from($data);

            /** @var BottleType $bottleType */
            $bottleType = parent::createWithMedia($createBottleTypeDTO->toArray());
            $productCategoryDto = new ProductCategoryDTO(
                product_type: ProductType::BOTTLE(),
                product_type_id: $bottleType->id,
            );

            /** @var \App\Models\ProductCategory $productCategory */
            $productCategory = $this->productCategoryRepository->create($productCategoryDto->toArray());

            if (! empty($createBottleTypeDTO->bottleTypeCityPrices)) {
                $bottleTypeCityPricesData = array_map(
                    fn (ProductCategoryCityPriceDTO $cityPriceDTO): array => [
                        'city_id' => $cityPriceDTO->city_id,
                        'content_price' => $cityPriceDTO->content_price,
                        'content_with_bottle_price' => $cityPriceDTO->content_with_bottle_price,
                    ],
                    $createBottleTypeDTO->bottleTypeCityPrices
                );
                $productCategory->cityPrices()->createMany($bottleTypeCityPricesData);
            }

            return $bottleType;
        });
    }

    public function update(Model|BottleType $model, array $data, ?array $imagesIdsToDelete = null): BottleType
    {
        return $this->executeInTransaction(function () use ($model, $data, $imagesIdsToDelete) {
            $updateBottleTypeDTO = UpdateBottleTypeDTO::from($data);

            /** @var BottleType $bottleType */
            $bottleType = parent::updateWithMedia($model, $updateBottleTypeDTO->toArray(), $imagesIdsToDelete);

            $productCategory = $bottleType->productCategory;

            if ($productCategory) {
                $productCategory->cityPrices()->delete();

                if (! empty($updateBottleTypeDTO->bottleTypeCityPrices)) {
                    $bottleTypeCityPricesData = array_map(
                        fn (ProductCategoryCityPriceDTO $cityPriceDTO): array => [
                            'city_id' => $cityPriceDTO->city_id,
                            'content_price' => $cityPriceDTO->content_price,
                            'content_with_bottle_price' => $cityPriceDTO->content_with_bottle_price,
                        ],
                        $updateBottleTypeDTO->bottleTypeCityPrices
                    );
                    $productCategory->cityPrices()->createMany($bottleTypeCityPricesData);
                }
            }

            return $bottleType;
        });
    }

    public function getWithMedia(int $bottleTypeId): ?BottleType
    {
        /** @var \App\Models\BottleType|null $bottleType */
        $bottleType = $this->bottleTypeRepository->find($bottleTypeId);
        if ($bottleType) {
            $bottleType->load('media');
        }

        return $bottleType;
    }

    protected function getMediaFields(): array
    {
        return ['images'];
    }

    protected function getModel(): string
    {
        return BottleType::class;
    }
}
