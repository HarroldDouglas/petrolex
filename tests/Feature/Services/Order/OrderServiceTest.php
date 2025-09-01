<?php

namespace Tests\Feature\Services\Order;

use App\Enums\BottleOrderType;
use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ProductType;
use App\Enums\UserRole;
use App\Events\OrderCreatedEvent;
use App\Models\AccessoryType;
use App\Models\BottleType;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductCategory;
use App\Services\Order\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;
    private Customer $customer;
    private DistributionCenter $distributionCenter;
    private CustomerDeliveryAddress $deliveryAddress;

    protected function setUp(): void
    {
        parent::setUp();

        // Create all necessary roles
        foreach (UserRole::cases() as $role) {
            Role::create(['name' => $role->value]);
        }

        $this->orderService = $this->app->make(OrderService::class);

        $this->customer = Customer::factory()->create();
        $this->distributionCenter = DistributionCenter::factory()->create();
        $this->deliveryAddress = CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);
    }

    private function mockOrderServiceWithOrderNumber(): OrderService
    {
        // Mock the repository to add order_number before creation
        $mockRepository = $this->getMockBuilder(\App\Repositories\Contracts\OrderRepositoryInterface::class)
            ->getMock();

        $mockRepository->method('create')
            ->willReturnCallback(function ($data) {
                $data['order_number'] = 'TEST-'.time().'-'.rand(1000, 9999);

                return Order::create($data);
            });

        $this->app->instance(\App\Repositories\Contracts\OrderRepositoryInterface::class, $mockRepository);

        return $this->app->make(OrderService::class);
    }

    private function createBottleProductCategory(array $bottleOverrides = []): ProductCategory
    {
        $bottleType = BottleType::factory()->create(array_merge([
            'bottle_with_content_price' => 25.00,
            'content_price' => 15.00,
            'is_active' => true,
        ], $bottleOverrides));

        return ProductCategory::factory()->create([
            'product_type' => ProductType::BOTTLE(),
            'product_type_id' => $bottleType->id,
        ]);
    }

    private function createAccessoryProductCategory(array $accessoryOverrides = []): ProductCategory
    {
        $accessoryType = AccessoryType::factory()->create(array_merge([
            'price' => 10.00,
            'is_active' => true,
        ], $accessoryOverrides));

        return ProductCategory::factory()->create([
            'product_type' => ProductType::ACCESSORY(),
            'product_type_id' => $accessoryType->id,
        ]);
    }

    public function test_it_can_create_an_order_with_bottle_items(): void
    {
        Event::fake();

        $bottleCategory = $this->createBottleProductCategory();

        $orderData = [
            'customer_id' => $this->customer->id,
            'delivery_address_id' => $this->deliveryAddress->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL(),
            'payment_method' => PaymentMethod::CREDIT_CARD(),
            'items' => [
                [
                    'product_category_id' => $bottleCategory->id,
                    'quantity' => 2,
                    'option' => BottleOrderType::FULL()->value,
                ],
            ],
        ];

        $orderService = $this->mockOrderServiceWithOrderNumber();
        $createdOrder = $orderService->create($orderData);

        $this->assertInstanceOf(Order::class, $createdOrder);
        $this->assertEquals($this->customer->id, $createdOrder->customer_id);
        $this->assertEquals(OrderStatus::PENDING()->value, $createdOrder->status);
        $this->assertEquals(50.00, $createdOrder->subtotal); // 2 * 25.00
        $this->assertEquals(DeliveryType::NORMAL()->fee(), $createdOrder->delivery_fee);
        $this->assertEquals(50.00 + DeliveryType::NORMAL()->fee(), $createdOrder->total_amount);

        Event::assertDispatched(OrderCreatedEvent::class);
    }

    public function test_it_can_create_an_order_with_accessory_items(): void
    {
        Event::fake();

        $accessoryCategory = $this->createAccessoryProductCategory(['price' => 12.00]);

        $orderData = [
            'customer_id' => $this->customer->id,
            'delivery_address_id' => $this->deliveryAddress->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'delivery_type' => DeliveryType::FAST(),
            'payment_method' => PaymentMethod::MTN_MONEY(),
            'items' => [
                [
                    'product_category_id' => $accessoryCategory->id,
                    'quantity' => 3,
                ],
            ],
        ];

        $orderService = $this->mockOrderServiceWithOrderNumber();
        $createdOrder = $orderService->create($orderData);

        $this->assertInstanceOf(Order::class, $createdOrder);
        $this->assertEquals(36.00, $createdOrder->subtotal); // 3 * 12.00
        $this->assertEquals(DeliveryType::FAST()->fee(), $createdOrder->delivery_fee);
        $this->assertEquals(36.00 + DeliveryType::FAST()->fee(), $createdOrder->total_amount);

        Event::assertDispatched(OrderCreatedEvent::class);
    }

    public function test_it_can_create_an_order_with_mixed_items(): void
    {
        // Fake only the events we don't want to test, but allow AddOrderItemsToOrderListener to run
        Event::fake([
            \App\Events\OrderStatusChanged::class,
        ]);

        $bottleCategory = $this->createBottleProductCategory([
            'bottle_with_content_price' => 30.00,
            'content_price' => 20.00,
        ]);
        $accessoryCategory = $this->createAccessoryProductCategory(['price' => 8.00]);

        $orderData = [
            'customer_id' => $this->customer->id,
            'delivery_address_id' => $this->deliveryAddress->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL(),
            'payment_method' => PaymentMethod::CREDIT_CARD(),
            'items' => [
                [
                    'product_category_id' => $bottleCategory->id,
                    'quantity' => 1,
                    'option' => BottleOrderType::RECHARGE()->value,
                ],
                [
                    'product_category_id' => $accessoryCategory->id,
                    'quantity' => 2,
                ],
            ],
        ];

        $orderService = $this->mockOrderServiceWithOrderNumber();
        $createdOrder = $orderService->create($orderData);

        $this->assertInstanceOf(Order::class, $createdOrder);
        $this->assertEquals(36.00, $createdOrder->subtotal); // (1 * 20.00) + (2 * 8.00)
        $this->assertCount(2, $createdOrder->items);
    }

    public function test_it_can_find_an_order(): void
    {
        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'order_number' => 'TEST-001',
        ]);

        $foundOrder = $this->orderService->find($order->id);

        $this->assertInstanceOf(Order::class, $foundOrder);
        $this->assertEquals($order->id, $foundOrder->id);
    }

    public function test_it_can_update_an_order(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::PENDING()->value,
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'order_number' => 'TEST-002',
        ]);
        $updateData = ['status' => OrderStatus::CONFIRMED()->value];

        $updatedOrder = $this->orderService->update($order, $updateData);

        $this->assertEquals(OrderStatus::CONFIRMED()->value, $updatedOrder->status);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::CONFIRMED()->value,
        ]);
    }

    public function test_it_can_delete_an_order(): void
    {
        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'order_number' => 'TEST-003',
        ]);

        $result = $this->orderService->delete($order);

        $this->assertTrue($result);
        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }

    public function test_it_can_get_all_orders(): void
    {
        Order::factory()->count(3)->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $this->distributionCenter->id,
        ]);

        $orders = $this->orderService->getAll();

        $this->assertCount(3, $orders);
    }

    public function test_it_can_paginate_orders(): void
    {
        Order::factory()->count(20)->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $this->distributionCenter->id,
        ]);

        $paginatedOrders = $this->orderService->paginate(15);

        $this->assertCount(15, $paginatedOrders);
    }

    public function test_it_can_get_order_with_grouped_items(): void
    {
        $bottleCategory = $this->createBottleProductCategory();
        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'order_number' => 'TEST-004',
        ]);

        // Create multiple order items manually
        OrderItem::create([
            'order_id' => $order->id,
            'product_category_id' => $bottleCategory->id,
            'quantity' => 2,
            'unit_price' => 25.00,
            'total_price' => 50.00,
            'option' => BottleOrderType::FULL()->value,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_category_id' => $bottleCategory->id,
            'quantity' => 1,
            'unit_price' => 15.00,
            'total_price' => 15.00,
            'option' => BottleOrderType::RECHARGE()->value,
        ]);

        $orderDetails = $this->orderService->getOrderWithGroupedItems($order->id);

        $this->assertNotNull($orderDetails);
        $this->assertEquals($order->id, $orderDetails->order->id);
        $this->assertCount(2, $orderDetails->groupedItems); // Should be grouped by option
    }

    public function test_it_returns_null_for_non_existent_order_details(): void
    {
        $orderDetails = $this->orderService->getOrderWithGroupedItems(999);

        $this->assertNull($orderDetails);
    }

    public function test_it_can_group_order_items_by_type(): void
    {
        $bottleCategory = $this->createBottleProductCategory();
        $accessoryCategory = $this->createAccessoryProductCategory();
        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'order_number' => 'TEST-005',
        ]);

        $bottleItem = OrderItem::create([
            'order_id' => $order->id,
            'product_category_id' => $bottleCategory->id,
            'quantity' => 2,
            'unit_price' => 25.00,
            'total_price' => 50.00,
            'option' => BottleOrderType::FULL()->value,
        ]);

        $accessoryItem = OrderItem::create([
            'order_id' => $order->id,
            'product_category_id' => $accessoryCategory->id,
            'quantity' => 3,
            'unit_price' => 10.00,
            'total_price' => 30.00,
        ]);

        $items = collect([$bottleItem, $accessoryItem]);
        $groupedItems = $this->orderService->groupOrderItems($items);

        $this->assertCount(2, $groupedItems);

        $bottleGroup = $groupedItems->first(function ($group) use ($bottleCategory) {
            return $group->orderItem->product_category_id === $bottleCategory->id;
        });

        $accessoryGroup = $groupedItems->first(function ($group) use ($accessoryCategory) {
            return $group->orderItem->product_category_id === $accessoryCategory->id;
        });

        $this->assertNotNull($bottleGroup);
        $this->assertNotNull($accessoryGroup);
        $this->assertEquals(2, $bottleGroup->groupedQuantity);
        $this->assertEquals(3, $accessoryGroup->groupedQuantity);
        $this->assertEquals(50.00, $bottleGroup->groupedTotalPrice);
        $this->assertEquals(30.00, $accessoryGroup->groupedTotalPrice);
    }

    public function test_it_calculates_correct_prices_for_different_bottle_options(): void
    {
        Event::fake();

        $bottleCategory = $this->createBottleProductCategory([
            'bottle_with_content_price' => 40.00,
            'content_price' => 25.00,
        ]);

        $orderDataFull = [
            'customer_id' => $this->customer->id,
            'delivery_address_id' => $this->deliveryAddress->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL(),
            'payment_method' => PaymentMethod::CREDIT_CARD(),
            'items' => [
                [
                    'product_category_id' => $bottleCategory->id,
                    'quantity' => 1,
                    'option' => BottleOrderType::FULL()->value,
                ],
            ],
        ];

        $orderDataRecharge = [
            'customer_id' => $this->customer->id,
            'delivery_address_id' => $this->deliveryAddress->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL(),
            'payment_method' => PaymentMethod::CREDIT_CARD(),
            'items' => [
                [
                    'product_category_id' => $bottleCategory->id,
                    'quantity' => 1,
                    'option' => BottleOrderType::RECHARGE()->value,
                ],
            ],
        ];

        $orderService = $this->mockOrderServiceWithOrderNumber();
        $fullOrder = $orderService->create($orderDataFull);
        $rechargeOrder = $orderService->create($orderDataRecharge);

        $this->assertEquals(40.00, $fullOrder->subtotal);
        $this->assertEquals(25.00, $rechargeOrder->subtotal);
    }

    public function test_order_creation_loads_items_and_product_categories(): void
    {
        // Don't fake events for this test so OrderItems get created

        $bottleCategory = $this->createBottleProductCategory();

        $orderData = [
            'customer_id' => $this->customer->id,
            'delivery_address_id' => $this->deliveryAddress->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL(),
            'payment_method' => PaymentMethod::CREDIT_CARD(),
            'items' => [
                [
                    'product_category_id' => $bottleCategory->id,
                    'quantity' => 1,
                    'option' => BottleOrderType::FULL()->value,
                ],
            ],
        ];

        $orderService = $this->mockOrderServiceWithOrderNumber();
        $createdOrder = $orderService->create($orderData);

        $this->assertTrue($createdOrder->relationLoaded('items'));
        $this->assertGreaterThan(0, $createdOrder->items->count());
        $this->assertTrue($createdOrder->items->first()->relationLoaded('productCategory'));
    }
}
