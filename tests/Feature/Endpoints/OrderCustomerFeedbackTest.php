<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\Customer;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class OrderCustomerFeedbackTest extends TestCase
{
    use RefreshDatabase;

    private User $customerUser;
    private Customer $customer;
    private string $authToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles for testing
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'delivery_person', 'guard_name' => 'web']);

        // Ensure a DistributionCenter exists for OrderFactory
        DistributionCenter::factory()->create();

        // Create a customer user and authenticate to get a token
        $this->customerUser = User::factory()->create([
            'email' => 'customer@test.com',
        ]);
        $this->customerUser->assignRole('customer');

        // Create customer
        $this->customer = Customer::factory()->create([
            'user_id' => $this->customerUser->id,
        ]);

        $response = $this->postJson(route('api.login'), [
            'login' => $this->customerUser->email,
            'password' => 'password',
        ]);

        // Assert that the login was successful and token is present
        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.access_token'));

        $this->authToken = $response->json('data.access_token');
    }

    #[Test]
    public function it_can_add_customer_feedback_to_order(): void
    {

        $distributionCenter = DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $order = Order::factory()->delivered()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        $payload = [
            'comments' => 'This is a test comment for the order.',
            'rating' => 4.5,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.customer-feedback', ['order' => $order->id]), $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => [
                    'success',
                    'message',
                ],
                'data' => [
                    'id',
                    'order_number',
                    'status',
                    'status_label',
                    'delivery_type',
                    'delivery_type_label',
                    'subtotal',
                    'delivery_fee',
                    'total_amount',
                    'comments',
                    'rating',
                    'created_at',
                    'updated_at',
                    'customer',
                    'delivery_address',
                    'items',
                    'payment',
                ],
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('_metadata.message', 'Commentaire ajouté à la commande avec succès')
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.comments', $payload['comments'])
            ->assertJsonPath('data.rating', $payload['rating'])
            ->assertJsonPath('data.status', $order->status->value)
            ->assertJsonPath('data.delivery_type', $order->delivery_type->value);

        // Assert that the order in the database has been updated
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'comments' => $payload['comments'],
            'rating' => $payload['rating'],
        ]);
    }

    #[Test]
    public function it_returns_422_if_validation_fails(): void
    {
        $distributionCenter = DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $order = Order::factory()->delivered()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        $payload = [
            'comments' => 'Too short',
            'rating' => 6.0, // Invalid rating
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.customer-feedback', ['order' => $order->id]), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comments', 'rating']);
    }

    #[Test]
    public function it_returns_404_if_order_not_found(): void
    {
        $nonExistentOrderId = 99999;

        $payload = [
            'comments' => 'This is a test comment.',
            'rating' => 4.0,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.customer-feedback', ['order' => $nonExistentOrderId]), $payload);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_returns_401_if_unauthenticated(): void
    {
        $order = Order::factory()->create();

        $payload = [
            'comments' => 'This is a test comment.',
            'rating' => 4.0,
        ];

        $response = $this->postJson(route('api.orders.customer-feedback', ['order' => $order->id]), $payload);

        $response->assertStatus(401);
    }
}
