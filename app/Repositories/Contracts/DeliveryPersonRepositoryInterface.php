<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\Order\GetOrdersFilterDTO;
use App\Models\DeliveryPerson;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DeliveryPersonRepositoryInterface extends BaseRepositoryInterface
{
    public function findLeastBusyDeliveryPerson(?int $distributionCenterId = null): ?DeliveryPerson;

    public function getOrdersForDeliveryPerson(DeliveryPerson $deliveryPerson, GetOrdersFilterDTO $filters, int $perPage): LengthAwarePaginator;
}
