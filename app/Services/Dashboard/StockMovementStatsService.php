<?php

namespace App\Services\Dashboard;

use App\DTOs\Dashboard\StockMovementStatsDTO;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use Carbon\Carbon;

class StockMovementStatsService
{
    public function __construct(private StockMovementRepositoryInterface $stockMovementRepository) {}

    /**
     * Get stock statistics for the specified period and distribution centers
     * 
     * @param string|null $startDate Start date of the period in string format
     * @param string|null $endDate End date of the period in string format
     * @param array|null $distributionCenterIds Array of distribution center IDs to filter by
     * @return StockMovementStatsDTO DTO containing the stock movement statistics
     */
    public function getStockStats(
        ?string $startDate = null,
        ?string $endDate = null,
        ?array $distributionCenterIds = null
    ): StockMovementStatsDTO {
        $startDateCarbon = $startDate ? Carbon::parse($startDate) : Carbon::now()->subDays(7);
        $endDateCarbon = $endDate ? Carbon::parse($endDate) : Carbon::now();
        $effectiveDistributionCenterIds = (is_array($distributionCenterIds) && count($distributionCenterIds) === 0)
            ? null
            : $distributionCenterIds;

        $totalSoldBottles = $this->stockMovementRepository->calculateTotalSoldBottles($startDateCarbon, $endDateCarbon, $effectiveDistributionCenterIds);
        $totalExchanges = $this->stockMovementRepository->calculateTotalExchanges($startDateCarbon, $endDateCarbon, $effectiveDistributionCenterIds);
        $fullBottles = $this->stockMovementRepository->calculateFullBottles(null, null, $effectiveDistributionCenterIds);
        $emptyBottles = $this->stockMovementRepository->calculateEmptyBottles(null, null, $effectiveDistributionCenterIds);
        $totalSupplied = $this->stockMovementRepository->calculateTotalSupplied($startDateCarbon, $endDateCarbon, $effectiveDistributionCenterIds);

        return new StockMovementStatsDTO(
            totalSoldBottles: $totalSoldBottles,
            totalExchanges: $totalExchanges,
            fullBottles: $fullBottles,
            emptyBottles: $emptyBottles,
            totalSupplied: $totalSupplied
        );
    }
}
