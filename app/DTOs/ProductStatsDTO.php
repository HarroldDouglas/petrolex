<?php

namespace App\DTOs;

class ProductStatsDTO
{
    public function __construct(
        public readonly int $activeCount,
        public readonly int $inactiveCount,
        public readonly int $totalCount,
    ) {}
}
