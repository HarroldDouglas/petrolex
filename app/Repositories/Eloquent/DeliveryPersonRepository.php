<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\OrderStatus;
use App\Models\DeliveryPerson;
use App\Repositories\Contracts\DeliveryPersonRepositoryInterface;

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
}
