<?php

namespace App\Repositories\Contracts;

use App\Models\Bottle;
use App\Models\Order;
use App\Models\OrderItem;
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
     * Get bottle types and their scan completion status for an order
     */
    public function getBottleTypesWithScanStatus(Order $order): array;

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
