<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Order\GetOrdersFilterDTO;
use App\Models\DeliveryPerson;
use App\Repositories\Contracts\DeliveryPersonRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

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

    public function getAll(): Collection
    {
        return DeliveryPerson::whereHas('user')
            ->with('user')
            ->get();
    }

    /**
     * Find the delivery person with the minimum number of pending orders.
     * If distributionCenterId is provided, only consider delivery persons assigned to that center.
     */
    public function findLeastBusyDeliveryPerson(?int $distributionCenterId = null): ?DeliveryPerson
    {
        return $this->deliveryPersonRepository->findLeastBusyDeliveryPerson($distributionCenterId);
    }

    public function getOrders(DeliveryPerson $deliveryPerson, GetOrdersFilterDTO $filters, int $perPage): LengthAwarePaginator
    {
        return $this->deliveryPersonRepository->getOrdersForDeliveryPerson($deliveryPerson, $filters, $perPage);
    }
}
