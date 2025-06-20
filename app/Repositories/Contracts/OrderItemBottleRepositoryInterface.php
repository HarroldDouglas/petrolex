<?php

namespace App\Repositories\Contracts;

use App\Models\Bottle;
use App\Models\BottleType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemBottle;
use Illuminate\Database\Eloquent\Collection;

interface OrderItemBottleRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Associate a bottle with an order item
     */
    public function associateBottle(OrderItem $orderItem, Bottle $bottle): bool;

    /**
     * Disassociate a bottle from an order item
     */
    public function removeBottle(OrderItem $orderItem, Bottle $bottle): bool;

    /**
     * Get all bottles associated with an order item
     */
    public function getBottlesByOrderItem(OrderItem $orderItem): Collection;

    /**
     * Get all bottles associated with an order
     */
    public function getBottlesByOrder(Order $order): Collection;

    /**
     * Get bottle types for an order
     *
     * @return Collection<int, BottleType> Collection of BottleType models
     */
    public function getBottleTypesByOrder(Order $order): Collection;

    /**
     * Get OrderItemBottles for a specific bottle type in an order with eager loaded bottle relationship
     *
     * @return Collection<int, OrderItemBottle> Collection of OrderItemBottle models with bottle relationship
     */
    public function getOrderItemBottlesByBottleType(Order $order, int $bottleTypeId): Collection;

    /**
     * Check if a bottle is already scanned for a specific order
     */
    public function isBottleAlreadyScanned(Bottle $bottle, Order $order): bool;

    /**
     * Check if a bottle is already scanned for any order
     */
    public function isBottleAlreadyScannedInAnyOrder(Bottle $bottle): bool;

    /**
     * Remove bottle associations for an order by bottle IDs
     */
    public function removeBottlesFromOrder(Order $order, array $bottleIds): bool;

    /**
     * Find a suitable OrderItem for a scanned bottle
     */
    public function findOrderItemForBottle(Order $order, Bottle $bottle): ?OrderItem;
}
