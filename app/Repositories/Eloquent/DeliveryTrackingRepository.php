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

        return $deliveryTracking;
    }

    public function findByOrderNumber(string $orderNumber): ?DeliveryTracking
    {
        /** @var DeliveryTracking|null $deliveryTracking */
        $deliveryTracking = $this->model->where('order_number', $orderNumber)->first();

        return $deliveryTracking;
    }

    public function findByOrder(int $orderId): ?DeliveryTracking
    {
        /** @var DeliveryTracking|null $deliveryTracking */
        $deliveryTracking = $this->model->where('order_id', $orderId)->first();

        return $deliveryTracking;
    }

    public function update(\Illuminate\Database\Eloquent\Model $deliveryTracking, array $attributes): DeliveryTracking
    {
        /** @var DeliveryTracking $deliveryTracking */
        $deliveryTracking->update($attributes);

        return $deliveryTracking;
    }

    public function getActives(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->whereIn('status', [
            \App\Enums\DeliveryTrackingStatus::PENDING(),
            \App\Enums\DeliveryTrackingStatus::STARTED(),
            \App\Enums\DeliveryTrackingStatus::IN_PROGRESS(),
        ])
            ->orderByDesc('created_at')
            ->get();
    }
}
