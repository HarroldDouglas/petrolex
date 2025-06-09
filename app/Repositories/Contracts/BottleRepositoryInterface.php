<?php

namespace App\Repositories\Contracts;

use App\Enums\BottleStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface BottleRepositoryInterface extends baseRepositoryInterface
{
    public function getBottleHistory($bottleId): Collection;

    public function updateStatus($bottleId, BottleStatus $status): void;

    /**
     * Count bottles with in_stock status
     */
    public function countInStockBottles(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): int;

    /**
     * Count bottles with with_delivery_person status
     */
    public function countWithDeliveryPersonBottles(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): int;

    /**
     * Count bottles with with_client status
     */
    public function countWithClientBottles(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): int;

    /**
     * Count bottles with lost_stolen status
     */
    public function countLostStolenBottles(?Carbon $startDate = null, ?Carbon $endDate = null, ?array $distributionCenterIds = null): int;
}
