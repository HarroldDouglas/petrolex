<?php

namespace Tests\Feature\Services;

use App\Models\DistributionCenter;
use App\Models\Order;
use App\Services\Dashboard\DashboardGraphService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardGraphServiceTest extends TestCase
{
    use RefreshDatabase;

    private DashboardGraphService $dashboardGraphService;
    private DistributionCenter $distributionCenter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->dashboardGraphService = $this->app->make(DashboardGraphService::class);
        $this->distributionCenter = DistributionCenter::factory()->create();
    }

    /**
     * @test
     */
    public function it_can_get_revenue_by_day_for_a_single_distribution_center(): void
    {
        // Arrange
        $startDate = Carbon::now()->subDays(2)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        // Create orders for the distribution center
        $customer = \App\Models\Customer::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create(['customer_id' => $customer->id]);

        Order::factory()->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'subtotal' => 100.50,
            'delivery_fee' => 0,
            'order_date' => Carbon::parse($startDate)->addHours(10),
            'created_at' => Carbon::parse($startDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        Order::factory()->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'subtotal' => 200.00,
            'delivery_fee' => 0,
            'order_date' => Carbon::parse($startDate)->addDay()->addHours(10),
            'created_at' => Carbon::parse($startDate)->addDay()->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        Order::factory()->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'subtotal' => 50.00,
            'delivery_fee' => 0,
            'order_date' => Carbon::parse($endDate)->addHours(10),
            'created_at' => Carbon::parse($endDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Act
        $result = $this->dashboardGraphService->getRevenueByDay($startDate, $endDate, $this->distributionCenter->id);

        // Assert
        $this->assertIsObject($result);
        $this->assertObjectHasProperty('labels', $result);
        $this->assertObjectHasProperty('data', $result);

        $this->assertCount(3, $result->labels); // 3 days in the period
        $this->assertCount(3, $result->data);

        $this->assertEquals(100.50, $result->data[0]);
        $this->assertEquals(200.00, $result->data[1]);
        $this->assertEquals(50.00, $result->data[2]);
    }

    /**
     * @test
     */
    public function it_can_get_revenue_by_day_for_all_distribution_centers(): void
    {
        // Arrange
        $startDate = Carbon::now()->subDays(2)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $distributionCenter2 = DistributionCenter::factory()->create();

        $customer = \App\Models\Customer::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create(['customer_id' => $customer->id]);

        // Orders for first distribution center
        Order::factory()->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'subtotal' => 100.00,
            'delivery_fee' => 0,
            'order_date' => Carbon::parse($startDate)->addHours(10),
            'created_at' => Carbon::parse($startDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        Order::factory()->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'subtotal' => 200.00,
            'delivery_fee' => 0,
            'order_date' => Carbon::parse($startDate)->addDay()->addHours(10),
            'created_at' => Carbon::parse($startDate)->addDay()->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Orders for second distribution center
        Order::factory()->create([
            'distribution_center_id' => $distributionCenter2->id,
            'subtotal' => 50.00,
            'delivery_fee' => 0,
            'order_date' => Carbon::parse($startDate)->addHours(10),
            'created_at' => Carbon::parse($startDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        Order::factory()->create([
            'distribution_center_id' => $distributionCenter2->id,
            'subtotal' => 150.00,
            'delivery_fee' => 0,
            'order_date' => Carbon::parse($endDate)->addHours(10),
            'created_at' => Carbon::parse($endDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Act
        $result = $this->dashboardGraphService->getRevenueByDay($startDate, $endDate, null);

        // Assert
        $this->assertIsObject($result);
        $this->assertObjectHasProperty('labels', $result);
        $this->assertObjectHasProperty('data', $result);

        $this->assertCount(3, $result->labels); // 3 days in the period
        $this->assertCount(3, $result->data);

        // Expected data: (100 + 50), 200, 150
        $this->assertEquals(150.00, $result->data[0]);
        $this->assertEquals(200.00, $result->data[1]);
        $this->assertEquals(150.00, $result->data[2]);
    }

    /**
     * @test
     */
    public function it_can_get_revenue_by_day_for_multiple_distribution_centers(): void
    {
        // Arrange
        $startDate = Carbon::now()->subDays(2)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $distributionCenter2 = DistributionCenter::factory()->create();
        $distributionCenter3 = DistributionCenter::factory()->create();

        $customer = \App\Models\Customer::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create(['customer_id' => $customer->id]);

        // Orders for first distribution center
        Order::factory()->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'subtotal' => 100.00,
            'delivery_fee' => 0,
            'order_date' => Carbon::parse($startDate)->addHours(10),
            'created_at' => Carbon::parse($startDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        Order::factory()->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'subtotal' => 200.00,
            'delivery_fee' => 0,
            'order_date' => Carbon::parse($startDate)->addDay()->addHours(10),
            'created_at' => Carbon::parse($startDate)->addDay()->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Orders for second distribution center
        Order::factory()->create([
            'distribution_center_id' => $distributionCenter2->id,
            'subtotal' => 50.00,
            'delivery_fee' => 0,
            'order_date' => Carbon::parse($startDate)->addHours(10),
            'created_at' => Carbon::parse($startDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        Order::factory()->create([
            'distribution_center_id' => $distributionCenter2->id,
            'subtotal' => 150.00,
            'delivery_fee' => 0,
            'order_date' => Carbon::parse($endDate)->addHours(10),
            'created_at' => Carbon::parse($endDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Orders for third distribution center (should not be included)
        Order::factory()->create([
            'distribution_center_id' => $distributionCenter3->id,
            'subtotal' => 1000.00,
            'delivery_fee' => 0,
            'order_date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now()->format('Y-m-d'),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Act
        $result = $this->dashboardGraphService->getRevenueByDay($startDate, $endDate, [$this->distributionCenter->id, $distributionCenter2->id]);

        // Assert
        $this->assertIsObject($result);
        $this->assertObjectHasProperty('labels', $result);
        $this->assertObjectHasProperty('data', $result);

        $this->assertCount(3, $result->labels); // 3 days in the period
        $this->assertCount(3, $result->data);

        // Expected data: (100 + 50), 200, 150
        $this->assertEquals(150.00, $result->data[0]);
        $this->assertEquals(200.00, $result->data[1]);
        $this->assertEquals(150.00, $result->data[2]);
    }

    /**
     * @test
     */
    public function it_can_get_orders_by_day_for_a_single_distribution_center(): void
    {
        // Arrange
        $startDate = Carbon::now()->subDays(2)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $customer = \App\Models\Customer::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create(['customer_id' => $customer->id]);

        // Create orders for the distribution center
        Order::factory()->count(2)->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'order_date' => Carbon::parse($startDate)->addHours(10),
            'created_at' => Carbon::parse($startDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        Order::factory()->count(1)->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'order_date' => Carbon::parse($startDate)->addDay()->addHours(10),
            'created_at' => Carbon::parse($startDate)->addDay()->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        Order::factory()->count(3)->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'order_date' => Carbon::parse($endDate)->addHours(10),
            'created_at' => Carbon::parse($endDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Act
        $result = $this->dashboardGraphService->getOrdersByDay($startDate, $endDate, $this->distributionCenter->id);

        // Assert
        $this->assertIsObject($result);
        $this->assertObjectHasProperty('labels', $result);
        $this->assertObjectHasProperty('data', $result);

        $this->assertCount(3, $result->labels); // 3 days in the period
        $this->assertCount(3, $result->data);

        $this->assertEquals(2, $result->data[0]);
        $this->assertEquals(1, $result->data[1]);
        $this->assertEquals(3, $result->data[2]);
    }

    /**
     * @test
     */
    public function it_can_get_orders_by_day_for_all_distribution_centers(): void
    {
        // Arrange
        $startDate = Carbon::now()->subDays(2)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $distributionCenter2 = DistributionCenter::factory()->create();

        $customer = \App\Models\Customer::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create(['customer_id' => $customer->id]);

        // Orders for first distribution center
        Order::factory()->count(2)->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'order_date' => Carbon::parse($startDate)->addHours(10),
            'created_at' => Carbon::parse($startDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        Order::factory()->count(1)->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'order_date' => Carbon::parse($startDate)->addDay()->addHours(10),
            'created_at' => Carbon::parse($startDate)->addDay()->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Orders for second distribution center
        Order::factory()->count(1)->create([
            'distribution_center_id' => $distributionCenter2->id,
            'order_date' => Carbon::parse($startDate)->addHours(10),
            'created_at' => Carbon::parse($startDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        Order::factory()->count(3)->create([
            'distribution_center_id' => $distributionCenter2->id,
            'order_date' => Carbon::parse($endDate)->addHours(10),
            'created_at' => Carbon::parse($endDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Act
        $result = $this->dashboardGraphService->getOrdersByDay($startDate, $endDate, null);

        // Assert
        $this->assertIsObject($result);
        $this->assertObjectHasProperty('labels', $result);
        $this->assertObjectHasProperty('data', $result);

        $this->assertCount(3, $result->labels); // 3 days in the period
        $this->assertCount(3, $result->data);

        // Expected data: (2 + 1), 1, 3
        $this->assertEquals(3, $result->data[0]);
        $this->assertEquals(1, $result->data[1]);
        $this->assertEquals(3, $result->data[2]);
    }

    /**
     * @test
     */
    public function it_can_get_orders_by_day_for_multiple_distribution_centers(): void
    {
        // Arrange
        $startDate = Carbon::now()->subDays(2)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $distributionCenter2 = DistributionCenter::factory()->create();
        $distributionCenter3 = DistributionCenter::factory()->create();

        $customer = \App\Models\Customer::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create(['customer_id' => $customer->id]);

        // Orders for first distribution center
        Order::factory()->count(2)->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'order_date' => Carbon::parse($startDate)->addHours(10),
            'created_at' => Carbon::parse($startDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        Order::factory()->count(1)->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'order_date' => Carbon::parse($startDate)->addDay()->addHours(10),
            'created_at' => Carbon::parse($startDate)->addDay()->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Orders for second distribution center
        Order::factory()->count(1)->create([
            'distribution_center_id' => $distributionCenter2->id,
            'order_date' => Carbon::parse($startDate)->addHours(10),
            'created_at' => Carbon::parse($startDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        Order::factory()->count(3)->create([
            'distribution_center_id' => $distributionCenter2->id,
            'order_date' => Carbon::parse($endDate)->addHours(10),
            'created_at' => Carbon::parse($endDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Orders for third distribution center (should not be included)
        Order::factory()->count(5)->create([
            'distribution_center_id' => $distributionCenter3->id,
            'order_date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now()->format('Y-m-d'),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Act
        $result = $this->dashboardGraphService->getOrdersByDay($startDate, $endDate, [$this->distributionCenter->id, $distributionCenter2->id]);

        // Assert
        $this->assertIsObject($result);
        $this->assertObjectHasProperty('labels', $result);
        $this->assertObjectHasProperty('data', $result);

        $this->assertCount(3, $result->labels); // 3 days in the period
        $this->assertCount(3, $result->data);

        // Expected data: (2 + 1), 1, (3)
        $this->assertEquals(3, $result->data[0]);
        $this->assertEquals(1, $result->data[1]);
        $this->assertEquals(3, $result->data[2]);
    }
}
