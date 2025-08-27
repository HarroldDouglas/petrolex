<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\Customer;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderCustomerFeedbackTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private string $authToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role for testing
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        // Create center_manager role for testing
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'center_manager', 'guard_name' => 'web']);

        // Ensure a Customer and DistributionCenter exist for OrderFactory
        Customer::factory()->create();
        DistributionCenter::factory()->create();

        // Create an admin user and authenticate to get a token
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
        ]);
        $this->adminUser->assignRole('admin');

        $response = $this->postJson(route('api.login'), [
            'login' => $this->adminUser->email,
            'password' => 'password',
        ]);

        // Assert that the login was successful and token is present
        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.access_token'));

        $this->authToken = $response->json('data.access_token');
    }

    /** @test */
    public function it_can_add_customer_feedback_to_order(): void
    {
        $order = Order::factory()->create();
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
                'data' => [],
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('_metadata.message', 'Commentaire ajouté à la commande avec succès');

        // Assert that the order in the database has been updated
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'comments' => $payload['comments'],
            'rating' => $payload['rating'],
        ]);
    }

    /** @test */
    public function it_returns_422_if_validation_fails(): void
    {
        $order = Order::factory()->create();

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

    /** @test */
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

    /** @test */
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
