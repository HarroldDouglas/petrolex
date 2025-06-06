<?php

namespace App\Repositories\Contracts;

use Carbon\Carbon;

interface StockMovementRepositoryInterface
{
    public function calculateTotalExits(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int;
    public function calculateFullBottles(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int;
    public function calculateEmptyBottles(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int;
}