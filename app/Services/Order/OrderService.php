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
use \App\Events\OrderCreatedEvent;

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

    /**
     * Create a new order.
     *
     * @param array $data The data for creating the order.
     * @return Order
     * @throws \Exception
     * @throws ModelNotFoundException
     */
    public function create(array $data): Order
    {
        $orderDTO = CreateOrderDTO::from($data);

        return $this->executeInTransaction(function () use ($orderDTO) {
            $totalAmount = 0;
            $processedOrderItemsData = [];

            foreach ($orderDTO->items as $itemDTO) {
                $productCategory = $this->productCategoryService->find($itemDTO->product_category_id);

                if (! $productCategory) {
                    throw new ModelNotFoundException('Product category not found.');
                }

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

                if (! $this->checkItemAvailabilityInStock($productCategory, $orderDTO->distribution_center_id, $itemDTO->quantity)) {
                    throw new \Exception('Insufficient stock for product: ' . ($productCategory instanceof ProductCategory ? $productCategory->name : 'Unknown'));
                }

                // TODO: Deduct stock after order creation (e.g., in a listener)

                $itemTotalPrice = $this->calculateOrderItemTotalPrice($unitPrice, $itemDTO->quantity);
                $totalAmount += $itemTotalPrice;

                // Convert item DTO to array and augment with calculated prices
                $itemArray = $itemDTO->toArray();
                $itemArray['unit_price'] = $unitPrice;
                $itemArray['total_price'] = $itemTotalPrice;

                $processedOrderItemsData[] = $itemArray;
            }

            // Get base order data from DTO and augment with calculated/generated fields
            $orderData = $orderDTO->toArray();
            unset($orderData['items']); // Remove items as they are handled separately

            $orderData['total_amount'] = $totalAmount;
            $orderData['subtotal'] = $totalAmount; // Assuming subtotal is initially the same as total_amount
            $orderData['status'] = OrderStatus::CONFIRMED()->value; // Default status
            $orderData['order_number'] = uniqid('ORDER-'); // Generate unique order number

            /** @var Order $order */
            $order = $this->repository->create($orderData);

            Event::dispatch(new OrderCreatedEvent($order, $processedOrderItemsData));

            // Eager load items and their productCategory relationship
            $order->load('items.productCategory');

            // Manually eager load productTypeInstance for each productCategory in a single loop
            $bottleTypeIds = [];
            $accessoryTypeIds = [];

            // Collect all necessary IDs first
            foreach ($order->items as $item) {
                /** @var ProductCategory $productCategory */
                $productCategory = $item->productCategory;

                if ($productCategory->product_type->value === ProductType::BOTTLE()->value) {
                    $bottleTypeIds[] = $productCategory->product_type_id;
                } elseif ($productCategory->product_type->value === ProductType::ACCESSORY()->value) {
                    $accessoryTypeIds[] = $productCategory->product_type_id;
                }
            }

            // Fetch all instances in bulk
            $bottleTypes = BottleType::findMany(array_unique($bottleTypeIds))->keyBy('id');
            $accessoryTypes = AccessoryType::findMany(array_unique($accessoryTypeIds))->keyBy('id');

            // Assign pre-fetched instances to productCategory->productTypeInstance
            foreach ($order->items as $item) {
                /** @var ProductCategory $productCategory */
                $productCategory = $item->productCategory;

                if ($productCategory->product_type->value === ProductType::BOTTLE()->value) {
                    $productCategory->setRelation('productTypeInstance', $bottleTypes->get($productCategory->product_type_id));
                } elseif ($productCategory->product_type->value === ProductType::ACCESSORY()->value) {
                    $productCategory->setRelation('productTypeInstance', $accessoryTypes->get($productCategory->product_type_id));
                }
            }

            return $order;
        });
    }

    /**
     * Calculate the total price for a single order item.
     */
    private function calculateOrderItemTotalPrice(float $unitPrice, int $quantity): float
    {
        return $unitPrice * $quantity;
    }

    /**
     * Check if an item is available in stock at a given distribution center.
     */
    private function checkItemAvailabilityInStock(
        ProductCategory $productCategory,
        int $distributionCenterId,
        int $requestedQuantity
    ): bool {
        $availableQuantity = $this->productCategoryService->getProductQuantity(
            $productCategory,
            $distributionCenterId
        );

        return $availableQuantity >= $requestedQuantity;
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