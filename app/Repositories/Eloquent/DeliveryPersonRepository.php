<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\Order\GetOrdersFilterDTO;
use App\Enums\OrderStatus;
use App\Models\DeliveryPerson;
use App\Repositories\Contracts\DeliveryPersonRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DeliveryPersonRepository extends BaseEloquentRepository implements DeliveryPersonRepositoryInterface
{
    public function __construct(DeliveryPerson $model)
    {
        parent::__construct($model);
    }

    public function findLeastBusyDeliveryPerson(): ?DeliveryPerson
    {
        /** @var \App\Models\DeliveryPerson|null $deliveryPerson */
        $deliveryPerson = $this->model::withCount([
            'orders' => function ($query) {
                $query->whereIn('status', [
                    OrderStatus::CONFIRMED(),
                    OrderStatus::PROCESSING(),
                ]);
            },
        ])
            ->orderBy('orders_count', 'asc')
            ->first();

        return $deliveryPerson;
    }

    public function getOrdersForDeliveryPerson(DeliveryPerson $deliveryPerson, GetOrdersFilterDTO $filters, int $perPage): LengthAwarePaginator
    {
        return $deliveryPerson->orders()
            ->with(['customer', 'deliveryAddress', 'distributionCenter', 'payment'])
            ->when(
                $filters->order_number !== null && $filters->order_number !== '',
                fn ($q) => $q->where('order_number', $filters->order_number)
            )->when(
                $filters->status !== null,
                fn ($q) => $q->where('status', $filters->status)
            )
            ->when(
                $filters->delivery_type !== null,
                fn ($q) => $q->where('delivery_type', $filters->delivery_type)
            )
            ->paginate($perPage);
    }
}
