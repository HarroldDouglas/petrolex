<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CustomerOrdersTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private string $authToken;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'center_manager', 'guard_name' => 'web']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $response = $this->postJson(route('api.login'), [
            'login' => $this->adminUser->email,
            'password' => 'password',
        ]);
        $this->authToken = $response->json('data.access_token');
    }

    #[Test]
    public function it_can_list_all_orders_of_a_customer(): void
    {
        $customer = Customer::factory()->create();

        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create();

        $orders = Order::factory()->count(3)->create([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.customers.orders.index', ['customer' => $customer->id]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message', 'pagination'],
                'data' => [
                    [
                        'id',
                        'order_number',
                        'status',
                        'total_amount',
                        // Add other order fields as needed
                    ],
                ],
            ])
            ->assertJsonPath('_metadata.success', true);

        // Assert that all created orders are present in the response
        foreach ($orders as $order) {
            $response->assertJsonFragment([
                'id' => $order->id,
                'order_number' => $order->order_number,
            ]);
        }
    }
}
