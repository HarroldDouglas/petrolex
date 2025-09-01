<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\DeliveryTracking;
use App\Repositories\Contracts\DeliveryTrackingRepositoryInterface;

final class DeliveryTrackingRepository extends BaseEloquentRepository implements DeliveryTrackingRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new DeliveryTracking);
    }

    public function create(array $attributes): DeliveryTracking
    {
        /** @var DeliveryTracking $deliveryTracking */
        $deliveryTracking = $this->model->create($attributes);

        return $deliveryTracking->load([
            'order.customer',
            'order.deliveryAddress',
            'order.deliveryPerson',
        ]);
    }

    public function findByOrderNumber(string $orderNumber): ?DeliveryTracking
    {
        /** @var DeliveryTracking|null $deliveryTracking */
        $deliveryTracking = $this->model
            ->with([
                'order.customer',
                'order.deliveryAddress',
                'order.deliveryPerson',
            ])
            ->whereHas('order', function ($query) use ($orderNumber) {
                $query->where('order_number', $orderNumber);
            })
            ->first();

        return $deliveryTracking;
    }

    public function findByOrder(int $orderId): ?DeliveryTracking
    {
        /** @var DeliveryTracking|null $deliveryTracking */
        $deliveryTracking = $this->model
            ->with([
                'order.customer',
                'order.deliveryAddress',
                'order.deliveryPerson',
            ])
            ->where('order_id', $orderId)
            ->first();

        return $deliveryTracking;
    }

    public function update(\Illuminate\Database\Eloquent\Model $deliveryTracking, array $attributes): DeliveryTracking
    {
        /** @var DeliveryTracking $deliveryTracking */
        $deliveryTracking->update($attributes);

        return $deliveryTracking->load([
            'order.customer',
            'order.deliveryAddress',
            'order.deliveryPerson',
        ]);
    }
}
