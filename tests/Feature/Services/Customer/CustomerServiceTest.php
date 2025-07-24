<?php

namespace Tests\Feature\Services\Customer;

use App\Models\Customer;
use App\Models\User;
use App\Services\Customer\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    private CustomerService $customerService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerService = $this->app->make(CustomerService::class);
        $this->user = User::factory()->create();
    }

    private function createCustomer(array $overrides = []): Customer
    {
        return Customer::factory()->create(array_merge([
            'user_id' => $this->user->id,
            'current_balance' => 0.00,
        ], $overrides));
    }

    /**
     * @test
     */
    public function it_can_create_a_customer(): void
    {
        // Arrange
        $customerData = [
            'user_id' => $this->user->id,
            'current_balance' => 100.00,
        ];

        // Act
        $createdCustomer = $this->customerService->create($customerData);

        // Assert
        $this->assertInstanceOf(Customer::class, $createdCustomer);
        $this->assertDatabaseHas('customers', [
            'id' => $createdCustomer->id,
            'user_id' => $this->user->id,
            'current_balance' => 100.00,
        ]);
    }

    /**
     * @test
     */
    public function it_can_find_a_customer(): void
    {
        // Arrange
        $customer = $this->createCustomer();

        // Act
        $foundCustomer = $this->customerService->find($customer->id);

        // Assert
        $this->assertInstanceOf(Customer::class, $foundCustomer);
        $this->assertEquals($customer->id, $foundCustomer->id);
    }

    /**
     * @test
     */
    public function it_can_update_a_customer(): void
    {
        // Arrange
        $customer = $this->createCustomer();
        $updateData = ['current_balance' => 200.00];

        // Act
        $updatedCustomer = $this->customerService->update($customer, $updateData);

        // Assert
        $this->assertEquals(200.00, $updatedCustomer->current_balance);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'current_balance' => 200.00,
        ]);
    }

    /**
     * @test
     */
    public function it_can_delete_a_customer(): void
    {
        // Arrange
        $customer = $this->createCustomer();

        // Act
        $result = $this->customerService->delete($customer);

        // Assert
        $this->assertTrue($result);
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    /**
     * @test
     */
    public function it_can_get_all_customers(): void
    {
        // Arrange
        $this->createCustomer();
        $this->createCustomer();

        // Act
        $customers = $this->customerService->getAll();

        // Assert
        $this->assertCount(2, $customers);
    }

    /**
     * @test
     */
    public function it_can_paginate_customers(): void
    {
        // Arrange
        for ($i = 0; $i < 20; $i++) {
            $this->createCustomer();
        }

        // Act
        $paginatedCustomers = $this->customerService->paginate(15);

        // Assert
        $this->assertCount(15, $paginatedCustomers);
    }

    /**
     * @test
     */
    public function it_returns_correct_media_fields(): void
    {
        // Act
        $mediaFields = $this->customerService->getMediaFields();

        // Assert
        $this->assertEquals(['picture'], $mediaFields);
    }
}
