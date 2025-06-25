<?php

namespace App\Services\Order;

use App\DTOs\Order\GroupedOrderItemDTO;
use App\DTOs\Order\OrderDetailsDTO;
use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Exceptions\OrderNotFoundException;
use App\Models\AccessoryType;
use App\Models\BottleType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {}

    /**
     * Get order with grouped items for display
     */
    public function getOrderWithGroupedItems(int $orderId): ?OrderDetailsDTO
    {
        $order = $this->orderRepository->getWithDetails($orderId);

        if (! $order) {
            return null;
        }

        $groupedItems = $this->groupOrderItems($order->items);

        return new OrderDetailsDTO(
            order: $order,
            groupedItems: $groupedItems
        );
    }

    /**
     * Group order items and return structured DTOs
     *
     * @param  Collection<OrderItem>  $items
     * @return Collection<GroupedOrderItemDTO>
     */
    public function groupOrderItems(Collection $items): Collection
    {
        return $items->groupBy(function ($item) {
            $productCategory = $item->productCategory;

            return match ($productCategory->product_type) {
                ProductType::BOTTLE() => $this->getBottleGroupingKey($item),
                ProductType::ACCESSORY() => $this->getAccessoryGroupingKey($item),
                default => 'unknown-'.$item->id,
            };
        })->map(function ($group, $groupKey) {
            $first = $group->first();
            $groupedQuantity = $group->sum('quantity');
            $groupedTotalPrice = $group->sum('total_price');

            return new GroupedOrderItemDTO(
                orderItem: $first,
                groupedQuantity: $groupedQuantity,
                groupedTotalPrice: $groupedTotalPrice,
                displayName: $this->getDisplayName($first),
                groupKey: $groupKey
            );
        })->values();
    }

    /**
     * Get display name for the grouped item
     */
    private function getDisplayName($item): string
    {
        $productCategory = $item->productCategory;

        return match ($productCategory->product_type) {
            ProductType::BOTTLE() => $this->getBottleDisplayName($item),
            ProductType::ACCESSORY() => $this->getAccessoryDisplayName($item),
            default => 'Produit inconnu',
        };
    }

    /**
     * Get display name for bottle items
     */
    private function getBottleDisplayName($item): string
    {
        $productCategory = $item->productCategory;
        $bottleType = BottleType::find($productCategory->product_type_id); // TODO: use a repository or service to get the bottle type

        if (! $bottleType) {
            return 'Bouteille inconnue';
        }

        $option = $item->bottle_type?->label;

        return "{$bottleType->name} ({$option})";
    }

    /**
     * Get display name for accessory items
     */
    private function getAccessoryDisplayName($item): string
    {
        $productCategory = $item->productCategory;
        $accessoryType = AccessoryType::find($productCategory->product_type_id); // TODO: use a repository or service to get the accessory type

        return $accessoryType?->name ?? 'Accessoire inconnu';
    }

    /**
     * Get grouping key for bottle items
     */
    private function getBottleGroupingKey($item): string
    {
        $productCategory = $item->productCategory;
        $bottleTypeId = $productCategory->product_type_id;

        if (! $bottleTypeId) {
            return 'bottle-unknown-'.$item->id;
        }

        return "bottle-{$bottleTypeId}-price-{$item->unit_price}";
    }

    /**
     * Get grouping key for accessory items
     */
    private function getAccessoryGroupingKey($item): string
    {
        $productCategory = $item->productCategory;
        $accessoryTypeId = $productCategory->product_type_id;

        if (! $accessoryTypeId) {
            return 'accessory-unknown-'.$item->id;
        }

        return "accessory-{$accessoryTypeId}";
    }

    /**
     * Cancel an order
     *
     * @throws \Exception
     */
    public function cancelOrder(int $orderId): bool
    {
        try {
            $order = $this->orderRepository->getById($orderId);

            if (! $order->canBeCancelled()) {
                throw new \Exception('Cette commande ne peut pas être annulée');
            }

            DB::beginTransaction();

            try {
                $result = $this->orderRepository->updateStatus($order, OrderStatus::CANCELLED());

                if ($result) {
                    DB::commit();

                    return true;
                }

                DB::rollBack();

                return false;

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (OrderNotFoundException $e) {
            throw $e;
        }
    }
}
