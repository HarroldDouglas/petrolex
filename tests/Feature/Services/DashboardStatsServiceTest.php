<?php

namespace Tests\Feature\Services;

use App\DTOs\Dashboard\StatsDTO;
use App\Enums\OrderStatus;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Services\Dashboard\DashboardStatsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatsServiceTest extends TestCase
{
    use RefreshDatabase;

    private DashboardStatsService $dashboardStatsService;
    private DistributionCenter $distributionCenter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->dashboardStatsService = $this->app->make(DashboardStatsService::class);
        $this->distributionCenter = DistributionCenter::factory()->create();
    }

    /**
     * Test method
     */
    public function test_it_can_get_stats_for_a_single_distribution_center(): void
    {
        // Arrange
        $startDate = Carbon::now()->subDays(2)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $customer = \App\Models\Customer::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create(['customer_id' => $customer->id]);

        // Create orders for the distribution center
        Order::factory()->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'subtotal' => 100.50,
            'delivery_fee' => 0,
            'order_date' => Carbon::parse($startDate)->addHours(10),
            'created_at' => Carbon::parse($startDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
            'status' => OrderStatus::DELIVERED()->value,
        ]);
        Order::factory()->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'subtotal' => 200.00,
            'delivery_fee' => 0,
            'order_date' => Carbon::parse($startDate)->addDay()->addHours(10),
            'created_at' => Carbon::parse($startDate)->addDay()->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
            'status' => OrderStatus::PENDING()->value,
        ]);
        Order::factory()->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'subtotal' => 50.00,
            'delivery_fee' => 0,
            'order_date' => Carbon::parse($endDate)->addHours(10),
            'created_at' => Carbon::parse($endDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
            'status' => OrderStatus::CANCELLED()->value,
        ]);

        // Act
        $stats = $this->dashboardStatsService->getStats($startDate, $endDate, $this->distributionCenter->id);

        // Assert
        $this->assertInstanceOf(StatsDTO::class, $stats);
        $this->assertEquals(100.50, $stats->revenue);
        $this->assertEquals(1, $stats->pendingOrders);
        $this->assertEquals(1, $stats->deliveredOrders);
        $this->assertEquals(1, $stats->canceledOrders);
    }

    /**
     * Test method
     */
    public function test_it_can_get_stats_for_all_distribution_centers(): void
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
            'status' => OrderStatus::DELIVERED()->value,
        ]);
        Order::factory()->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'subtotal' => 200.00,
            'delivery_fee' => 0,
            'order_date' => Carbon::parse($startDate)->addDay()->addHours(10),
            'created_at' => Carbon::parse($startDate)->addDay()->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
            'status' => OrderStatus::PENDING()->value,
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
            'status' => OrderStatus::DELIVERED()->value,
        ]);
        Order::factory()->create([
            'distribution_center_id' => $distributionCenter2->id,
            'subtotal' => 150.00,
            'delivery_fee' => 0,
            'order_date' => Carbon::parse($endDate)->addHours(10),
            'created_at' => Carbon::parse($endDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
            'status' => OrderStatus::CANCELLED()->value,
        ]);

        // Act
        $stats = $this->dashboardStatsService->getStats($startDate, $endDate, null);

        // Assert
        $this->assertInstanceOf(StatsDTO::class, $stats);
        $this->assertEquals(150.00, $stats->revenue); // 100 (delivered) + 50 (delivered)
        $this->assertEquals(1, $stats->pendingOrders);
        $this->assertEquals(2, $stats->deliveredOrders);
        $this->assertEquals(1, $stats->canceledOrders);
    }

    /**
     * Test method
     */
    public function test_it_can_get_stats_for_multiple_distribution_centers(): void
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
            'status' => OrderStatus::DELIVERED()->value,
        ]);
        Order::factory()->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'subtotal' => 200.00,
            'delivery_fee' => 0,
            'order_date' => Carbon::parse($startDate)->addDay()->addHours(10),
            'created_at' => Carbon::parse($startDate)->addDay()->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
            'status' => OrderStatus::PENDING()->value,
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
            'status' => OrderStatus::DELIVERED()->value,
        ]);
        Order::factory()->create([
            'distribution_center_id' => $distributionCenter2->id,
            'subtotal' => 150.00,
            'delivery_fee' => 0,
            'order_date' => Carbon::parse($endDate)->addHours(10),
            'created_at' => Carbon::parse($endDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
            'status' => OrderStatus::CANCELLED()->value,
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
            'status' => OrderStatus::DELIVERED()->value,
        ]);

        // Act
        $stats = $this->dashboardStatsService->getStats($startDate, $endDate, [$this->distributionCenter->id, $distributionCenter2->id]);

        // Assert
        $this->assertInstanceOf(StatsDTO::class, $stats);
        $this->assertEquals(150.00, $stats->revenue); // 100 (delivered) + 50 (delivered)
        $this->assertEquals(1, $stats->pendingOrders);
        $this->assertEquals(2, $stats->deliveredOrders);
        $this->assertEquals(1, $stats->canceledOrders);
    }
}
