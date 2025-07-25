<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\DeliveryPersonRepositoryInterface;
use App\Enums\OrderStatus;
use App\Models\DeliveryPerson;

class DeliveryPersonRepository extends BaseEloquentRepository implements DeliveryPersonRepositoryInterface
{
    public function __construct(DeliveryPerson $model)
    {
        parent::__construct($model);
    }

    public function findLeastBusyDeliveryPerson(): ?DeliveryPerson
    {
        return $this->model::withCount([
            'orders' => function ($query) {
                $query->whereIn('status', [
                    OrderStatus::CONFIRMED(),
                    OrderStatus::PROCESSING(),
                ]);
            },
        ])
            ->orderBy('orders_count', 'asc')
            ->first();
    }
}
