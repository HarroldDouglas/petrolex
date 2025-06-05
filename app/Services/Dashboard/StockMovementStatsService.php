<?php

namespace App\Services\Dashboard;

use App\Repositories\Contracts\StockMovementRepositoryInterface;
use App\DTOs\Dashboard\StockMovementStatsDTO;
use Carbon\Carbon;

class StockMovementStatsService
{
    private StockMovementRepositoryInterface $stockMovementRepository;

    public function __construct(StockMovementRepositoryInterface $stockMovementRepository)
    {
        $this->stockMovementRepository = $stockMovementRepository;
    }

    public function getStockStats(?string $startDate = null, ?string $endDate = null, ?string $distributionCenterId = null): StockMovementStatsDTO
    {
        $startDateCarbon = $startDate ? Carbon::parse($startDate) : null;
        $endDateCarbon = $endDate ? Carbon::parse($endDate) : null;

        $distributionCenterIds = $distributionCenterId ? [$distributionCenterId] : null;

        $totalEntries = $this->stockMovementRepository->calculateTotalEntries($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $totalExits = $this->stockMovementRepository->calculateTotalExits($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $totalExchanges = $this->stockMovementRepository->calculateTotalExchanges($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $currentStock = $this->stockMovementRepository->calculateCurrentStock($distributionCenterIds);
        $totalStock = $this->stockMovementRepository->calculateTotalStock($distributionCenterIds); // Assuming total stock is the same as current stock

        return new StockMovementStatsDTO(
            totalEntries: $totalEntries,
            totalExits: $totalExits,
            totalExchanges: $totalExchanges,
            currentStock: $currentStock,
            totalStock: $totalStock
        );
    }

    public function getStockStatsByMultipleCenters(?string $startDate = null, ?string $endDate = null, array $distributionCenterIds = []): StockMovementStatsDTO
    {
        $startDateCarbon = $startDate ? Carbon::parse($startDate) : null;
        $endDateCarbon = $endDate ? Carbon::parse($endDate) : null;

        $totalEntries = $this->stockMovementRepository->calculateTotalEntries($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $totalExits = $this->stockMovementRepository->calculateTotalExits($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $totalExchanges = $this->stockMovementRepository->calculateTotalExchanges($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $currentStock = $this->stockMovementRepository->calculateCurrentStock($distributionCenterIds);
        $totalStock = $this->stockMovementRepository->calculateTotalStock($distributionCenterIds);

        return new StockMovementStatsDTO(
            totalEntries: $totalEntries,
            totalExits: $totalExits,
            totalExchanges: $totalExchanges,
            currentStock: $currentStock,
            totalStock: $totalStock
        );
    }
}
