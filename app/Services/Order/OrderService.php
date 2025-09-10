<?php

namespace App\Services\Order;

use App\DTOs\Order\CreateOrderDTO;
use App\DTOs\Order\GroupedOrderItemDTO;
use App\DTOs\Order\OrderDetailsDTO;
use App\DTOs\Order\OrderItemDTO;
use App\DTOs\Order\ScanEmptyBottleDTO;
use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Events\EmptyBottleReturnedEvent;
use App\Events\OrderCreatedEvent;
use App\Events\OrderDeliveredEvent;
use App\Events\OrderStatusChanged;
use App\Models\AccessoryType;
use App\Models\Bottle;
use App\Models\BottleType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Contracts\BottleRepositoryInterface;
use App\Repositories\Contracts\OrderBottleScanRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\BaseServiceForEntity;
use App\Services\ProductCategoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

class OrderService extends BaseServiceForEntity
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        private readonly BottleRepositoryInterface $bottleRepository,
        private readonly OrderBottleScanRepositoryInterface $orderBottleScanRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductCategoryService $productCategoryService
    ) {
        parent::__construct($this->orderRepository);
    }

    protected function getModel(): string
    {
        return Order::class;
    }

    /**
     * Handle the return of an empty bottle.
     */
    public function handleEmptyBottleReturn(ScanEmptyBottleDTO $dto, Order $order): bool
    {
        return $this->executeInTransaction(function () use ($dto, $order) {
            /** @var Bottle|null $bottle */
            $bottle = $this->bottleRepository->findByBarcode($dto->barcode);

            if ($bottle && $bottle->is_filled) {
                return false;
            }

            if ($bottle && $this->orderBottleScanRepository->existsEmptyBottleForOrderItem($dto->orderItemId, $bottle->id)) {
                return false;
            }

            $scanToUpdate = $this->orderBottleScanRepository->findUnassignedEmptyBottleScan($dto->orderItemId);

            if (! $scanToUpdate) {
                return false;
            }

            if (! $bottle) {
                /** @var OrderItem $orderItem */
                $orderItem = OrderItem::query()->findOrFail($dto->orderItemId);

                /** @var \App\Models\Product $product */
                $product = $this->productRepository->create([
                    'product_category_id' => $orderItem->product_category_id,
                ]);

                /** @var \App\Models\Bottle $bottle */
                $bottle = $this->bottleRepository->create([
                    'barcode' => $dto->barcode,
                    'product_id' => $product->id,
                    'distribution_center_id' => $order->distribution_center_id,
                    'is_filled' => false,
                ]);
            } else {
                $this->bottleRepository->update($bottle, [
                    'is_filled' => false,
                    'distribution_center_id' => $order->distribution_center_id,
                ]);
            }

            $this->orderBottleScanRepository->update($scanToUpdate, [
                'empty_bottle_id' => $bottle->id,
            ]);

            Event::dispatch(new EmptyBottleReturnedEvent($bottle, $order, $dto->orderItemId));

            return true;
        });
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

    /**
     * Mark an order as delivered.
     *
     * @param  Order  $order  The order to mark as delivered.
     * @return ?Order The updated order.
     */
    public function deliverOrder(Order $order): ?Order
    {

        if (! $order->canBeDelivered()) {
            return null;
        }

        $updatedOrder = $this->update($order, ['status' => OrderStatus::DELIVERED()->value]);
        
        Event::dispatch(new OrderDeliveredEvent($updatedOrder));

        return $updatedOrder instanceof Order ? $updatedOrder : null;
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

        $result = $this->update($order, ['status' => OrderStatus::CANCELLED()->value]);

        return (bool) $result;
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

    /**
     * Update the status of an order.
     *
     * @param  \App\Models\Order  $order  The order to update.
     * @param  \App\Enums\OrderStatus  $status  The new status for the order.
     */
    public function updateOrderStatus(Order $order, OrderStatus $status): void
    {
        $this->update($order, ['status' => $status->value]);
    }

    /**
     * Override the update method to manually dispatch OrderStatusChanged events
     */
    public function update(\Illuminate\Database\Eloquent\Model $model, array $data): \Illuminate\Database\Eloquent\Model
    {
        $oldStatus = $model->status ?? null;

        $result = parent::update($model, $data);
        if (isset($data['status']) && $result) {
            $newStatus = OrderStatus::from($data['status']);
            Event::dispatch(new OrderStatusChanged($result, $oldStatus, $newStatus));
        }

        return $result;
    }
}
