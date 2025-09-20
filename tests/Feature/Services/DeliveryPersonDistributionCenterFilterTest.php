<?php

namespace Tests\Feature\Services;

use App\Models\Customer;
use App\Models\DeliveryPerson;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\User;
use App\Services\DeliveryPersonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryPersonDistributionCenterFilterTest extends TestCase
{
    use RefreshDatabase;

    private DeliveryPersonService $deliveryPersonService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->deliveryPersonService = app(DeliveryPersonService::class);
    }

    public function test_finds_least_busy_delivery_person_from_specific_distribution_center(): void
    {
        // Create two distribution centers
        $distributionCenter1 = DistributionCenter::factory()->create();
        $distributionCenter2 = DistributionCenter::factory()->create();

        // Create delivery persons for each distribution center
        $deliveryPerson1 = $this->createDeliveryPersonForDistributionCenter($distributionCenter1);
        $deliveryPerson2 = $this->createDeliveryPersonForDistributionCenter($distributionCenter1);
        $deliveryPerson3 = $this->createDeliveryPersonForDistributionCenter($distributionCenter2);

        // Create customer for orders
        $customer = Customer::factory()->create();

        // Give deliveryPerson1 more orders (making them busier)
        Order::factory()->count(3)->create([
            'delivery_person_id' => $deliveryPerson1->id,
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter1->id,
            'status' => 'paid',
        ]);

        // Give deliveryPerson2 fewer orders (making them less busy)
        Order::factory()->count(1)->create([
            'delivery_person_id' => $deliveryPerson2->id,
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter1->id,
            'status' => 'paid',
        ]);

        // Give deliveryPerson3 no orders (making them least busy globally, but in different center)

        // When finding least busy for distribution center 1
        $leastBusyForCenter1 = $this->deliveryPersonService->findLeastBusyDeliveryPerson($distributionCenter1->id);

        // Should return deliveryPerson2 (least busy in center 1), not deliveryPerson3 (different center)
        $this->assertNotNull($leastBusyForCenter1);
        $this->assertEquals($deliveryPerson2->id, $leastBusyForCenter1->id);

        // When finding least busy for distribution center 2
        $leastBusyForCenter2 = $this->deliveryPersonService->findLeastBusyDeliveryPerson($distributionCenter2->id);

        // Should return deliveryPerson3
        $this->assertNotNull($leastBusyForCenter2);
        $this->assertEquals($deliveryPerson3->id, $leastBusyForCenter2->id);
    }

    public function test_returns_null_when_no_delivery_person_available_for_distribution_center(): void
    {
        $distributionCenter = DistributionCenter::factory()->create();

        // Create a delivery person for a different distribution center
        $otherDistributionCenter = DistributionCenter::factory()->create();
        $this->createDeliveryPersonForDistributionCenter($otherDistributionCenter);

        // Should return null when no delivery person is assigned to the requested center
        $result = $this->deliveryPersonService->findLeastBusyDeliveryPerson($distributionCenter->id);

        $this->assertNull($result);
    }

    private function createDeliveryPersonForDistributionCenter(DistributionCenter $distributionCenter): DeliveryPerson
    {
        $user = User::factory()->create([
            'email' => fake()->unique()->email(),
        ]);

        $deliveryPerson = DeliveryPerson::factory()->create([
            'user_id' => $user->id,
        ]);

        // Attach the delivery person to the distribution center as active
        $deliveryPerson->distributionCenters()->attach($distributionCenter->id, [
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $deliveryPerson;
    }
}
