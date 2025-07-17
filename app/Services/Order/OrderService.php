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
use Spatie\LaravelData\DataCollection;

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
     * @param array $data The data for creating the order.
     * @return Order
     * @throws \Exception
     * @throws ModelNotFoundException
     */
    public function create(array $data): Order
    {
        $orderDTO = CreateOrderDTO::from($data);

        return $this->executeInTransaction(function () use ($orderDTO) {
            $subtotal = 0;
            $processedOrderItemsData = [];

            foreach ($orderDTO->items as $itemDTO) {
                $productCategory = $this->productCategoryService->find($itemDTO->product_category_id);

                if (! $productCategory) {
                    throw new ModelNotFoundException('Product category not found.');
                }

                $unitPrice = $this->productCategoryService->getProductPrice(
                    $productCategory,
                    $itemDTO->option
                );

                $availableQuantity = $this->productCategoryService->getProductQuantity(
                    $productCategory,
                    $orderDTO->distribution_center_id
                );

                if ($availableQuantity < $itemDTO->quantity) {
                    throw new \Exception('Insufficient stock for product: ' . ($productCategory instanceof ProductCategory ? $productCategory->name : 'Unknown'));
                }

                $itemTotalPrice = $unitPrice * $itemDTO->quantity;
                $subtotal += $itemTotalPrice;

                $itemArray = $itemDTO->toArray();
                $itemArray['unit_price'] = $unitPrice;
                $itemArray['total_price'] = $itemTotalPrice;

                $processedOrderItemsData[] = $itemArray;
            }

            $orderData = $orderDTO->toArray();
            if (isset($orderData['items'])) {
                unset($orderData['items']);
            }

            $orderData['total_amount'] = $subtotal;
            $orderData['subtotal'] = $subtotal;
            $orderData['status'] = OrderStatus::PENDING()->value;

            /** @var Order $order */
            $order = $this->repository->create($orderData);

            Event::dispatch(new OrderCreatedEvent($order, $processedOrderItemsData));

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