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
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DeliverOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $deliveryUser;
    private \App\Models\DeliveryPerson $deliveryPerson;
    private string $authToken;
    private DistributionCenter $distributionCenter;
    private BottleType $bottleType;
    private ProductCategory $productCategory;
    private Product $product;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'delivery_person', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'center_manager', 'guard_name' => 'web']);

        $country = \App\Models\Geography\Country::where('code', 'CM')->first();
        if (! $country) {
            $country = \App\Models\Geography\Country::create([
                'name' => 'Cameroun',
                'code' => 'CM',
                'phone_code' => '+237',
                'is_active' => true,
            ]);
        }

        $this->deliveryUser = User::factory()->create([
            'country_id' => $country->id,
        ]);
        $this->deliveryUser->assignRole('delivery_person');

        $this->deliveryPerson = \App\Models\DeliveryPerson::factory()->create([
            'user_id' => $this->deliveryUser->id,
        ]);

        $response = $this->postJson(route('api.login'), [
            'login' => $this->deliveryUser->phone_number,
            'password' => 'password',
            'country_code' => 'CM',
        ]);
        $this->authToken = $response->json('data.access_token');

        $this->distributionCenter = DistributionCenter::factory()->create();
        $this->bottleType = BottleType::factory()->create();
        $this->productCategory = ProductCategory::factory()->create([
            'product_type' => ProductType::BOTTLE(),
            'product_type_id' => $this->bottleType->id,
        ]);
        $this->product = Product::factory()->create([
            'product_category_id' => $this->productCategory->id,
        ]);
        $this->customer = Customer::factory()->create();
    }

    #[Test]
    public function it_can_mark_an_order_as_delivered_and_update_bottle_status(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::PROCESSING(),
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'delivery_person_id' => $this->deliveryPerson->id,
        ]);

        $bottle = Bottle::factory()->create([
            'barcode' => 'BOTTLE123',
            'status' => BottleStatus::IN_STOCK(),
            'is_filled' => true,
            'distribution_center_id' => $this->distributionCenter->id,
            'product_id' => $this->product->id,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_category_id' => $this->productCategory->id,
            'quantity' => 1,
            'unit_price' => 1000,
            'total_price' => 1000,
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

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::DELIVERED()->value,
        ]);

        $this->assertDatabaseHas('bottles', [
            'id' => $bottle->id,
            'status' => BottleStatus::WITH_CLIENT()->value,
        ]);

        $this->assertDatabaseHas('bottle_movements', [
            'bottle_id' => $bottle->id,
            'type' => BottleMovementType::DELIVERY_TO_CUSTOMER()->value,
            'order_id' => $order->id,
            'customer_id' => $this->customer->id,
        ]);

    }

    #[Test]
    public function it_returns_404_if_order_not_found(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->patch(route('api.orders.deliver', ['order' => 99999])); // Non-existent ID

        $response->assertStatus(404);
    }

    #[Test]
    public function it_cannot_deliver_an_order_that_cannot_be_delivered(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::CANCELLED(),
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'delivery_person_id' => $this->deliveryPerson->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->patch(route('api.orders.deliver', ['order' => $order->id]));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', false)
            ->assertJsonPath('_metadata.message', 'This order cannot be marked as delivered.');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::CANCELLED()->value,
        ]);
    }
}
