<?php

namespace Tests\Feature\Endpoints;

use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\Geography\City;
use App\Models\Geography\Country;
use App\Models\Geography\Municipality;
use App\Models\Geography\Neighborhood;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCustomerDeliveryAddressTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;
    private Country $country;
    private City $city;
    private Municipality $municipality;
    private Neighborhood $neighborhood;

    protected function setUp(): void
    {
        parent::setUp();

        // Create geographic data
        $this->country = Country::factory()->create([
            'name' => 'Cameroun',
            'code' => 'CM',
        ]);

        $this->city = City::factory()->create([
            'name' => 'Yaoundé',
            'country_id' => $this->country->id,
        ]);

        $this->municipality = Municipality::factory()->create([
            'name' => 'Yaoundé I',
            'city_id' => $this->city->id,
        ]);

        $this->neighborhood = Neighborhood::factory()->create([
            'name' => 'Bali',
            'municipality_id' => $this->municipality->id,
        ]);

        // Create user and customer
        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_store_customer_delivery_address_with_required_fields(): void
    {
        $requestData = [
            'label' => 'Maison principale',
            'address' => '123 Avenue de la Liberté',
            'neighborhood_id' => $this->neighborhood->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/customers/{$this->customer->id}/delivery-addresses", $requestData);

        $response->assertStatus(201)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Adresse de livraison créée avec succès',
                ],
                'data' => [
                    'id' => 1,
                    'label' => 'Maison principale',
                    'address' => '123 Avenue de la Liberté',
                    'neighborhood' => [
                        'id' => $this->neighborhood->id,
                        'name' => 'Bali',
                        'municipality_id' => $this->municipality->id,
                    ],
                    'municipality' => [
                        'id' => $this->municipality->id,
                        'name' => 'Yaoundé I',
                        'city_id' => $this->city->id,
                    ],
                    'city' => [
                        'id' => $this->city->id,
                        'name' => 'Yaoundé',
                        'country_id' => $this->country->id,
                    ],
                    'country' => [
                        'id' => $this->country->id,
                        'name' => 'Cameroun',
                        'code' => 'CM',
                    ],
                    'is_default' => false,
                ],
            ]);

        $this->assertDatabaseHas('customer_delivery_addresses', [
            'customer_id' => $this->customer->id,
            'label' => 'Maison principale',
            'address' => '123 Avenue de la Liberté',
            'neighborhood_id' => $this->neighborhood->id,
        ]);
    }

    public function test_can_store_customer_delivery_address_with_all_fields(): void
    {
        $requestData = [
            'label' => 'Bureau secondaire',
            'address' => '456 Boulevard du 20 Mai',
            'neighborhood_id' => $this->neighborhood->id,
            'latitude' => 3.848,
            'longitude' => 11.502,
            'phone' => '699887766',
            'phone_country_code' => '+237',
            'contact_firstname' => 'Marie',
            'contact_lastname' => 'Curie',
            'email' => 'marie.curie@example.com',
            'address_precision' => 'Bâtiment C, 3ème étage, porte 302',
            'is_default' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/customers/{$this->customer->id}/delivery-addresses", $requestData);

        $response->assertStatus(201)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                ],
                'data' => [
                    'label' => 'Bureau secondaire',
                    'address' => '456 Boulevard du 20 Mai',
                    'latitude' => '3.84800000',
                    'longitude' => '11.50200000',
                    'phone' => '699887766',
                    'phone_country_code' => '+237',
                    'contact_firstname' => 'Marie',
                    'contact_lastname' => 'Curie',
                    'contact_full_name' => 'Marie Curie',
                    'email' => 'marie.curie@example.com',
                    'address_precision' => 'Bâtiment C, 3ème étage, porte 302',
                    'is_default' => true,
                ],
            ]);
    }

    public function test_setting_default_address_unsets_other_default_addresses(): void
    {
        // Create an existing default address
        CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
            'neighborhood_id' => $this->neighborhood->id,
            'is_default' => true,
        ]);

        $requestData = [
            'label' => 'Nouvelle adresse par défaut',
            'address' => '789 Rue de la Paix',
            'neighborhood_id' => $this->neighborhood->id,
            'is_default' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/customers/{$this->customer->id}/delivery-addresses", $requestData);

        $response->assertStatus(201);

        // Verify only the new address is default
        $this->assertEquals(1, CustomerDeliveryAddress::where('customer_id', $this->customer->id)
            ->where('is_default', true)
            ->count());

        $this->assertDatabaseHas('customer_delivery_addresses', [
            'customer_id' => $this->customer->id,
            'label' => 'Nouvelle adresse par défaut',
            'is_default' => true,
        ]);
    }

    public function test_requires_authentication(): void
    {
        $requestData = [
            'label' => 'Test',
            'address' => 'Test Address',
            'neighborhood_id' => $this->neighborhood->id,
        ];

        $response = $this->postJson("/api/customers/{$this->customer->id}/delivery-addresses", $requestData);

        $response->assertStatus(401);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/customers/{$this->customer->id}/delivery-addresses", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['label', 'address', 'neighborhood_id']);
    }

    public function test_validates_neighborhood_exists(): void
    {
        $requestData = [
            'label' => 'Test',
            'address' => 'Test Address',
            'neighborhood_id' => 99999, // Non-existing neighborhood
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/customers/{$this->customer->id}/delivery-addresses", $requestData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['neighborhood_id']);
    }

    public function test_validates_email_format(): void
    {
        $requestData = [
            'label' => 'Test',
            'address' => 'Test Address',
            'neighborhood_id' => $this->neighborhood->id,
            'email' => 'invalid-email',
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/customers/{$this->customer->id}/delivery-addresses", $requestData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_validates_numeric_coordinates(): void
    {
        $requestData = [
            'label' => 'Test',
            'address' => 'Test Address',
            'neighborhood_id' => $this->neighborhood->id,
            'latitude' => 'not-a-number',
            'longitude' => 'not-a-number',
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/customers/{$this->customer->id}/delivery-addresses", $requestData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    public function test_response_includes_geographic_data(): void
    {
        $requestData = [
            'label' => 'Test Address',
            'address' => '123 Test Street',
            'neighborhood_id' => $this->neighborhood->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/customers/{$this->customer->id}/delivery-addresses", $requestData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'neighborhood' => ['id', 'name', 'municipality_id'],
                    'municipality' => ['id', 'name', 'city_id'],
                    'city' => ['id', 'name', 'country_id'],
                    'country' => ['id', 'name', 'code'],
                ],
            ]);

        // Verify the geographic data matches the expected relationships
        $responseData = $response->json('data');
        $this->assertEquals($this->neighborhood->id, $responseData['neighborhood']['id']);
        $this->assertEquals($this->municipality->id, $responseData['municipality']['id']);
        $this->assertEquals($this->city->id, $responseData['city']['id']);
        $this->assertEquals($this->country->id, $responseData['country']['id']);
    }
}
