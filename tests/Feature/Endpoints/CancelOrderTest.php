<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CancelOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private string $authToken;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role for testing
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        // Create center_manager role for testing
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'center_manager', 'guard_name' => 'web']);

        // Create an admin user and authenticate to get a token
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        // Ensure a Customer and DistributionCenter exist for OrderFactory
        Customer::factory()->create();
        DistributionCenter::factory()->create();

        $this->order = Order::factory()->create();

        $response = $this->postJson(route('api.login'), [
            'login' => $this->adminUser->email,
            'password' => 'password', // Default password from factory
        ]);
        $this->authToken = $response->json('data.access_token');
    }

    /** @test */
    public function it_can_cancel_an_order(): void
    {

        $cancellationData = [
            'cancelled_reason' => 'Customer requested cancellation.',
            'cancelled_by' => $this->adminUser->id,
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')
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
            'cancelled_by' => $this->adminUser->id,
        ]);

        $updatedOrder = Order::find($this->order->id);
        $this->assertNotNull($updatedOrder->cancelled_at);
    }

    /** @test */
    public function it_returns_an_error_if_cancellation_reason_is_missing(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->patchJson(route('api.orders.cancel', ['order' => $this->order->id]), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cancelled_reason']);
    }
}
