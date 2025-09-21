<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CancelOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $customerUser;
    private Customer $customer;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create required roles for the system
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'center_manager', 'guard_name' => 'web']);

        // Create country for authentication
        $country = \App\Models\Geography\Country::factory()->create([
            'code' => 'CM',
            'phone_code' => '+237',
        ]);

        // Create a customer user who will own the order
        $this->customerUser = User::factory()->create([
            'country_id' => $country->id,
        ]);

        $this->customer = Customer::factory()->create([
            'user_id' => $this->customerUser->id,
        ]);

        // Ensure a DistributionCenter exists for OrderFactory
        DistributionCenter::factory()->create();

        // Create an order that belongs to this customer
        $this->order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'pending', // Ensure order can be cancelled
        ]);
    }

    #[Test]
    public function it_can_cancel_an_order(): void
    {
        $cancellationData = [
            'cancelled_reason' => 'Customer requested cancellation.',
        ];

        $response = $this->actingAs($this->customerUser, 'sanctum')
            ->patchJson(route('api.orders.cancel', ['order' => $this->order->id]), $cancellationData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => [],
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('_metadata.message', 'Order cancelled successfully.');

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => OrderStatus::CANCELLED(),
            'cancelled_reason' => $cancellationData['cancelled_reason'],
            'cancelled_by' => $this->customerUser->id,
        ]);

        $updatedOrder = Order::find($this->order->id);
        $this->assertNotNull($updatedOrder->cancelled_at);
    }

    #[Test]
    public function it_can_cancel_an_order_without_reason(): void
    {
        // Create a new order for this test
        $secondOrder = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        // Since cancelled_reason is nullable, this should work and use the default reason
        $response = $this->actingAs($this->customerUser, 'sanctum')
            ->patchJson(route('api.orders.cancel', ['order' => $secondOrder->id]), []);

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => [],
            ]);

        // Check that the default reason was used
        $this->assertDatabaseHas('orders', [
            'id' => $secondOrder->id,
            'status' => OrderStatus::CANCELLED(),
            'cancelled_reason' => 'Annulée par le client', // Default reason from controller
            'cancelled_by' => $this->customerUser->id,
        ]);
    }
}
