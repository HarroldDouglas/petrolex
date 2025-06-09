<?php

namespace App\Services\Accessory;

use App\DTOs\AccessoryStatsDTO;
use App\Models\AccessoryType;
use App\Repositories\Contracts\AccessoryRepositoryInterface;
use App\Services\BaseService;

class AccessoryTypeService extends BaseService
{
    public function __construct(
        private readonly AccessoryRepositoryInterface $accessoryRepository
    ) {
        parent::__construct($accessoryRepository);
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
}
