<?php

namespace App\Services\Order;

use App\DTOs\Order\GroupedOrderItemDTO;
use App\DTOs\Order\OrderDetailsDTO;
use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Models\AccessoryType;
use App\Models\BottleType;
use App\Models\DeliveryPerson;
use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\BaseServiceForEntity;
use Illuminate\Support\Collection;

class OrderService extends BaseServiceForEntity
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($orderRepository);
    }

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
     * @param  Collection<int, OrderItem>  $items
     * @return Collection<int, GroupedOrderItemDTO>
     */
    public function groupOrderItems(Collection $items): Collection
    {
        return $items->groupBy(function (OrderItem $item): string {
            $productCategory = $item->productCategory;

            return match ($productCategory->product_type) {
                ProductType::BOTTLE() => $this->getBottleGroupingKey($item),
                ProductType::ACCESSORY() => $this->getAccessoryGroupingKey($item),
                default => 'unknown-'.$item->id,
            };
        })->map(function (Collection $group, string $groupKey): GroupedOrderItemDTO {
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
    private function getDisplayName(OrderItem $item): string
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
    private function getBottleDisplayName(OrderItem $item): string
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
    private function getAccessoryDisplayName(OrderItem $item): string
    {
        $productCategory = $item->productCategory;
        $accessoryType = AccessoryType::find($productCategory->product_type_id); // TODO: use a repository or service to get the accessory type

        if (! $accessoryType) {
            return 'Accessoire inconnu';
        }

        return $accessoryType->name ?? 'Accessoire inconnu';
    }

    /**
     * Get grouping key for bottle items
     */
    private function getBottleGroupingKey(OrderItem $item): string
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
    private function getAccessoryGroupingKey(OrderItem $item): string
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
        $order = $this->orderRepository->getById($orderId);

        if (! $order->canBeCancelled()) {
            throw new \Exception('Cette commande ne peut pas être annulée');
        }

        return $this->executeInTransaction(function () use ($order) {
            $order->status = OrderStatus::CANCELLED();

            return $order->save();
        });
    }

    /**
     * Assigns the most suitable delivery person to an order based on defined criteria.
     *
     * @param  Order  $order  The order to assign a delivery person to.
     * @return DeliveryPerson|null The assigned delivery person, or null if none found.
     */
    public function assignDeliveryPerson(Order $order): ?DeliveryPerson
    {
        $distributionCenterId = $order->distribution_center_id;

        if (! $distributionCenterId) {
            return null; // Or throw an exception if distribution center is mandatory
        }

        // Get all active delivery persons for the given distribution center
        $deliveryPersons = DeliveryPerson::whereHas('activeDistributionCenters', function ($query) use ($distributionCenterId) {
            $query->where('distribution_centers.id', $distributionCenterId);
        })->get();

        if ($deliveryPersons->isEmpty()) {
            return null; // No delivery persons found for this distribution center
        }

        $eligibleDeliveryPersons = collect();

        foreach ($deliveryPersons as $deliveryPerson) {
            $confirmedOrdersCount = $deliveryPerson->orders()->where('status', OrderStatus::CONFIRMED())->count();
            $processingOrdersCount = $deliveryPerson->orders()->where('status', OrderStatus::PROCESSING())->count();

            $eligibleDeliveryPersons->push([
                'deliveryPerson' => $deliveryPerson,
                'confirmedOrdersCount' => $confirmedOrdersCount,
                'processingOrdersCount' => $processingOrdersCount,
                'rating' => $deliveryPerson->calculateRating(),
            ]);
        }

        // Sort by confirmed orders count (ascending)
        $eligibleDeliveryPersons = $eligibleDeliveryPersons->sortBy('confirmedOrdersCount');

        // Filter for those with no processing orders
        $bestCandidates = $eligibleDeliveryPersons->groupBy('confirmedOrdersCount')->first();

        $noProcessingCandidates = $bestCandidates->filter(function ($candidate) {
            return $candidate['processingOrdersCount'] === 0;
        });

        if ($noProcessingCandidates->isNotEmpty()) {
            // If there are candidates with no processing orders, sort them by rating (descending)
            $selectedCandidates = $noProcessingCandidates->sortByDesc('rating');
        } else {
            // If all best candidates have processing orders, sort them by rating (descending)
            $selectedCandidates = $bestCandidates->sortByDesc('rating');
        }

        $selectedDeliveryPerson = $selectedCandidates->first()['deliveryPerson'] ?? null;

        if ($selectedDeliveryPerson) {
            $order->delivery_person_id = $selectedDeliveryPerson->id;
            $order->save(); // Save the order with the assigned delivery person
        }

        return $selectedDeliveryPerson;
    }

    protected function getModel(): string
    {
        return Order::class;
    }
}
