<?php

namespace App\Services\Order;

use App\DTOs\Order\CreateOrderDTO;
use App\DTOs\Order\GroupedOrderItemDTO;
use App\DTOs\Order\OrderDetailsDTO;
use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Exceptions\OrderNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\AccessoryType;
use App\Models\BottleType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductCategory;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\BaseServiceForEntity;
use App\Services\ProductCategoryService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class OrderService extends BaseServiceForEntity
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        private ProductCategoryService $productCategoryService
    ) {
        parent::__construct($orderRepository);
    }

    protected function getModel(): string
    {
        return Order::class;
    }

    public function createOrder(CreateOrderDTO $orderDTO): Order
    {
        return $this->executeInTransaction(function () use ($orderDTO) {
            $totalAmount = 0;
            $orderItemsData = [];

            foreach ($orderDTO->items as $itemDTO) {
                $productCategory = $this->productCategoryService->getProductCategory($itemDTO->product_category_id);
                $productInstance = $this->productCategoryService->getProductInstance($productCategory);

                $unitPrice = $this->productCategoryService->getProductPrice(
                    $productCategory,
                    $productInstance,
                    $itemDTO->option
                );

                $availableQuantity = $this->productCategoryService->getProductQuantity(
                    $productCategory,
                    $orderDTO->distribution_center_id
                );

                if ($availableQuantity < $itemDTO->quantity) {
                    throw new \Exception('Insufficient stock for product: ' . $productCategory->name);
                }

                // TODO: Deduct stock after order creation (e.g., in a listener)

                $itemTotalPrice = $unitPrice * $itemDTO->quantity;
                $totalAmount += $itemTotalPrice;

                $orderItemsData[] = [
                    'product_category_id' => $itemDTO->product_category_id,
                    'quantity' => $itemDTO->quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $itemTotalPrice,
                    'option' => $itemDTO->option, // Nullable for non-bottles
                ];
            }

            $orderData = [
                'customer_id' => $orderDTO->customer_id,
                'delivery_address_id' => $orderDTO->delivery_address_id,
                'distribution_center_id' => $orderDTO->distribution_center_id,
                'delivery_type' => $orderDTO->delivery_type->value,
                'payment_method' => $orderDTO->payment_method->value,
                'total_amount' => $totalAmount,
                'status' => OrderStatus::CONFIRMED()->value, // Default status
            ];

            /** @var Order $order */
            $order = $this->repository->create($orderData);

            // Dispatch event to add order items and log
            Event::dispatch(new \App\Events\OrderCreatedEvent($order, $orderItemsData));

            return $order;
        });
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

    /**
     * Get display name for the grouped item
     */
    private function getDisplayName(OrderItem $item): string
    {
        $productCategory = $item->productCategory;

        return match ($productCategory->product_type) {
            ProductType::BOTTLE()->value => $this->getBottleDisplayName($item),
            ProductType::ACCESSORY()->value => $this->getAccessoryDisplayName($item),
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
     * @throws OrderNotFoundException
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
                $result = $this->orderRepository->updateStatus($order, OrderStatus::CANCELLED()->value);

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
