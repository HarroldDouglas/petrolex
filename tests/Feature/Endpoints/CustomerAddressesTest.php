<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\Geography\Neighborhood;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CustomerAddressesTest extends TestCase
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
            'password' => 'password',
        ]);
        $this->authToken = $response->json('data.access_token');
    }

    /** @test */
    public function it_can_retrieve_customer_details_with_addresses(): void
    {
        $customer = Customer::factory()->create();
        CustomerDeliveryAddress::factory()->count(3)->create([
            'customer_id' => $customer->id,
            'neighborhood_id' => $this->neighborhood->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.customers.show', ['customerId' => $customer->id]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone_number',
                    'deliveryAddresses' => [
                        '*' => [
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
                    ],
                    'current_balance',
                ],
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.id', $customer->id)
            ->assertJsonCount(3, 'data.deliveryAddresses');
    }
}
