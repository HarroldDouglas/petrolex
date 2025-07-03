<?php

namespace App\Services\BottleType;

use App\DTOs\BottleType\CreateBottleTypeDTO;
use App\DTOs\BottleType\ProductCategoryCityPriceDTO;
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

            $productCategory = $this->productCategoryRepository->create($productCategoryDto->toArray());

            if (! empty($createBottleTypeDTO->bottleTypeCityPrices)) {
                $bottleTypeCityPricesData = array_map(
                    fn (ProductCategoryCityPriceDTO $cityPriceDTO) => $cityPriceDTO->toArray(),
                    $createBottleTypeDTO->bottleTypeCityPrices
                );
                $productCategory->cityPrices()->createMany($bottleTypeCityPricesData);
            }

            return $bottleType;
        });
    }

    public function update(Model|BottleType $model, array $data, ?array $imagesIdsToDelete = null): BottleType
    {
        /** @var BottleType $bottleType */
        $bottleType = parent::updateWithMedia($model, $data, $imagesIdsToDelete);

        $productCategory = $bottleType->productCategory;

        if ($productCategory) {
            $productCategory->cityPrices()->delete();

            if (! empty($data['bottleTypeCityPrices'])) {
                $productCategory->cityPrices()->createMany($data['bottleTypeCityPrices']);
            }
        }

        return $bottleType;
    }

    public function getWithMedia(int $bottleTypeId): BottleType
    {
        $bottleType = $this->bottleTypeRepository->find($bottleTypeId);
        $bottleType->load('media');

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
