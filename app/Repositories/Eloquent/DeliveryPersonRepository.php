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
        $query = $deliveryPerson->orders()->with(['customer', 'deliveryAddress', 'distributionCenter']);

        if ($filters->status) {
            $query->where('status', $filters->status);
        }

        if ($filters->order_number) {
            $query->where('order_number', 'like', '%'.$filters->order_number.'%');
        }

        if ($filters->delivery_type) {
            $query->where('delivery_type', $filters->delivery_type);
        }

        return $query->paginate($perPage);
    }
}
