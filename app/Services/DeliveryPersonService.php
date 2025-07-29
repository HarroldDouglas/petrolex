<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DeliveryPerson;
use App\Repositories\Contracts\DeliveryPersonRepositoryInterface;

class DeliveryPersonService extends BaseServiceForEntity
{
    public function __construct(
        protected DeliveryPersonRepositoryInterface $deliveryPersonRepository
    ) {
        parent::__construct($deliveryPersonRepository);
        $this->deliveryPersonRepository = $deliveryPersonRepository;
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
