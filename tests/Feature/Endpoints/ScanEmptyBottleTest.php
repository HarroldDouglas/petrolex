<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Models\Bottle;
use App\Models\BottleType;
use App\Models\Customer;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\OrderBottleScans;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ScanEmptyBottleTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private string $authToken;
    private DistributionCenter $distributionCenter;
    private BottleType $bottleType;
    private ProductCategory $productCategory;
    private Product $product;
    private Customer $customer;
    private Order $order;
    private OrderItem $orderItem;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'center_manager', 'guard_name' => 'web']);

        $this->adminUser = User::factory()->create([
            'password' => bcrypt('password'),
        ]);
        $this->adminUser->assignRole('admin');

        $response = $this->postJson(route('api.login'), [
            'login' => $this->adminUser->email,
            'password' => 'password',
        ]);
        $this->authToken = $response->json('data.access_token');

        $this->distributionCenter = DistributionCenter::factory()->create();
        $this->bottleType = BottleType::factory()->create();
        $this->productCategory = ProductCategory::factory()->create([
            'product_type' => ProductType::BOTTLE(),
            'product_type_id' => $this->bottleType->id,
        ]);
        $this->product = Product::factory()->create([
            'product_category_id' => $this->productCategory->id,
        ]);
        $this->customer = Customer::factory()->create();

        $this->order = Order::factory()->create([
            'status' => OrderStatus::CONFIRMED(),
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $this->distributionCenter->id,
        ]);

        $this->orderItem = OrderItem::create([
            'order_id' => $this->order->id,
            'product_category_id' => $this->productCategory->id,
            'quantity' => 1,
            'unit_price' => 1000,
            'total_price' => 1000,
        ]);
    }

    /** @test */
    public function it_can_scan_existing_empty_bottle_and_associate_with_order(): void
    {
        $filledBottle = Bottle::factory()->create([
            'is_filled' => 1,
            'product_id' => $this->product->id,
            'distribution_center_id' => $this->distributionCenter->id,
        ]);

        OrderBottleScans::create([
            'order_item_id' => $this->orderItem->id,
            'bottle_id' => $filledBottle->id,
            'empty_bottle_id' => null,
        ]);

        $emptyBottleToReturn = Bottle::factory()->create([
            'is_filled' => 0,
            'product_id' => $this->product->id,
            'distribution_center_id' => $this->distributionCenter->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.scan-empty-bottle', $this->order), [
            'barcode' => $emptyBottleToReturn->barcode,
            'order_item_id' => $this->orderItem->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true);

        $this->assertDatabaseHas('order_bottle_scans', [
            'order_item_id' => $this->orderItem->id,
            'bottle_id' => $filledBottle->id,
            'empty_bottle_id' => $emptyBottleToReturn->id,
        ]);

        $this->assertDatabaseHas('bottles', [
            'id' => $emptyBottleToReturn->id,
            'is_filled' => 0,
            'distribution_center_id' => $this->distributionCenter->id,
        ]);
    }

    /** @test */
    public function it_can_scan_new_empty_bottle_and_associate_with_order(): void
    {
        $filledBottle = Bottle::factory()->create([
            'is_filled' => 1,
            'product_id' => $this->product->id,
            'distribution_center_id' => $this->distributionCenter->id,
        ]);

        OrderBottleScans::create([
            'order_item_id' => $this->orderItem->id,
            'bottle_id' => $filledBottle->id,
            'empty_bottle_id' => null,
        ]);

        $newBarcode = 'NEW-BOTTLE-BARCODE-'.rand(1000, 9999);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.scan-empty-bottle', $this->order), [
            'barcode' => $newBarcode,
            'order_item_id' => $this->orderItem->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true);

        $newBottle = Bottle::where('barcode', $newBarcode)->first();
        $this->assertNotNull($newBottle);

        $this->assertDatabaseHas('order_bottle_scans', [
            'order_item_id' => $this->orderItem->id,
            'bottle_id' => $filledBottle->id,
            'empty_bottle_id' => $newBottle->id,
        ]);

        $this->assertDatabaseHas('bottles', [
            'id' => $newBottle->id,
            'barcode' => $newBarcode,
            'is_filled' => 0,
            'distribution_center_id' => $this->distributionCenter->id,
        ]);
    }
}
