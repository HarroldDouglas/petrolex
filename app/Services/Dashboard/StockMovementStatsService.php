<?php

namespace App\Services\Dashboard;

use App\Repositories\Contracts\StockMovementRepositoryInterface;
use App\DTOs\Dashboard\StockMovementStatsDTO;
use Carbon\Carbon;

class StockMovementStatsService
{
    public function __construct(private StockMovementRepositoryInterface $stockMovementRepository)
    {
    }

    /**
     * 
     *
     * @param string|null
     * @param string|null 
     * @param array|null 
     * @return StockMovementStatsDTO
     */
    public function getStockStats(
        ?string $startDate = null,
        ?string $endDate = null,
        ?array $distributionCenterIds = null 
    ): StockMovementStatsDTO {
        $startDateCarbon = $startDate ? Carbon::parse($startDate) : null;
        $endDateCarbon = $endDate ? Carbon::parse($endDate) : null;
        $effectiveDistributionCenterIds = (is_array($distributionCenterIds) && count($distributionCenterIds) === 0)
                                        ? null
                                        : $distributionCenterIds;


        $totalExits = $this->stockMovementRepository->calculateTotalExits($startDateCarbon, $endDateCarbon, $effectiveDistributionCenterIds);
        $totalExchanges = $this->stockMovementRepository->calculateTotalExchanges($startDateCarbon, $endDateCarbon, $effectiveDistributionCenterIds);
        $fullBottles = $this->stockMovementRepository->calculateFullBottles($startDateCarbon, $endDateCarbon, $effectiveDistributionCenterIds);
        $emptyBottles = $this->stockMovementRepository->calculateEmptyBottles($startDateCarbon, $endDateCarbon, $effectiveDistributionCenterIds);

        return new StockMovementStatsDTO(
            totalExits: $totalExits,
            totalExchanges: $totalExchanges,
            fullBottles: $fullBottles,
            emptyBottles: $emptyBottles
        );
    }
}