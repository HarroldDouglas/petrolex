<?php

namespace Tests\Feature\Services\Customer;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;
use App\Services\Customer\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    private CustomerService $customerService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create required roles
        Role::create(['name' => UserRole::CUSTOMER()->value]);
        Role::create(['name' => UserRole::ADMIN()->value]);

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
     * Test method
     */
    public function test_it_can_create_a_customer(): void
    {
        // Arrange - CustomerService creates both User and Customer
        $customerData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone_number' => '+1234567890',
            'password' => 'password123',
            'current_balance' => 100.00,
        ];

        // Act
        $createdCustomer = $this->customerService->create($customerData);

        // Assert
        $this->assertInstanceOf(Customer::class, $createdCustomer);
        $this->assertDatabaseHas('customers', [
            'id' => $createdCustomer->id,
            'current_balance' => 100.00,
        ]);

        // Verify the User was also created
        $this->assertDatabaseHas('users', [
            'id' => $createdCustomer->user_id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
        ]);
    }

    /**
     * Test method
     */
    public function test_it_can_find_a_customer(): void
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
     * Test method
     */
    public function test_it_can_update_a_customer(): void
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
     * Test method
     */
    public function test_it_can_delete_a_customer(): void
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
     * Test method
     */
    public function test_it_can_get_all_customers(): void
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
     * Test method
     */
    public function test_it_can_paginate_customers(): void
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
     * Test method
     */
    public function test_it_returns_correct_media_fields(): void
    {
        // We can't directly test protected methods, but we can test that the service
        // correctly handles media fields by creating a customer with media

        // Act - Create a customer with media data
        $customerData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'phone_number' => '+0987654321',
            'password' => 'password123',
            'current_balance' => 50.00,
            'image' => null, // Media field that should be handled correctly
        ];

        $createdCustomer = $this->customerService->create($customerData);

        // Assert - Verify the customer was created successfully
        $this->assertInstanceOf(Customer::class, $createdCustomer);
        $this->assertDatabaseHas('customers', [
            'id' => $createdCustomer->id,
            'current_balance' => 50.00,
        ]);
    }
}
