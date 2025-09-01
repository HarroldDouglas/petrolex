<?php

namespace Tests\Feature\Services;

use App\DTOs\Dashboard\StockMovementStatsDTO;
use App\Enums\BottleOrderType;
use App\Enums\SupplierDeliveryStatus;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\ProductCategory;
use App\Models\SupplierDelivery;
use App\Services\Dashboard\StockMovementStatsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementStatsServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockMovementStatsService $stockMovementStatsService;
    private DistributionCenter $distributionCenter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->stockMovementStatsService = $this->app->make(StockMovementStatsService::class);
        $this->distributionCenter = DistributionCenter::factory()->create();
    }

    /**
     * Test method
     */
    public function test_it_can_get_stock_stats_for_a_single_distribution_center(): void
    {
        // Arrange
        $startDate = Carbon::now()->subDays(2)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $customer = \App\Models\Customer::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create(['customer_id' => $customer->id]);

        // Create orders for sold bottles
        Order::factory()->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'order_date' => Carbon::parse($startDate)->addHours(10),
            'created_at' => Carbon::parse($startDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ])->items()->create([
            'product_category_id' => ProductCategory::factory()->create()->id,
            'bottle_type' => BottleOrderType::FULL()->value,
            'quantity' => 10,
            'unit_price' => 10.00,
            'total_price' => 100.00,
        ]);

        // Create orders for exchanges
        Order::factory()->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'order_date' => Carbon::parse($startDate)->addDay()->addHours(10),
            'created_at' => Carbon::parse($startDate)->addDay()->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ])->items()->create([
            'product_category_id' => ProductCategory::factory()->create()->id,
            'bottle_type' => BottleOrderType::RECHARGE()->value,
            'quantity' => 5,
            'unit_price' => 10.00,
            'total_price' => 50.00,
        ]);

        // Create supplier delivery for supplied bottles
        SupplierDelivery::factory()->create([
            'distribution_center_id' => $this->distributionCenter->id,
            'supply_date' => Carbon::parse($endDate)->addHours(10),
            'status' => SupplierDeliveryStatus::COMPLETED()->value,
        ])->productTypes()->create([
            'product_category_id' => ProductCategory::factory()->create()->id,
            'expected_quantity' => 20,
        ]);

        // For fullBottles and emptyBottles, we need to investigate DistributionCenter model
        // For now, set them to 0 as we don't have a clear way to set them via factories yet.
        // We will need to mock the DistributionCenter model or its attributes if they are not directly manipulable via stock movements.
        $fullBottles = 0;
        $emptyBottles = 0;

        // Act
        $stats = $this->stockMovementStatsService->getStockStats($startDate, $endDate, [$this->distributionCenter->id]);

        // Assert
        $this->assertInstanceOf(StockMovementStatsDTO::class, $stats);
        $this->assertEquals(10, $stats->totalSoldBottles);
        $this->assertEquals(5, $stats->totalExchanges);
        $this->assertEquals(0, $stats->fullBottles);
        $this->assertEquals(0, $stats->emptyBottles);
        $this->assertEquals(20, $stats->totalSupplied);
    }

    /**
     * Test method
     */
    public function test_it_can_get_stock_stats_for_all_distribution_centers(): void
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
            'order_date' => Carbon::parse($startDate)->addHours(10),
            'created_at' => Carbon::parse($startDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ])->items()->create([
            'product_category_id' => ProductCategory::factory()->create()->id,
            'bottle_type' => BottleOrderType::FULL()->value,
            'quantity' => 10,
            'unit_price' => 10.00,
            'total_price' => 100.00,
        ]);

        // Orders for second distribution center
        Order::factory()->create([
            'distribution_center_id' => $distributionCenter2->id,
            'order_date' => Carbon::parse($startDate)->addHours(10),
            'created_at' => Carbon::parse($startDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ])->items()->create([
            'product_category_id' => ProductCategory::factory()->create()->id,
            'bottle_type' => BottleOrderType::FULL()->value,
            'quantity' => 20,
            'unit_price' => 10.00,
            'total_price' => 200.00,
        ]);

        // Act
        $stats = $this->stockMovementStatsService->getStockStats($startDate, $endDate, null);

        // Assert
        $this->assertInstanceOf(StockMovementStatsDTO::class, $stats);
        $this->assertEquals(30, $stats->totalSoldBottles); // 10 + 20
        $this->assertEquals(0, $stats->totalExchanges);
        $this->assertEquals(0, $stats->fullBottles);
        $this->assertEquals(0, $stats->emptyBottles);
        $this->assertEquals(0, $stats->totalSupplied);
    }

    /**
     * Test method
     */
    public function test_it_can_get_stock_stats_for_multiple_distribution_centers(): void
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
            'order_date' => Carbon::parse($startDate)->addHours(10),
            'created_at' => Carbon::parse($startDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ])->items()->create([
            'product_category_id' => ProductCategory::factory()->create()->id,
            'bottle_type' => BottleOrderType::FULL()->value,
            'quantity' => 10,
            'unit_price' => 10.00,
            'total_price' => 100.00,
        ]);

        // Orders for second distribution center
        Order::factory()->create([
            'distribution_center_id' => $distributionCenter2->id,
            'order_date' => Carbon::parse($startDate)->addHours(10),
            'created_at' => Carbon::parse($startDate)->addHours(10),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ])->items()->create([
            'product_category_id' => ProductCategory::factory()->create()->id,
            'bottle_type' => BottleOrderType::FULL()->value,
            'quantity' => 20,
            'unit_price' => 10.00,
            'total_price' => 200.00,
        ]);

        // Orders for third distribution center (should not be included)
        Order::factory()->create([
            'distribution_center_id' => $distributionCenter3->id,
            'order_date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now()->format('Y-m-d'),
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
        ])->items()->create([
            'product_category_id' => ProductCategory::factory()->create()->id,
            'bottle_type' => BottleOrderType::FULL()->value,
            'quantity' => 50,
            'unit_price' => 10.00,
            'total_price' => 500.00,
        ]);

        // Act
        $stats = $this->stockMovementStatsService->getStockStats($startDate, $endDate, [$this->distributionCenter->id, $distributionCenter2->id]);

        // Assert
        $this->assertInstanceOf(StockMovementStatsDTO::class, $stats);
        $this->assertEquals(30, $stats->totalSoldBottles); // 10 + 20
        $this->assertEquals(0, $stats->totalExchanges);
        $this->assertEquals(0, $stats->fullBottles);
        $this->assertEquals(0, $stats->emptyBottles);
        $this->assertEquals(0, $stats->totalSupplied);
    }
}
