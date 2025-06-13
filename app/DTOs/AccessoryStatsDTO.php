<?php

namespace App\DTOs;

class AccessoryStatsDTO
{
    public function __construct(
        public readonly int $activeCount,
        public readonly int $inactiveCount,
        public readonly int $totalCount,
    ) {}
}
