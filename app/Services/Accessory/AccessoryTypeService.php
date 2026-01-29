<?php

namespace App\Services\Accessory;

use App\DTOs\AccessoryStatsDTO;
use App\DTOs\ProductCategory\ProductCategoryDTO;
use App\Enums\ProductType;
use App\Models\AccessoryType;
use App\Repositories\Contracts\AccessoryRepositoryInterface;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use App\Services\BaseServiceWithMedia;
use App\Services\Shared\Media\MediaServiceInterface;
use Illuminate\Database\Eloquent\Model;

class AccessoryTypeService extends BaseServiceWithMedia
{
    public function __construct(
        private readonly AccessoryRepositoryInterface $accessoryRepository,
        protected MediaServiceInterface $mediaService,
        private ProductCategoryRepositoryInterface $productCategoryRepository
    ) {
        parent::__construct($accessoryRepository, $mediaService);
    }

    /**
     * Create an accessory type with its product category
     */
    public function createWithMedia(array $data): Model
    {
        return $this->executeInTransaction(function () use ($data) {
            /** @var AccessoryType $accessoryType */
            $accessoryType = parent::createWithMedia($data);

            // Créer automatiquement la ProductCategory pour cet accessoire
            $productCategoryDto = new ProductCategoryDTO(
                product_type: ProductType::ACCESSORY(),
                product_type_id: $accessoryType->id,
            );

            $this->productCategoryRepository->create($productCategoryDto->toArray());

            return $accessoryType;
        });
    }

    /**
     * Get product statistics
     */
    public function getAccessoryStats(): AccessoryStatsDTO
    {
        $activeProducts = $this->accessoryRepository->getActiveProducts();
        $inactiveProducts = $this->accessoryRepository->getInactiveProducts();

        $activeCount = $activeProducts->count();
        $inactiveCount = $inactiveProducts->count();
        $totalCount = $activeCount + $inactiveCount;

        return new AccessoryStatsDTO(
            activeCount: $activeCount,
            inactiveCount: $inactiveCount,
            totalCount: $totalCount
        );
    }

    protected function getModel(): string
    {
        return AccessoryType::class;
    }

    protected function getMediaFields(): array
    {
        return ['images'];
    }
}
