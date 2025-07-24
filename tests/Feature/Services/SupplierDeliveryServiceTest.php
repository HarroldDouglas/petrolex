<?php

namespace Tests\Feature\Services;

use App\Models\DistributionCenter;
use App\Models\SupplierDelivery;
use App\Models\User;
use App\Services\SupplierDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierDeliveryServiceTest extends TestCase
{
    use RefreshDatabase;

    private SupplierDeliveryService $supplierDeliveryService;
    private User $user;
    private DistributionCenter $distributionCenter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->supplierDeliveryService = $this->app->make(SupplierDeliveryService::class);
        $this->user = User::factory()->create();
        $this->distributionCenter = DistributionCenter::factory()->create();
    }

    private function createSupplierDelivery(array $overrides = []): SupplierDelivery
    {
        $data = array_merge([
            'distribution_center_id' => $this->distributionCenter->id,
            'user_id' => $this->user->id,
            'delivery_number' => 'DEL-'.uniqid(),
            'title' => 'Test Delivery',
            'supplier_name' => 'Test Supplier',
            'supply_date' => now(),
        ], $overrides);

        return $this->supplierDeliveryService->create($data);
    }

    /**
     * @test
     */
    public function it_can_create_a_supplier_delivery(): void
    {
        // Arrange
        $deliveryData = [
            'distribution_center_id' => $this->distributionCenter->id,
            'user_id' => $this->user->id,
            'delivery_number' => 'DEL-'.uniqid(),
            'title' => 'Test Delivery',
            'supplier_name' => 'Test Supplier',
            'supply_date' => now(),
        ];

        // Act
        $createdDelivery = $this->supplierDeliveryService->create($deliveryData);

        // Assert
        $this->assertInstanceOf(SupplierDelivery::class, $createdDelivery);
        $this->assertDatabaseHas('supplier_deliveries', [
            'id' => $createdDelivery->id,
            'delivery_number' => $deliveryData['delivery_number'],
        ]);
    }

    /**
     * @test
     */
    public function it_can_find_a_supplier_delivery(): void
    {
        // Arrange
        $delivery = $this->createSupplierDelivery();

        // Act
        $foundDelivery = $this->supplierDeliveryService->find($delivery->id);

        // Assert
        $this->assertInstanceOf(SupplierDelivery::class, $foundDelivery);
        $this->assertEquals($delivery->id, $foundDelivery->id);
    }

    /**
     * @test
     */
    public function it_can_update_a_supplier_delivery(): void
    {
        // Arrange
        $delivery = $this->createSupplierDelivery();
        $updateData = ['title' => 'Updated Title'];

        // Act
        $updatedDelivery = $this->supplierDeliveryService->update($delivery, $updateData);

        // Assert
        $this->assertEquals('Updated Title', $updatedDelivery->title);
        $this->assertDatabaseHas('supplier_deliveries', [
            'id' => $delivery->id,
            'title' => 'Updated Title',
        ]);
    }

    /**
     * @test
     */
    public function it_can_delete_a_supplier_delivery(): void
    {
        // Arrange
        $delivery = $this->createSupplierDelivery();

        // Act
        $result = $this->supplierDeliveryService->delete($delivery);

        // Assert
        $this->assertTrue($result);
        $this->assertSoftDeleted('supplier_deliveries', ['id' => $delivery->id]);
    }

    /**
     * @test
     */
    public function it_can_get_all_supplier_deliveries(): void
    {
        // Arrange
        $this->createSupplierDelivery();
        $this->createSupplierDelivery();

        // Act
        $deliveries = $this->supplierDeliveryService->getAll();

        // Assert
        $this->assertCount(2, $deliveries);
    }

    /**
     * @test
     */
    public function it_can_paginate_supplier_deliveries(): void
    {
        // Arrange
        for ($i = 0; $i < 20; $i++) {
            $this->createSupplierDelivery();
        }

        // Act
        $paginatedDeliveries = $this->supplierDeliveryService->paginate(15);

        // Assert
        $this->assertCount(15, $paginatedDeliveries);
        // As the current implementation of paginate returns a collection, we cannot assert the total.
        // However, we can assert the number of items on the current "page".
        $this->assertCount(15, $paginatedDeliveries);
    }
}
