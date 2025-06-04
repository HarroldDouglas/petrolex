<?php

namespace App\Contracts\Repositories;

use Carbon\Carbon;

interface StockMovementRepositoryInterface
{
    public function calculateTotalEntries(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int;
    public function calculateTotalExits(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int;
    public function calculateTotalExchanges(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $centerIds = null): int;
    public function calculateCurrentStock(?array $centerIds = null): int; // Current stock often not date-filtered
}