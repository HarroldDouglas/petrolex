<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Order\GetOrdersFilterDTO;
use App\Models\DeliveryPerson;
use App\Repositories\Contracts\DeliveryPersonRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

    public function getOrders(DeliveryPerson $deliveryPerson, GetOrdersFilterDTO $filters, int $perPage): LengthAwarePaginator
    {
        return $this->deliveryPersonRepository->getOrdersForDeliveryPerson($deliveryPerson, $filters, $perPage);
    }
}
