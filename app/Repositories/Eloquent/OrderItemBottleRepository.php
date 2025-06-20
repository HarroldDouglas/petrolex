<?php

namespace App\Repositories\Eloquent;

use App\Enums\ProductType;
use App\Models\Bottle;
use App\Models\BottleType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemBottle;
use App\Repositories\Contracts\OrderItemBottleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class OrderItemBottleRepository extends BaseEloquentRepository implements OrderItemBottleRepositoryInterface
{
    public function __construct(OrderItemBottle $model)
    {
        parent::__construct($model);
    }

    /**
     * Associate a bottle with an order item
     */
    public function associateBottle(OrderItem $orderItem, Bottle $bottle): bool
    {
        try {
            $this->model->create([
                'order_item_id' => $orderItem->id,
                'bottle_id' => $bottle->id,
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Disassociate a bottle from an order item
     */
    public function removeBottle(OrderItem $orderItem, Bottle $bottle): bool
    {
        return (bool) $this->model
            ->where('order_item_id', $orderItem->id)
            ->where('bottle_id', $bottle->id)
            ->delete();
    }

    /**
     * Get all bottles associated with an order item
     */
    public function getBottlesByOrderItem(OrderItem $orderItem): Collection
    {
        return $orderItem->bottles()->get();
    }

    /**
     * Get all bottles associated with an order
     */
    public function getBottlesByOrder(Order $order): Collection
    {
        return Bottle::whereIn('id', function ($query) use ($order) {
            $query->select('bottle_id')
                ->from('order_item_bottles')
                ->join('order_items', 'order_item_bottles.order_item_id', '=', 'order_items.id')
                ->where('order_items.order_id', $order->id);
        })->get();
    }

    /**
     * Get bottle types for an order
     *
     * @return Collection<int, BottleType> Collection of BottleType models
     */
    public function getBottleTypesByOrder(Order $order): Collection
    {
        return BottleType::whereIn('id', function ($query) use ($order) {
            $query->select('bottles.bottle_type_id')
                ->from('products')
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('bottles', 'products.id', '=', 'bottles.product_id')
                ->where('order_items.order_id', $order->id)
                ->where('products.product_type', ProductType::BOTTLE());
        })->get();
    }

    /**
     * Check if a bottle is already scanned for a specific order
     */
    public function isBottleAlreadyScanned(Bottle $bottle, Order $order): bool
    {
        return $this->model
            ->whereHas('orderItem', function ($query) use ($order) {
                $query->where('order_id', $order->id);
            })
            ->where('bottle_id', $bottle->id)
            ->exists();
    }

    /**
     * Check if a bottle is already scanned for any order item
     */
    public function isBottleAlreadyScannedInAnyOrder(Bottle $bottle): bool
    {
        return $this->model->where('bottle_id', $bottle->id)->exists();
    }

    /**
     * Remove bottle associations for an order by bottle IDs
     */
    public function removeBottlesFromOrder(Order $order, array $bottleIds): bool
    {
        try {
            $this->model
                ->whereIn('bottle_id', $bottleIds)
                ->whereHas('orderItem', function ($query) use ($order) {
                    $query->where('order_id', $order->id);
                })
                ->delete();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Find a suitable OrderItem for a scanned bottle
     */
    public function findOrderItemForBottle(Order $order, Bottle $bottle): ?OrderItem
    {
        $bottleTypeId = $bottle->bottle_type_id;

        /** @var OrderItem|null */
        return $order->items()
            ->whereHas('product.bottle', function ($query) use ($bottleTypeId) {
                $query->where('bottle_type_id', $bottleTypeId);
            })
            ->get()
            ->filter(function (OrderItem $item) {
                return $item->scanned_bottles_count < $item->quantity;
            })
            ->first();
    }

    /**
     * Get OrderItemBottles for a specific bottle type in an order with eager loaded bottle relationship
     *
     * @return Collection<int, OrderItemBottle> Collection of OrderItemBottle models with bottle relationship
     */
    public function getOrderItemBottlesByBottleType(Order $order, int $bottleTypeId): Collection
    {
        $orderItemIds = $order->items()
            ->whereHas('product.bottle', function ($query) use ($bottleTypeId) {
                $query->where('bottle_type_id', $bottleTypeId);
            })
            ->pluck('id')
            ->toArray();

        if (empty($orderItemIds)) {
            return collect();
        }

        /** @var Collection<int, OrderItemBottle> */
        return $this->model
            ->with('bottle')
            ->whereIn('order_item_id', $orderItemIds)
            ->get();
    }
}
