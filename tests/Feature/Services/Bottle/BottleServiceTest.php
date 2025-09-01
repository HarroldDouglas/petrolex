<?php

namespace Tests\Feature\Services\Bottle;

use App\Enums\BottleStatus;
use App\Models\Bottle;
use App\Models\DistributionCenter;
use App\Models\Product;
use App\Models\User;
use App\Services\Bottle\BottleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BottleServiceTest extends TestCase
{
    use RefreshDatabase;

    private BottleService $bottleService;
    private User $user;
    private DistributionCenter $distributionCenter;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bottleService = $this->app->make(BottleService::class);

        // Create geographical dependencies
        $country = \App\Models\Geography\Country::factory()->create();
        $city = \App\Models\Geography\City::factory()->create(['country_id' => $country->id]);
        $municipality = \App\Models\Geography\Municipality::factory()->create(['city_id' => $city->id]);
        $neighborhood = \App\Models\Geography\Neighborhood::factory()->create(['municipality_id' => $municipality->id]);

        // Create product type dependencies
        $bottleType = \App\Models\BottleType::factory()->create();
        $accessoryType = \App\Models\AccessoryType::factory()->create();

        // Create product category
        $productCategory = \App\Models\ProductCategory::factory()->create([
            'product_type' => \App\Enums\ProductType::BOTTLE(),
            'product_type_id' => $bottleType->id,
        ]);

        $this->user = User::factory()->create();
        $this->distributionCenter = DistributionCenter::factory()->create(['neighborhood_id' => $neighborhood->id]);
        $this->product = Product::factory()->create(['product_category_id' => $productCategory->id]);
    }

    private function createBottle(array $overrides = []): Bottle
    {
        return Bottle::factory()->create(array_merge([
            'product_id' => $this->product->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'barcode' => 'BAR-'.uniqid(),
            'is_filled' => true,
            'status' => BottleStatus::IN_STOCK(),
        ], $overrides));
    }

    /**
     * Test method
     */
    public function test_it_can_create_a_bottle(): void
    {
        // Arrange
        $bottleData = [
            'product_id' => $this->product->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'barcode' => 'BAR-'.uniqid(),
            'is_filled' => true,
            'status' => BottleStatus::IN_STOCK(),
        ];

        // Act
        $this->actingAs($this->user);
        $createdBottle = $this->bottleService->create($bottleData);

        // Assert
        $this->assertInstanceOf(Bottle::class, $createdBottle);
        $this->assertDatabaseHas('bottles', [
            'id' => $createdBottle->id,
            'barcode' => $bottleData['barcode'],
        ]);
    }

    /**
     * Test method
     */
    public function test_it_can_find_a_bottle(): void
    {
        // Arrange
        $bottle = $this->createBottle();

        // Act
        $foundBottle = $this->bottleService->find($bottle->id);

        // Assert
        $this->assertInstanceOf(Bottle::class, $foundBottle);
        $this->assertEquals($bottle->id, $foundBottle->id);
    }

    /**
     * Test method
     */
    public function test_it_can_update_a_bottle(): void
    {
        // Arrange
        $bottle = $this->createBottle();
        $updateData = ['status' => BottleStatus::WITH_CLIENT()];

        // Act
        $this->actingAs($this->user);
        $updatedBottle = $this->bottleService->update($bottle, $updateData);

        // Assert
        $this->assertEquals(BottleStatus::WITH_CLIENT(), $updatedBottle->status);
        $this->assertDatabaseHas('bottles', [
            'id' => $bottle->id,
            'status' => BottleStatus::WITH_CLIENT(),
        ]);
    }

    /**
     * Test method
     */
    public function test_it_can_delete_a_bottle(): void
    {
        // Arrange
        $bottle = $this->createBottle();

        // Act
        $this->actingAs($this->user);
        $result = $this->bottleService->delete($bottle);

        // Assert
        $this->assertTrue($result);
        $this->assertSoftDeleted('bottles', ['id' => $bottle->id]);
    }

    /**
     * Test method
     */
    public function test_it_can_get_all_bottles(): void
    {
        // Arrange
        $this->createBottle();
        $this->createBottle();

        // Act
        $bottles = $this->bottleService->getAll();

        // Assert
        $this->assertCount(2, $bottles);
    }

    /**
     * Test method
     */
    public function test_it_can_paginate_bottles(): void
    {
        // Arrange
        for ($i = 0; $i < 20; $i++) {
            $this->createBottle();
        }

        // Act
        $paginatedBottles = $this->bottleService->paginate(15);

        // Assert
        $this->assertCount(15, $paginatedBottles);
    }

    /**
     * Test method
     */
    public function test_it_can_get_bottle_stats(): void
    {
        // Arrange
        $this->createBottle(['status' => BottleStatus::IN_STOCK()]);
        $this->createBottle(['status' => BottleStatus::WITH_DELIVERY_PERSON()]);
        $this->createBottle(['status' => BottleStatus::WITH_CLIENT()]);
        $this->createBottle(['status' => BottleStatus::LOST_STOLEN()]);

        // Act
        $stats = $this->bottleService->getStats();

        // Assert
        $this->assertEquals(1, $stats->inStock);
        $this->assertEquals(1, $stats->withDeliveryPerson);
        $this->assertEquals(1, $stats->withClient);
        $this->assertEquals(1, $stats->lostStolen);
    }
}
