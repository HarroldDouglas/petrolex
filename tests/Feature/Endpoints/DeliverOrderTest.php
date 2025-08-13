<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Enums\BottleMovementType;
use App\Enums\BottleStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Models\Bottle;
use App\Models\BottleType;
use App\Models\Customer;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class DeliverOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private string $authToken;
    private DistributionCenter $distributionCenter;
    private BottleType $bottleType;
    private ProductCategory $productCategory;
    private Product $product;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role for testing
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Create an admin user and authenticate to get a token
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $response = $this->postJson(route('api.login'), [
            'login' => $this->adminUser->email,
            'password' => 'password', // Default password from factory
        ]);
        $this->authToken = $response->json('data.access_token');

        // Setup necessary related models
        $this->distributionCenter = DistributionCenter::factory()->create();
        $this->bottleType = BottleType::factory()->create();
        $this->productCategory = ProductCategory::factory()->create([
            'product_type' => ProductType::BOTTLE(), // Assuming this is a bottle product category
            'product_type_id' => $this->bottleType->id,
        ]);
        $this->product = Product::factory()->create([
            'product_category_id' => $this->productCategory->id,
        ]);
        $this->customer = Customer::factory()->create();

        // Ensure events are faked to assert dispatching
        Event::fake();
    }

    /** @test */
    public function it_can_mark_an_order_as_delivered_and_update_bottle_status(): void
    {
        // Create an order with a bottle item
        $order = Order::factory()->create([
            'status' => OrderStatus::PENDING(), // Order must be in a deliverable state
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $this->distributionCenter->id,
        ]);

        $bottle = Bottle::factory()->create([
            'barcode' => 'BOTTLE123',
            'status' => BottleStatus::IN_STOCK(), // Bottle must be in a state to be delivered
            'is_filled' => true,
            'distribution_center_id' => $this->distributionCenter->id,
            'bottle_type_id' => $this->bottleType->id,
        ]);

        // Attach the bottle to the order item via order_bottle_scans
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_category_id' => $this->productCategory->id,
            'quantity' => 1,
        ]);
        $orderItem->bottles()->attach($bottle->id);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->patch(route('api.orders.deliver', ['order' => $order->id]));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.status', OrderStatus::DELIVERED()->value);

        // Assert order status is updated in database
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::DELIVERED()->value,
        ]);

        // Assert bottle status is updated in database
        $this->assertDatabaseHas('bottles', [
            'id' => $bottle->id,
            'status' => BottleStatus::WITH_CLIENT()->value,
        ]);

        // Assert bottle movement entry is created
        $this->assertDatabaseHas('bottle_movements', [
            'bottle_id' => $bottle->id,
            'type' => BottleMovementType::DELIVERY_TO_CLIENT()->value,
            'order_id' => $order->id,
            'customer_id' => $this->customer->id,
        ]);

        // Assert OrderDeliveredEvent was dispatched
        Event::assertDispatched(\App\Events\OrderDeliveredEvent::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });
    }

    /** @test */
    public function it_returns_404_if_order_not_found(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->patch(route('api.orders.deliver', ['order' => 99999])); // Non-existent ID

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_401_for_unauthenticated_access(): void
    {
        $order = Order::factory()->create(); // Create a dummy order
        $response = $this->patch(route('api.orders.deliver', ['order' => $order->id]));

        $response->assertStatus(401);
    }

    /** @test */
    public function it_cannot_deliver_an_order_that_cannot_be_delivered(): void
    {
        // Create an order in a status that cannot be delivered (e.g., CANCELLED)
        $order = Order::factory()->create([
            'status' => OrderStatus::CANCELLED(),
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $this->distributionCenter->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->patch(route('api.orders.deliver', ['order' => $order->id]));

        $response->assertStatus(422) // Assuming 422 for unprocessable entity
            ->assertJsonPath('_metadata.success', false)
            ->assertJsonPath('_metadata.message', 'This order cannot be marked as delivered.');

        // Assert order status is NOT updated in database
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::CANCELLED()->value,
        ]);
    }
}
