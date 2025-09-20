<?php

namespace Tests\Feature\Services;

use App\Enums\BottleOrderType;
use App\Enums\ProductType;
use App\Models\AccessoryType;
use App\Models\BottleType;
use App\Models\DistributionCenter;
use App\Models\ProductCategory;
use App\Services\ProductCategoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductCategoryService $productCategoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productCategoryService = $this->app->make(ProductCategoryService::class);
    }

    private function createBottleProductCategory(array $bottleOverrides = []): ProductCategory
    {
        $bottleType = BottleType::factory()->create(array_merge([
            'full_price' => 25.00,
            'content_price' => 15.00,
            'is_active' => true,
        ], $bottleOverrides));

        return ProductCategory::factory()->create([
            'product_type' => ProductType::BOTTLE(),
            'product_type_id' => $bottleType->id,
        ]);
    }

    private function createAccessoryProductCategory(array $accessoryOverrides = []): ProductCategory
    {
        $accessoryType = AccessoryType::factory()->create(array_merge([
            'price' => 10.00,
            'is_active' => true,
        ], $accessoryOverrides));

        return ProductCategory::factory()->create([
            'product_type' => ProductType::ACCESSORY(),
            'product_type_id' => $accessoryType->id,
        ]);
    }

    public function test_it_can_create_a_product_category(): void
    {
        $bottleType = BottleType::factory()->create();
        $productCategoryData = [
            'product_type' => ProductType::BOTTLE(),
            'product_type_id' => $bottleType->id,
        ];

        $createdProductCategory = $this->productCategoryService->create($productCategoryData);

        $this->assertInstanceOf(ProductCategory::class, $createdProductCategory);
        $this->assertDatabaseHas('product_categories', [
            'id' => $createdProductCategory->id,
            'product_type' => ProductType::BOTTLE()->value,
            'product_type_id' => $bottleType->id,
        ]);
    }

    public function test_it_can_find_a_product_category(): void
    {
        $productCategory = $this->createBottleProductCategory();

        $foundProductCategory = $this->productCategoryService->find($productCategory->id);

        $this->assertInstanceOf(ProductCategory::class, $foundProductCategory);
        $this->assertEquals($productCategory->id, $foundProductCategory->id);
    }

    public function test_it_can_update_a_product_category(): void
    {
        $productCategory = $this->createBottleProductCategory();
        $newBottleType = BottleType::factory()->create();
        $updateData = ['product_type_id' => $newBottleType->id];

        $updatedProductCategory = $this->productCategoryService->update($productCategory, $updateData);

        $this->assertEquals($newBottleType->id, $updatedProductCategory->product_type_id);
        $this->assertDatabaseHas('product_categories', [
            'id' => $productCategory->id,
            'product_type_id' => $newBottleType->id,
        ]);
    }

    public function test_it_can_delete_a_product_category(): void
    {
        $productCategory = $this->createBottleProductCategory();

        $result = $this->productCategoryService->delete($productCategory);

        $this->assertTrue($result);
        $this->assertSoftDeleted('product_categories', ['id' => $productCategory->id]);
    }

    public function test_it_can_get_all_product_categories(): void
    {
        $this->createBottleProductCategory();
        $this->createAccessoryProductCategory();

        $productCategories = $this->productCategoryService->getAll();

        $this->assertCount(2, $productCategories);
    }

    public function test_it_can_paginate_product_categories(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->createBottleProductCategory();
        }

        $paginatedProductCategories = $this->productCategoryService->paginate(15);

        $this->assertCount(15, $paginatedProductCategories);
    }

    public function test_it_can_get_bottle_price_for_full_order(): void
    {
        $productCategory = $this->createBottleProductCategory([
            'full_price' => 30.00,
            'content_price' => 20.00,
        ]);

        $price = $this->productCategoryService->getProductPrice(
            $productCategory->id,
            BottleOrderType::FULL()
        );

        $this->assertEquals(30.00, $price);
    }

    public function test_it_can_get_bottle_price_for_recharge_order(): void
    {
        $productCategory = $this->createBottleProductCategory([
            'full_price' => 30.00,
            'content_price' => 20.00,
        ]);

        $price = $this->productCategoryService->getProductPrice(
            $productCategory->id,
            BottleOrderType::RECHARGE()
        );

        $this->assertEquals(20.00, $price);
    }

    public function test_it_can_get_accessory_price(): void
    {
        $productCategory = $this->createAccessoryProductCategory([
            'price' => 15.00,
        ]);

        $price = $this->productCategoryService->getProductPrice($productCategory->id);

        $this->assertEquals(15.00, $price);
    }

    public function test_it_throws_exception_for_non_existent_product_category(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Product category not found.');

        $this->productCategoryService->getProductPrice(999);
    }

    public function test_it_throws_exception_for_product_category_without_instance(): void
    {
        $productCategory = ProductCategory::factory()->create([
            'product_type' => ProductType::BOTTLE(),
            'product_type_id' => 999, // Non-existent bottle type
        ]);

        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Product instance not found for category.');

        $this->productCategoryService->getProductPrice($productCategory->id);
    }

    public function test_it_throws_exception_for_unsupported_bottle_option(): void
    {
        $productCategory = $this->createBottleProductCategory();

        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Price not found for product type.');

        // Pass null for bottle type should trigger the exception since we need an option for bottles
        $this->productCategoryService->getProductPrice($productCategory->id, null);
    }

    public function test_it_can_get_product_quantity(): void
    {
        $productCategory = $this->createBottleProductCategory();
        $distributionCenter = DistributionCenter::factory()->create();

        // Mock the repository to return a specific quantity
        $mockRepository = $this->getMockBuilder(\App\Repositories\Contracts\ProductCategoryRepositoryInterface::class)
            ->getMock();

        $mockRepository->method('find')
            ->with($productCategory->id)
            ->willReturn($productCategory);

        $mockRepository->method('getAvailableStock')
            ->with($productCategory->id, $distributionCenter->id)
            ->willReturn(50);

        $this->app->instance(\App\Repositories\Contracts\ProductCategoryRepositoryInterface::class, $mockRepository);
        $service = $this->app->make(ProductCategoryService::class);

        $quantity = $service->getProductQuantity($productCategory->id, $distributionCenter->id);

        $this->assertEquals(50, $quantity);
    }

    public function test_it_throws_exception_for_quantity_of_non_existent_product_category(): void
    {
        $distributionCenter = DistributionCenter::factory()->create();

        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Product category not found.');

        $this->productCategoryService->getProductQuantity(999, $distributionCenter->id);
    }

    public function test_product_category_name_accessor(): void
    {
        $bottleType = BottleType::factory()->create(['name' => 'Test Bottle']);
        $productCategory = ProductCategory::factory()->create([
            'product_type' => ProductType::BOTTLE(),
            'product_type_id' => $bottleType->id,
        ]);

        $this->assertEquals('Test Bottle', $productCategory->name);
    }

    public function test_product_category_is_active_accessor(): void
    {
        $activeBottleType = BottleType::factory()->create(['is_active' => true]);
        $inactiveBottleType = BottleType::factory()->create(['is_active' => false]);

        $activeProductCategory = ProductCategory::factory()->create([
            'product_type' => ProductType::BOTTLE(),
            'product_type_id' => $activeBottleType->id,
        ]);

        $inactiveProductCategory = ProductCategory::factory()->create([
            'product_type' => ProductType::BOTTLE(),
            'product_type_id' => $inactiveBottleType->id,
        ]);

        $this->assertTrue($activeProductCategory->is_active);
        $this->assertFalse($inactiveProductCategory->is_active);
    }

    public function test_product_category_price_accessor(): void
    {
        $accessoryType = AccessoryType::factory()->create(['price' => 25.50]);
        $productCategory = ProductCategory::factory()->create([
            'product_type' => ProductType::ACCESSORY(),
            'product_type_id' => $accessoryType->id,
        ]);

        $this->assertEquals(25.50, $productCategory->price);
    }
}
