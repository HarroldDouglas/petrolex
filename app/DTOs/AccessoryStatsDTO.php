<?php

namespace App\DTOs;

class AccessoryStatsDTO extends BaseDTO
{
    public function __construct(
        public readonly int $activeCount,
        public readonly int $inactiveCount,
        public readonly int $totalCount,
    ) {}
}
