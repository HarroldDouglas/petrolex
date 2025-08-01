<?php

namespace App\Services\Order;

use App\DTOs\Order\CreateOrderDTO;
use App\DTOs\Order\GroupedOrderItemDTO;
use App\DTOs\Order\OrderDetailsDTO;
use App\DTOs\Order\OrderItemDTO;
use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Events\OrderCreatedEvent;
use App\Models\AccessoryType;
use App\Models\BottleType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\BaseServiceForEntity;
use App\Services\ProductCategoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

class OrderService extends BaseServiceForEntity
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        private ProductCategoryService $productCategoryService
    ) {
        parent::__construct($this->orderRepository);
    }

    protected function getModel(): string
    {
        return Order::class;
    }

    /**
     * Create a new order.
     *
     * @param  array  $data  The data for creating the order.
     *
     * @throws \Exception
     * @throws ModelNotFoundException
     */
    public function create(array $data): Order
    {
        $orderDTO = CreateOrderDTO::from($data);

        return $this->executeInTransaction(function () use ($orderDTO) {

            $orderItemsData = array_map(function (OrderItemDTO $itemDTO): OrderItemDTO {

                $unitPrice = $this->productCategoryService->getProductPrice(
                    $itemDTO->product_category_id,
                    $itemDTO->option
                );

                $itemTotalPrice = $unitPrice * $itemDTO->quantity;

                $itemDTO->unit_price = $unitPrice;
                $itemDTO->total_price = $itemTotalPrice;
                $itemDTO->option = $itemDTO->option ?? null;

                return $itemDTO;
            }, $orderDTO->items);

            $subtotal = array_sum(array_column($orderItemsData, 'total_price'));

            $deliveryFee = $orderDTO->delivery_type->fee();
            $totalAmount = $subtotal + $deliveryFee;

            $orderData = $orderDTO->toArray();
            if (isset($orderData['items'])) {
                unset($orderData['items']);
            }

            $orderData['subtotal'] = $subtotal;
            $orderData['delivery_fee'] = $deliveryFee;
            $orderData['total_amount'] = $totalAmount;
            $orderData['status'] = OrderStatus::PENDING()->value;

            /** @var Order $order */
            $order = $this->repository->create($orderData);

            Event::dispatch(new OrderCreatedEvent($order, $orderItemsData));

            $order->load('items.productCategory');

            return $order;
        });
    }

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

    public function groupOrderItems(Collection $items): Collection
    {
        return $items->groupBy(function (OrderItem $item): string {
            $productCategory = $item->productCategory;

            return match ($productCategory->product_type) {
                ProductType::BOTTLE()->value => $this->getBottleGroupingKey($item),
                ProductType::ACCESSORY()->value => $this->getAccessoryGroupingKey($item),
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

    private function getDisplayName(OrderItem $item): string
    {
        $productCategory = $item->productCategory;

        return match ($productCategory->product_type) {
            ProductType::BOTTLE()->value => $this->getBottleDisplayName($item),
            ProductType::ACCESSORY()->value => $this->getAccessoryDisplayName($item),
            default => 'Produit inconnu',
        };
    }

    private function getBottleDisplayName(OrderItem $item): string
    {
        $productCategory = $item->productCategory;
        $bottleType = BottleType::find($productCategory->product_type_id);

        if (! $bottleType) {
            return 'Bouteille inconnue';
        }

        $option = $item->bottle_type?->label;

        return "{$bottleType->name} ({$option})";
    }

    private function getAccessoryDisplayName(OrderItem $item): string
    {
        $productCategory = $item->productCategory;
        $accessoryType = AccessoryType::find($productCategory->product_type_id);

        if (! $accessoryType) {
            return 'Accessoire inconnu';
        }

        return $accessoryType->name ?? 'Accessoire inconnu';
    }

    private function getBottleGroupingKey(OrderItem $item): string
    {
        $productCategory = $item->productCategory;
        $bottleTypeId = $productCategory->product_type_id;

        if (! $bottleTypeId) {
            return 'bottle-unknown-'.$item->id;
        }

        return "bottle-{$bottleTypeId}-price-{$item->unit_price}";
    }

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

        return $order->update(['status' => OrderStatus::CANCELLED()->value]);
    }

    /**
     * Assigns a delivery person to an order by their ID.
     *
     * @param  \App\Models\Order  $order  The order to assign the delivery person to.
     * @param  int  $deliveryPersonId  The ID of the delivery person to assign.
     */
    public function assignDeliveryPerson(Order $order, int $deliveryPersonId, ?string $reason = null): void
    {
        $this->orderRepository->assignDeliveryPerson($order, $deliveryPersonId, $reason);
    }
}
