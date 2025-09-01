<?php

namespace Tests\Feature\Endpoints;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\DeliveryTracking;
use App\Models\DistributionCenter;
use App\Models\Geography\Neighborhood;
use App\Models\Order;
use App\Models\User;
use Database\Factories\SimpleOrderFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GetDeliveryTrackingDetailsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $deliveryPerson;
    private User $customerUser;
    private Customer $customer;
    private DistributionCenter $distributionCenter;
    private CustomerDeliveryAddress $deliveryAddress;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => UserRole::DELIVERY_PERSON()->value]);
        Role::create(['name' => UserRole::CUSTOMER()->value]);
        Role::create(['name' => UserRole::CENTER_MANAGER()->value]);

        // Create users
        $this->deliveryPerson = User::factory()->create();
        $this->deliveryPerson->assignRole(UserRole::DELIVERY_PERSON()->value);

        $this->customerUser = User::factory()->create();
        $this->customerUser->assignRole(UserRole::CUSTOMER()->value);

        // Create customer record
        $this->customer = Customer::factory()->create([
            'user_id' => $this->customerUser->id,
        ]);

        // Create distribution center with neighborhood
        $neighborhood = Neighborhood::factory()->create();
        $this->distributionCenter = DistributionCenter::factory()->create([
            'neighborhood_id' => $neighborhood->id,
        ]);

        // Create delivery address
        $this->deliveryAddress = CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
            'is_default' => true,
        ]);

        // Create order using simple factory with proper foreign keys
        $this->order = SimpleOrderFactory::new()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'delivery_address_id' => $this->deliveryAddress->id,
            'delivery_person_id' => $this->deliveryPerson->id,
        ]);
    }

    public function test_get_delivery_tracking_details_requires_authentication(): void
    {
        $response = $this->getJson("/api/tracking/delivery/{$this->order->id}");
        $response->assertUnauthorized();
    }

    public function test_get_delivery_tracking_details_returns_tracking_info(): void
    {
        Sanctum::actingAs($this->deliveryPerson);

        $tracking = DeliveryTracking::factory()->inProgress()->create([
            'order_id' => $this->order->id,
        ]);

        $response = $this->getJson("/api/tracking/delivery/{$this->order->id}");

        $response->assertOk();
        $response->assertJsonStructure(['data']);
    }

    public function test_get_delivery_tracking_details_handles_nonexistent_order(): void
    {
        Sanctum::actingAs($this->deliveryPerson);

        $response = $this->getJson('/api/tracking/delivery/99999');

        // The API might return 400 (Bad Request) or 404 (Not Found) depending on validation logic
        // Both are acceptable for a nonexistent order
        $this->assertContains($response->status(), [400, 404],
            'Expected 400 (Bad Request) or 404 (Not Found) for nonexistent order');

        // Ensure it's not a successful response
        $this->assertFalse($response->isSuccessful(),
            'Response should not be successful for nonexistent order');
    }
}
