<?php

namespace App\Services\Accessory;

use App\DTOs\AccessoryStatsDTO;
use App\Models\AccessoryType;
use App\Repositories\Contracts\AccessoryRepositoryInterface;
use App\Services\BaseServiceWithMedia;
use App\Services\Shared\Media\MediaServiceInterface;

class AccessoryTypeService extends BaseServiceWithMedia
{
    public function __construct(
        private readonly AccessoryRepositoryInterface $accessoryRepository,
        protected MediaServiceInterface $mediaService,
    ) {
        parent::__construct($accessoryRepository, $mediaService);
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
