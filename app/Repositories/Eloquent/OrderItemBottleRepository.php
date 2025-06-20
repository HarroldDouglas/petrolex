<?php

namespace App\Repositories\Eloquent;

use App\Enums\ProductType;
use App\Models\Bottle;
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
     * Get bottle types and their scan completion status for an order
     */
    public function getBottleTypesWithScanStatus(Order $order): array
    {
        $result = [];

        // Get all bottle order items for this order
        $bottleItems = $order->items()
            ->whereHas('product', function ($query) {
                $query->where('product_type', ProductType::BOTTLE());
            })
            ->with(['product.bottle.bottleType'])
            ->get();

        // Group by bottle type
        foreach ($bottleItems as $item) {
            $bottleType = $item->product->bottle->bottleType;
            $bottleTypeId = $bottleType->id;

            if (! isset($result[$bottleTypeId])) {
                $result[$bottleTypeId] = [
                    'bottle_type' => $bottleType,
                    'total_quantity' => 0,
                    'scanned_quantity' => 0,
                    'is_complete' => false,
                ];
            }

            $result[$bottleTypeId]['total_quantity'] += $item->quantity;
            $result[$bottleTypeId]['scanned_quantity'] += $item->orderItemBottles()->count();
        }

        // Set completion status for each bottle type
        foreach ($result as $key => $value) {
            $result[$key]['is_complete'] =
                $value['scanned_quantity'] >= $value['total_quantity'];
        }

        return array_values($result);
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
        // Get the bottle type
        $bottleTypeId = $bottle->bottle_type_id;

        // Find an order item with this bottle type that needs scanning
        return $order->items()
            ->whereHas('product.bottle', function ($query) use ($bottleTypeId) {
                $query->where('bottle_type_id', $bottleTypeId);
            })
            ->get()
            ->filter(function ($item) {
                return $item->scanned_bottles_count < $item->quantity;
            })
            ->first();
    }
}
