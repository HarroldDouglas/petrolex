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
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateCustomerDeliveryAddressTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;
    private CustomerDeliveryAddress $deliveryAddress;
    private Country $country;
    private City $city;
    private Municipality $municipality;
    private Neighborhood $neighborhood;

    protected function setUp(): void
    {
        parent::setUp();

        // Create geographic entities
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

        // Create delivery address
        $this->deliveryAddress = CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
            'neighborhood_id' => $this->neighborhood->id,
            'label' => 'Original Label',
            'address' => 'Original Address',
            'is_default' => false,
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_can_update_customer_delivery_address(): void
    {
        $requestData = [
            'label' => 'Updated Label',
            'address' => 'Updated Address',
            'neighborhood_id' => $this->neighborhood->id,
            'is_default' => true,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/my/delivery-addresses/{$this->deliveryAddress->id}", $requestData);

        $response->assertStatus(200)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Adresse de livraison mise à jour avec succès',
                ],
                'data' => [
                    'id' => $this->deliveryAddress->id,
                    'label' => 'Updated Label',
                    'address' => 'Updated Address',
                    'is_default' => true,
                ],
            ]);

        $this->assertDatabaseHas('customer_delivery_addresses', [
            'id' => $this->deliveryAddress->id,
            'customer_id' => $this->customer->id,
            'label' => 'Updated Label',
            'address' => 'Updated Address',
            'is_default' => true,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson("/api/my/delivery-addresses/{$this->deliveryAddress->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['label', 'address', 'neighborhood_id']);
    }

    public function test_returns_full_geographic_data_structure(): void
    {
        $requestData = [
            'label' => 'Updated Label',
            'address' => 'Updated Address',
            'neighborhood_id' => $this->neighborhood->id,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/my/delivery-addresses/{$this->deliveryAddress->id}", $requestData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'neighborhood' => ['id', 'name', 'municipality_id'],
                    'municipality' => ['id', 'name', 'city_id'],
                    'city' => ['id', 'name', 'country_id'],
                    'country' => ['id', 'name', 'code'],
                ],
            ]);
    }
}
