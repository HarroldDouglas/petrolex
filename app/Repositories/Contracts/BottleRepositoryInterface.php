<?php

namespace App\Repositories\Contracts;

use App\Models\Bottle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface BottleRepositoryInterface extends BaseRepositoryInterface
{
    public function getBottleHistory($bottleId): Collection;

    /**
     * Find a bottle by its barcode
     */
    public function findByBarcode(string $barcode): ?Bottle;

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
