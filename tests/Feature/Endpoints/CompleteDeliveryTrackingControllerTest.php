<?php

namespace Tests\Feature\Endpoints;

use App\Enums\DeliveryTrackingStatus;
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

class CompleteDeliveryTrackingControllerTest extends TestCase
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

        // Create delivery person record
        $deliveryPersonRecord = \App\Models\DeliveryPerson::factory()->create([
            'user_id' => $this->deliveryPerson->id,
        ]);

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
            'delivery_person_id' => $deliveryPersonRecord->id,
        ]);
    }

    public function test_complete_delivery_tracking_requires_authentication(): void
    {
        $response = $this->patchJson("/api/tracking/delivery/{$this->order->id}/complete", [
            'final_latitude' => 48.8566,
            'final_longitude' => 2.3522,
        ]);

        $response->assertUnauthorized();
    }

    public function test_complete_delivery_tracking_marks_as_delivered(): void
    {
        Sanctum::actingAs($this->deliveryPerson);

        // Create in-progress tracking
        $tracking = DeliveryTracking::factory()->inProgress()->create([
            'order_id' => $this->order->id,
        ]);

        $response = $this->patchJson("/api/tracking/delivery/{$this->order->id}/complete", [
            'final_latitude' => 48.8566,
            'final_longitude' => 2.3522,
            'notes' => 'Package delivered successfully',
        ]);

        // Should mark as delivered
        $this->assertDatabaseHas('delivery_trackings', [
            'id' => $tracking->id,
            'status' => DeliveryTrackingStatus::DELIVERED()->value,
        ]);
    }

    public function test_complete_delivery_tracking_accepts_optional_coordinates(): void
    {
        Sanctum::actingAs($this->deliveryPerson);

        // Create in-progress tracking
        $tracking = DeliveryTracking::factory()->inProgress()->create([
            'order_id' => $this->order->id,
        ]);

        $response = $this->patchJson("/api/tracking/delivery/{$this->order->id}/complete", [
            'notes' => 'Package delivered successfully without final coordinates',
        ]);

        // Should still mark as delivered even without coordinates
        $this->assertDatabaseHas('delivery_trackings', [
            'id' => $tracking->id,
            'status' => DeliveryTrackingStatus::DELIVERED()->value,
        ]);
    }

    public function test_complete_delivery_tracking_returns_404_for_nonexistent_order(): void
    {
        Sanctum::actingAs($this->deliveryPerson);

        $response = $this->patchJson('/api/tracking/delivery/99999/complete', [
            'notes' => 'Test completion',
        ]);

        $response->assertNotFound();
    }

    public function test_complete_delivery_tracking_fails_for_unauthorized_delivery_person(): void
    {
        Sanctum::actingAs($this->deliveryPerson);

        // Create in-progress tracking
        $tracking = DeliveryTracking::factory()->inProgress()->create([
            'order_id' => $this->order->id,
        ]);

        // Create a different delivery person
        $unauthorizedDeliveryPerson = User::factory()->create();
        $unauthorizedDeliveryPerson->assignRole(UserRole::DELIVERY_PERSON()->value);

        // Create delivery person record for unauthorized user
        \App\Models\DeliveryPerson::factory()->create([
            'user_id' => $unauthorizedDeliveryPerson->id,
        ]);

        Sanctum::actingAs($unauthorizedDeliveryPerson);

        $response = $this->patchJson("/api/tracking/delivery/{$this->order->id}/complete", [
            'notes' => 'Package delivered successfully',
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'message' => 'You are not authorized to access this delivery',
            ]);
    }

    public function test_complete_delivery_tracking_succeeds_for_customer(): void
    {
        Sanctum::actingAs($this->deliveryPerson);

        // Create in-progress tracking
        $tracking = DeliveryTracking::factory()->inProgress()->create([
            'order_id' => $this->order->id,
        ]);

        Sanctum::actingAs($this->customerUser);

        $response = $this->patchJson("/api/tracking/delivery/{$this->order->id}/complete", [
            'notes' => 'Package delivered successfully',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                '_metadata' => [
                    'message' => 'Delivery completed successfully.',
                ],
            ]);
    }

    public function test_complete_delivery_tracking_fails_for_unauthorized_user(): void
    {
        Sanctum::actingAs($this->deliveryPerson);

        // Create in-progress tracking
        $tracking = DeliveryTracking::factory()->inProgress()->create([
            'order_id' => $this->order->id,
        ]);

        // Create a different user who is not related to this order
        $unauthorizedUser = User::factory()->create();
        $unauthorizedUser->assignRole(UserRole::CUSTOMER()->value);

        Sanctum::actingAs($unauthorizedUser);

        $response = $this->patchJson("/api/tracking/delivery/{$this->order->id}/complete", [
            'notes' => 'Package delivered successfully',
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'message' => 'You are not authorized to access this delivery',
            ]);
    }
}
