<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\DeliveryPerson;

interface DeliveryPersonRepositoryInterface extends BaseRepositoryInterface
{
    public function findLeastBusyDeliveryPerson(): ?DeliveryPerson;
}
