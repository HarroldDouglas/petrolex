<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\Customer;
use App\Models\Geography\Neighborhood;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CustomerAddressTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private string $authToken;
    private Neighborhood $neighborhood;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role for testing
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Create a neighborhood for the address
        $this->neighborhood = Neighborhood::factory()->create();

        // Create an admin user and authenticate to get a token
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $response = $this->postJson(route('api.login'), [
            'login' => $this->adminUser->email,
            'password' => 'password', // Default password from factory
        ]);
        $this->authToken = $response->json('data.access_token');
    }

    /** @test */
    public function it_can_create_a_delivery_address_for_a_customer(): void
    {
        $customer = Customer::factory()->create();

        $addressData = [
            'label' => 'Home Address',
            'address' => '123 Main St',
            'neighborhood_id' => $this->neighborhood->id,
            'phone' => '+1234567890',
            'contact_firstname' => 'John',
            'contact_lastname' => 'Doe',
            'email' => 'john.doe@example.com',
            'address_precision' => 'Near the park',
            'is_default' => true,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.customers.delivery-addresses.store', ['customer' => $customer->id]), $addressData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => [
                    'id',
                    'label',
                    'address',
                    'neighborhood' => [
                        'id',
                        'name',
                    ],
                    'city' => [
                        'id',
                        'name',
                    ],
                    'country' => [
                        'id',
                        'name',
                    ],
                    'latitude',
                    'longitude',
                    'phone',
                    'contact_firstname',
                    'contact_lastname',
                    'email',
                    'address_precision',
                    'is_default',
                ],
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.label', $addressData['label']);

        $this->assertDatabaseHas('customer_delivery_addresses', [
            'customer_id' => $customer->id,
            'label' => $addressData['label'],
            'address' => $addressData['address'],
            'neighborhood_id' => $addressData['neighborhood_id'],
        ]);
    }
}
