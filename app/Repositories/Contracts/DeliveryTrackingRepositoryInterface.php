<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\DeliveryTracking;

interface DeliveryTrackingRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): DeliveryTracking;

    public function findByOrderNumber(string $orderNumber): ?DeliveryTracking;

    public function findByOrder(int $orderId): ?DeliveryTracking;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(\Illuminate\Database\Eloquent\Model $deliveryTracking, array $attributes): DeliveryTracking;

    public function getActives(): \Illuminate\Database\Eloquent\Collection;
}
