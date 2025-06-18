<?php

namespace App\DTOs\Dashboard;

class StockMovementStatsDTO
{
    public function __construct(
        public int $totalSoldBottles,
        public int $totalExchanges,
        public int $emptyBottles,
        public int $fullBottles,
        public int $totalSupplied
    ) {}
}
