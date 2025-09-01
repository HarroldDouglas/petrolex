<?php

namespace Tests\Feature\Services\Accessory;

use App\Models\AccessoryType;
use App\Services\Accessory\AccessoryTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessoryTypeServiceTest extends TestCase
{
    use RefreshDatabase;

    private AccessoryTypeService $accessoryTypeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->accessoryTypeService = $this->app->make(AccessoryTypeService::class);
    }

    private function createAccessoryType(array $overrides = []): AccessoryType
    {
        return AccessoryType::factory()->create($overrides);
    }

    /**
     * Test method
     */
    public function test_it_can_create_an_accessory_type(): void
    {
        // Arrange
        $accessoryTypeData = [
            'name' => 'Test Accessory Type',
            'price' => 100.00,
            'description' => 'A test accessory type',
            'is_active' => true,
        ];

        // Act
        $createdAccessoryType = $this->accessoryTypeService->create($accessoryTypeData);

        // Assert
        $this->assertInstanceOf(AccessoryType::class, $createdAccessoryType);
        $this->assertDatabaseHas('accessory_types', [
            'id' => $createdAccessoryType->id,
            'name' => 'Test Accessory Type',
        ]);
    }

    /**
     * Test method
     */
    public function test_it_can_find_an_accessory_type(): void
    {
        // Arrange
        $accessoryType = $this->createAccessoryType();

        // Act
        $foundAccessoryType = $this->accessoryTypeService->find($accessoryType->id);

        // Assert
        $this->assertInstanceOf(AccessoryType::class, $foundAccessoryType);
        $this->assertEquals($accessoryType->id, $foundAccessoryType->id);
    }

    /**
     * Test method
     */
    public function test_it_can_update_an_accessory_type(): void
    {
        // Arrange
        $accessoryType = $this->createAccessoryType();
        $updateData = ['name' => 'Updated Accessory Type'];

        // Act
        $updatedAccessoryType = $this->accessoryTypeService->update($accessoryType, $updateData);

        // Assert
        $this->assertEquals('Updated Accessory Type', $updatedAccessoryType->name);
        $this->assertDatabaseHas('accessory_types', [
            'id' => $accessoryType->id,
            'name' => 'Updated Accessory Type',
        ]);
    }

    /**
     * Test method
     */
    public function test_it_can_delete_an_accessory_type(): void
    {
        // Arrange
        $accessoryType = $this->createAccessoryType();

        // Act
        $result = $this->accessoryTypeService->delete($accessoryType);

        // Assert
        $this->assertTrue($result);
        $this->assertSoftDeleted('accessory_types', ['id' => $accessoryType->id]);
    }

    /**
     * Test method
     */
    public function test_it_can_get_all_accessory_types(): void
    {
        // Arrange
        $this->createAccessoryType();
        $this->createAccessoryType();

        // Act
        $accessoryTypes = $this->accessoryTypeService->getAll();

        // Assert
        $this->assertCount(2, $accessoryTypes);
    }

    /**
     * Test method
     */
    public function test_it_can_paginate_accessory_types(): void
    {
        // Arrange
        for ($i = 0; $i < 20; $i++) {
            $this->createAccessoryType();
        }

        // Act
        $paginatedAccessoryTypes = $this->accessoryTypeService->paginate(15);

        // Assert
        $this->assertCount(15, $paginatedAccessoryTypes);
    }

    /**
     * Test method
     */
    public function test_it_can_get_accessory_stats(): void
    {
        // Arrange
        $this->createAccessoryType(['is_active' => true]);
        $this->createAccessoryType(['is_active' => true]);
        $this->createAccessoryType(['is_active' => false]);

        // Act
        $stats = $this->accessoryTypeService->getAccessoryStats();

        // Assert
        $this->assertEquals(2, $stats->activeCount);
        $this->assertEquals(1, $stats->inactiveCount);
        $this->assertEquals(3, $stats->totalCount);
    }
}
