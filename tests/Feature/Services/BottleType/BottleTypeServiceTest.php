<?php

namespace Tests\Feature\Services\BottleType;

use App\DTOs\ProductCategory\ProductCategoryCityPriceDTO;
use App\Models\BottleType;
use App\Models\Geography\City;
use App\Models\ProductCategory;
use App\Services\BottleType\BottleTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BottleTypeServiceTest extends TestCase
{
    use RefreshDatabase;

    private BottleTypeService $bottleTypeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bottleTypeService = $this->app->make(BottleTypeService::class);
    }

    private function createBottleType(array $overrides = []): BottleType
    {
        $bottleType = BottleType::factory()->create($overrides);
        $productCategory = ProductCategory::create([
            'product_type' => \App\Enums\ProductType::BOTTLE(),
            'product_type_id' => $bottleType->id,
        ]);
        $bottleType->setRelation('productCategory', $productCategory);

        return $bottleType;
    }

    /**
     * Test method
     */
    public function test_it_can_create_a_bottle_type(): void
    {
        // Arrange
        $city = City::factory()->create();
        $cityPriceDTO = new ProductCategoryCityPriceDTO(
            product_category_id: 1,
            city_id: $city->id,
            content_price: 100.00,
            content_with_bottle_price: 150.00
        );

        $bottleTypeData = [
            'name' => 'Test Bottle Type',
            'description' => 'A test bottle type',
            'capacity' => 10.0,
            'height' => 30.0,
            'weight' => 5.0,
            'radius' => 10.0,
            'content_price' => 100.00,
            'full_price' => 150.00,
            'is_active' => true,
            'bottleTypeCityPrices' => [$cityPriceDTO],
        ];

        // Act
        $createdBottleType = $this->bottleTypeService->create($bottleTypeData);

        // Assert
        $this->assertInstanceOf(BottleType::class, $createdBottleType);
        $this->assertDatabaseHas('bottle_types', [
            'id' => $createdBottleType->id,
            'name' => 'Test Bottle Type',
        ]);
        $this->assertDatabaseHas('product_categories', [
            'product_type_id' => $createdBottleType->id,
            'product_type' => 'bottle',
        ]);
        $this->assertDatabaseHas('product_category_city_prices', [
            'product_category_id' => $createdBottleType->productCategory->id,
            'city_id' => $city->id,
            'content_price' => 100.00,
            'content_with_bottle_price' => 150.00,
        ]);
    }

    /**
     * Test method
     */
    public function test_it_can_find_a_bottle_type(): void
    {
        // Arrange
        $bottleType = $this->createBottleType();

        // Act
        $foundBottleType = $this->bottleTypeService->find($bottleType->id);

        // Assert
        $this->assertInstanceOf(BottleType::class, $foundBottleType);
        $this->assertEquals($bottleType->id, $foundBottleType->id);
    }

    /**
     * Test method
     */
    public function test_it_can_update_a_bottle_type(): void
    {
        // Arrange
        $bottleType = $this->createBottleType();
        $existingAttributes = $bottleType->toArray();
        $city = City::factory()->create();
        $cityPriceDTO = new ProductCategoryCityPriceDTO(
            product_category_id: 1,
            city_id: $city->id,
            content_price: 200.00,
            content_with_bottle_price: 250.00
        );
        $updateData = array_merge($existingAttributes, [
            'name' => 'Updated Bottle Type',
            'bottleTypeCityPrices' => [$cityPriceDTO],
        ]);

        // Act
        $updatedBottleType = $this->bottleTypeService->update($bottleType, $updateData);
        $updatedBottleType->load('productCategory');

        // Assert
        $this->assertEquals('Updated Bottle Type', $updatedBottleType->name);
        $this->assertDatabaseHas('bottle_types', [
            'id' => $bottleType->id,
            'name' => 'Updated Bottle Type',
        ]);
        $this->assertDatabaseHas('product_category_city_prices', [
            'product_category_id' => $updatedBottleType->productCategory->id,
            'city_id' => $city->id,
            'content_price' => 200.00,
            'content_with_bottle_price' => 250.00,
        ]);
    }

    /**
     * Test method
     */
    public function test_it_can_delete_a_bottle_type(): void
    {
        // Arrange
        $bottleType = $this->createBottleType();

        // Act
        $result = $this->bottleTypeService->delete($bottleType);

        // Assert
        $this->assertTrue($result);
        $this->assertSoftDeleted('bottle_types', ['id' => $bottleType->id]);
    }

    /**
     * Test method
     */
    public function test_it_can_get_all_bottle_types(): void
    {
        // Arrange
        $this->createBottleType();
        $this->createBottleType();

        // Act
        $bottleTypes = $this->bottleTypeService->getAll();

        // Assert
        $this->assertCount(2, $bottleTypes);
    }

    /**
     * Test method
     */
    public function test_it_can_paginate_bottle_types(): void
    {
        // Arrange
        for ($i = 0; $i < 20; $i++) {
            $this->createBottleType();
        }

        // Act
        $paginatedBottleTypes = $this->bottleTypeService->paginate(15);

        // Assert
        $this->assertCount(15, $paginatedBottleTypes);
    }

    /**
     * Test method
     */
    public function test_it_can_get_bottle_type_with_media(): void
    {
        // Arrange
        $bottleType = $this->createBottleType();
        // Assuming you have a way to attach media, e.g., using Spatie Media Library
        // $bottleType->addMedia(UploadedFile::fake()->image('test.jpg'))->toMediaCollection('images');

        // Act
        $foundBottleType = $this->bottleTypeService->getWithMedia($bottleType->id);

        // Assert
        $this->assertInstanceOf(BottleType::class, $foundBottleType);
        $this->assertEquals($bottleType->id, $foundBottleType->id);
        // $this->assertCount(1, $foundBottleType->getMedia('images')); // Uncomment if media is added
    }
}
