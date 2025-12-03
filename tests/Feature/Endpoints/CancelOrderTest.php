<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Enums\OrderStatus;
use App\Listeners\BaseListener;
use App\Models\Customer;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;
use App\Services\Order\OrderService;
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

        // Clear processed events to avoid interference between tests
        BaseListener::clearProcessedEvents();

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
            ->assertJsonPath('_metadata.message', __('api.order_cancelled_success'));

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

    #[Test]
    public function it_can_cancel_a_paid_order(): void
    {
        // Create a paid order
        $paidOrder = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => OrderStatus::PAID()->value,
            'total_amount' => 10000,
            'paid_at' => now(),
        ]);

        // Create payment record
        OrderPayment::factory()->create([
            'order_id' => $paidOrder->id,
            'payment_status' => 'paid',
            'amount_paid' => 10000,
        ]);

        $response = $this->actingAs($this->customerUser, 'sanctum')
            ->patchJson(route('api.orders.cancel', ['order' => $paidOrder->id]), [
                'cancelled_reason' => 'Changed my mind',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true);

        $this->assertDatabaseHas('orders', [
            'id' => $paidOrder->id,
            'status' => OrderStatus::CANCELLED()->value,
        ]);
    }

    #[Test]
    public function it_cannot_cancel_a_processing_order(): void
    {
        // Create an order in processing status
        $processingOrder = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => OrderStatus::PROCESSING()->value,
            'processing_at' => now(),
        ]);

        $response = $this->actingAs($this->customerUser, 'sanctum')
            ->patchJson(route('api.orders.cancel', ['order' => $processingOrder->id]), [
                'cancelled_reason' => 'Want to cancel',
            ]);

        $response->assertStatus(422);

        // Order should still be in processing status
        $this->assertDatabaseHas('orders', [
            'id' => $processingOrder->id,
            'status' => OrderStatus::PROCESSING()->value,
        ]);
    }

    #[Test]
    public function it_cannot_cancel_a_delivered_order(): void
    {
        // Create a delivered order
        $deliveredOrder = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => OrderStatus::DELIVERED()->value,
            'delivered_at' => now(),
        ]);

        $response = $this->actingAs($this->customerUser, 'sanctum')
            ->patchJson(route('api.orders.cancel', ['order' => $deliveredOrder->id]), [
                'cancelled_reason' => 'Want to cancel',
            ]);

        $response->assertStatus(422);

        // Order should still be in delivered status
        $this->assertDatabaseHas('orders', [
            'id' => $deliveredOrder->id,
            'status' => OrderStatus::DELIVERED()->value,
        ]);
    }

    #[Test]
    public function it_cannot_cancel_another_customers_order(): void
    {
        // Create another customer
        $anotherUser = User::factory()->create([
            'country_id' => $this->customerUser->country_id,
        ]);
        $anotherCustomer = Customer::factory()->create([
            'user_id' => $anotherUser->id,
        ]);

        // Create an order for the other customer
        $otherOrder = Order::factory()->create([
            'customer_id' => $anotherCustomer->id,
            'status' => OrderStatus::PENDING()->value,
        ]);

        // Try to cancel the other customer's order
        $response = $this->actingAs($this->customerUser, 'sanctum')
            ->patchJson(route('api.orders.cancel', ['order' => $otherOrder->id]), [
                'cancelled_reason' => 'Trying to cancel',
            ]);

        $response->assertStatus(403);

        // Order should still be pending
        $this->assertDatabaseHas('orders', [
            'id' => $otherOrder->id,
            'status' => OrderStatus::PENDING()->value,
        ]);
    }

    #[Test]
    public function it_refunds_wallet_when_paid_order_is_cancelled(): void
    {
        // Set initial customer balance
        $initialBalance = 5000.00;
        $this->customer->update(['current_balance' => $initialBalance]);

        $orderAmount = 10000.00;

        // Create a paid order using OrderService to trigger proper events
        $paidOrder = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => OrderStatus::PENDING()->value,
            'total_amount' => $orderAmount,
            'subtotal' => $orderAmount - 500,
            'delivery_fee' => 500,
        ]);

        // Create payment record
        OrderPayment::factory()->create([
            'order_id' => $paidOrder->id,
            'payment_status' => 'paid',
            'amount_paid' => $orderAmount,
            'amount_due' => $orderAmount,
        ]);

        // Use OrderService to change status to PAID (triggers events properly)
        $orderService = app(OrderService::class);
        $orderService->update($paidOrder, [
            'status' => OrderStatus::PAID()->value,
            'paid_at' => now(),
        ]);

        // Refresh to get updated total_amount
        $paidOrder->refresh();
        $actualOrderAmount = (float) $paidOrder->total_amount;

        // Cancel the order via API
        $response = $this->actingAs($this->customerUser, 'sanctum')
            ->patchJson(route('api.orders.cancel', ['order' => $paidOrder->id]), [
                'cancelled_reason' => 'Refund test',
            ]);

        $response->assertStatus(200);

        // Verify order is cancelled
        $this->assertDatabaseHas('orders', [
            'id' => $paidOrder->id,
            'status' => OrderStatus::CANCELLED()->value,
        ]);

        // Verify wallet was credited (initial + refund amount)
        $this->customer->refresh();
        $expectedBalance = $initialBalance + $actualOrderAmount;
        $this->assertEquals($expectedBalance, (float) $this->customer->current_balance);

        // Verify wallet transaction was created
        $this->assertDatabaseHas('wallet_transactions', [
            'customer_id' => $this->customer->id,
            'order_id' => $paidOrder->id,
            'type' => 'credit',
        ]);
    }
}
