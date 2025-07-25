<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\DeliveryPersonRepositoryInterface;
use App\Models\DeliveryPerson;

class DeliveryPersonService extends BaseServiceForEntity
{
    public function __construct(
            DeliveryPersonRepositoryInterface $deliveryPersonRepository
    ) {
        parent::__construct($deliveryPersonRepository);
    }

    protected function getModel(): string
    {
        return DeliveryPerson::class;
    }

    /**
     * Find the delivery person with the minimum number of pending orders.
     */
    public function findLeastBusyDeliveryPerson(): ?DeliveryPerson
    {
        return $this->deliveryPersonRepository->findLeastBusyDeliveryPerson();
    }
}
