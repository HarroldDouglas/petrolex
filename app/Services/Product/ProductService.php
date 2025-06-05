<?php

namespace App\Services\Product;

use App\DTOs\ProductStatsDTO;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Get product statistics
     */
    public function getProductStats(): ProductStatsDTO
    {
        $activeProducts = $this->productRepository->getActiveProducts();
        $inactiveProducts = $this->productRepository->getInactiveProducts();

        $activeCount = $activeProducts->count();
        $inactiveCount = $inactiveProducts->count();
        $totalCount = $activeCount + $inactiveCount;

        return new ProductStatsDTO(
            activeCount: $activeCount,
            inactiveCount: $inactiveCount,
            totalCount: $totalCount
        );
    }
}
