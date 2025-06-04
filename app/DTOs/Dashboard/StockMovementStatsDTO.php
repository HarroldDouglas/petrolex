<?php

namespace App\DTOs\Dashboard;

class StockMovementStatsDTO
{
    public function __construct(
        public int $totalEntries,
        public int $totalExits,
        public int $totalExchanges,
        public int $currentStock,
        public int $totalStock
    ) {}
}