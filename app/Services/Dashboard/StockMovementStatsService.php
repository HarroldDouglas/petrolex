<?php

namespace App\Services\Dashboard;

use App\Contracts\Repositories\StockMovementRepositoryInterface;
use App\DTOs\Dashboard\StockMovementStatsDTO;
use Carbon\Carbon;

class StockMovementStatsService
{
    // PHP 8.0+ constructor property promotion: cleaner way to inject and assign
    public function __construct(private StockMovementRepositoryInterface $stockMovementRepository)
    {
        // The property is automatically declared and assigned.
        // No need for 'private StockMovementRepositoryInterface $stockMovementRepository;' above the constructor.
    }

    /**
     * Retrieves stock movement statistics based on date range and optional distribution center(s) IDs.
     * This is the consolidated method that replaces both previous public methods.
     *
     * @param string|null $startDate        Optional start date string.
     * @param string|null $endDate          Optional end date string.
     * @param array|null  $distributionCenterIds  Optional array of distribution center IDs.
     * If null or empty, stats for all centers (or as per repository's default) are fetched.
     * @return StockMovementStatsDTO
     */
    public function getStats(
        ?string $startDate = null,
        ?string $endDate = null,
        ?array $distributionCenterIds = null // This parameter is now an array or null
    ): StockMovementStatsDTO {
        // Convert date strings to Carbon instances
        $startDateCarbon = $startDate ? Carbon::parse($startDate) : null;
        $endDateCarbon = $endDate ? Carbon::parse($endDate) : null;

        // Call repository methods to get individual stats
        // The repository methods are already designed to handle null or empty arrays for $centerIds
        $totalExits = $this->stockMovementRepository->calculateTotalExits($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $totalExchanges = $this->stockMovementRepository->calculateTotalExchanges($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $fullBottles = $this->stockMovementRepository->calculateFullBottles($startDateCarbon, $endDateCarbon, $distributionCenterIds);
        $emptyBottles = $this->stockMovementRepository->calculateEmptyBottles($startDateCarbon, $endDateCarbon, $distributionCenterIds);

        // Return the Data Transfer Object
        return new StockMovementStatsDTO(
            total_exits: $totalExits,
            total_exchanges: $totalExchanges,
            full_bottles: $fullBottles,
            empty_bottles: $emptyBottles
        );
    }

    // You can remove the old getStockStats() and getStockStatsByMultipleCenters() methods.
    // The previous public methods were:
    // public function getStockStats(?string $startDate = null, ?string $endDate = null, ?string $distributionCenterId = null): StockMovementStatsDTO
    // public function getStockStatsByMultipleCenters(?string $startDate = null, ?string $endDate = null, array $distributionCenterIds = []): StockMovementStatsDTO
}