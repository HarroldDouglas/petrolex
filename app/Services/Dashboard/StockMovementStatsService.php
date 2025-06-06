<?php

namespace App\Services\Dashboard;

use App\Repositories\Contracts\StockMovementRepositoryInterface;
use App\DTOs\Dashboard\StockMovementStatsDTO;
use Carbon\Carbon;

class StockMovementStatsService
{
    public function __construct(private StockMovementRepositoryInterface $stockMovementRepository)
    {
        // Plus besoin de déclarer la propriété ici, ni de l'affecter explicitement
    }

    public function getStockStats(?string $startDate = null, ?string $endDate = null, ?string $distributionCenterId = null): StockMovementStatsDTO
    {
        $startDateCarbon = $startDate ? Carbon::parse($startDate) : null;
        $endDateCarbon = $endDate ? Carbon::parse($endDate) : null;

        $distributionCenterIds = $distributionCenterId ? [$distributionCenterId] : null;

        $totalExits = $this->stockMovementRepository->calculateTotalExits($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $totalExchanges = $this->stockMovementRepository->calculateTotalExchanges($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $fullBottles = $this->stockMovementRepository->calculateFullBottles($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $emptyBottles = $this->stockMovementRepository->calculateEmptyBottles($startDateCarbon, $endDateCarbon, $distributionCenterIds);

        return new StockMovementStatsDTO(
            totalExits: $totalExits,
            totalExchanges: $totalExchanges,
            fullBottles: $fullBottles,
            emptyBottles: $emptyBottles
        );
    }

    public function getStockStatsByMultipleCenters(?string $startDate = null, ?string $endDate = null, array $distributionCenterIds = []): StockMovementStatsDTO
    {
        $startDateCarbon = $startDate ? Carbon::parse($startDate) : null;
        $endDateCarbon = $endDate ? Carbon::parse($endDate) : null;

        $totalExits = $this->stockMovementRepository->calculateTotalExits($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $totalExchanges = $this->stockMovementRepository->calculateTotalExchanges($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $fullBottles = $this->stockMovementRepository->calculateFullBottles($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $emptyBottles = $this->stockMovementRepository->calculateEmptyBottles($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        return new StockMovementStatsDTO(
            totalExits: $totalExits,
            totalExchanges: $totalExchanges,
            fullBottles: $fullBottles,
            emptyBottles: $emptyBottles
        );
    }
}
