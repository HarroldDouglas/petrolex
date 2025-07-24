<?php

namespace Tests\Feature\Services\User;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\DistributionCenter;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $userService;
    private DistributionCenter $distributionCenter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = $this->app->make(UserService::class);
        
        // Create geographical dependencies with correct structure
        $country = \App\Models\Geography\Country::factory()->create();
        $city = \App\Models\Geography\City::factory()->create(['country_id' => $country->id]);
        $municipality = \App\Models\Geography\Municipality::factory()->create(['city_id' => $city->id]);
        $neighborhood = \App\Models\Geography\Neighborhood::factory()->create(['municipality_id' => $municipality->id]);
        
        $this->distributionCenter = DistributionCenter::factory()->create([
            'neighborhood_id' => $neighborhood->id,
        ]);
        
        // Create roles
        $this->createRoles();
    }

    private function createRoles(): void
    {
        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value]);
        }
    }

    private function createUserData(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone_number' => '237699999999',
            'password' => 'password123',
            'role' => UserRole::CUSTOMER()->value,
            'is_active' => true,
            'distribution_center_ids' => [$this->distributionCenter->id],
        ], $overrides);
    }

    /**
     * @test
     */
    public function it_can_create_user_without_image(): void
    {
        // Arrange
        $userData = $this->createUserData();

        // Act
        $createdUser = $this->userService->createWithMedia($userData);

        // Assert
        $this->assertInstanceOf(User::class, $createdUser);
        $this->assertDatabaseHas('users', [
            'id' => $createdUser->id,
            'email' => 'john.doe@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone_number' => '237699999999',
            'is_active' => true,
        ]);
        $this->assertTrue($createdUser->hasRole(UserRole::CUSTOMER()->value));
    }

    /**
     * @test
     */
    public function it_can_create_user_with_image(): void
    {
        // Arrange
        Storage::fake('public');
        $image = UploadedFile::fake()->image('avatar.jpg');
        $userData = $this->createUserData(['image' => $image]);

        // Act
        $createdUser = $this->userService->createWithMedia($userData);

        // Assert
        $this->assertInstanceOf(User::class, $createdUser);
        $this->assertDatabaseHas('users', [
            'id' => $createdUser->id,
            'email' => 'john.doe@example.com',
        ]);
        $this->assertCount(1, $createdUser->getMedia('images'));
    }

    /**
     * @test
     */
    public function it_can_find_user_by_id(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $foundUser = $this->userService->find($user->id);

        // Assert
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->id, $foundUser->id);
        $this->assertEquals($user->email, $foundUser->email);
    }

    /**
     * @test
     */
    public function it_returns_null_when_user_not_found(): void
    {
        // Act
        $foundUser = $this->userService->find(999);

        // Assert
        $this->assertNull($foundUser);
    }

    /**
     * @test
     */
    public function it_can_update_user_without_image(): void
    {
        // Arrange
        $user = User::factory()->create();
        $updateData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
        ];

        // Act
        $updatedUser = $this->userService->update($user, $updateData);

        // Assert
        $this->assertEquals('Jane', $updatedUser->first_name);
        $this->assertEquals('Smith', $updatedUser->last_name);
        $this->assertEquals('jane.smith@example.com', $updatedUser->email);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
        ]);
    }

    /**
     * @test
     */
    public function it_can_update_user_with_image(): void
    {
        // Arrange
        Storage::fake('public');
        $user = User::factory()->create();
        $image = UploadedFile::fake()->image('new-avatar.jpg');
        $updateData = [
            'first_name' => 'Jane',
            'image' => $image,
        ];

        // Act
        $updatedUser = $this->userService->update($user, $updateData);

        // Assert
        $this->assertEquals('Jane', $updatedUser->first_name);
        $this->assertCount(1, $updatedUser->getMedia('images'));
    }

    /**
     * @test
     */
    public function it_can_update_user_password(): void
    {
        // Arrange
        $user = User::factory()->create(['password' => Hash::make('oldpassword')]);
        $updateData = ['password' => 'newpassword123'];

        // Act  
        $updatedUser = $this->userService->update($user, $updateData);

        // Assert
        $this->assertTrue(Hash::check('newpassword123', $updatedUser->password));
    }

    /**
     * @test
     */
    public function it_ignores_empty_password_on_update(): void
    {
        // Arrange
        $originalPassword = Hash::make('password123');
        $user = User::factory()->create(['password' => $originalPassword]);
        $updateData = [
            'first_name' => 'Jane',
            'password' => '',
        ];

        // Act
        $updatedUser = $this->userService->update($user, $updateData);

        // Assert
        $this->assertEquals('Jane', $updatedUser->first_name);
        $this->assertEquals($originalPassword, $updatedUser->password);
    }

    /**
     * @test
     */
    public function it_returns_user_unchanged_when_no_attributes_provided(): void
    {
        // Arrange
        $user = User::factory()->create();
        $originalName = $user->first_name;

        // Act
        $result = $this->userService->update($user, []);

        // Assert
        $this->assertEquals($originalName, $result->first_name);
        $this->assertEquals($user->id, $result->id);
    }

    /**
     * @test
     */
    public function it_can_delete_user(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $result = $this->userService->delete($user);

        // Assert
        $this->assertTrue($result);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /**
     * @test
     */
    public function it_can_get_all_customers(): void
    {
        // Arrange
        $customerUser1 = User::factory()->create();
        $customerUser1->assignRole(UserRole::CUSTOMER()->value);
        Customer::factory()->create(['user_id' => $customerUser1->id]);

        $customerUser2 = User::factory()->create();
        $customerUser2->assignRole(UserRole::CUSTOMER()->value);
        Customer::factory()->create(['user_id' => $customerUser2->id]);

        $adminUser = User::factory()->create();
        $adminUser->assignRole(UserRole::ADMIN()->value);

        // Act
        $customers = $this->userService->getAllCustomers();

        // Assert
        $this->assertCount(2, $customers);
        $customers->each(function ($user) {
            $this->assertTrue($user->hasRole(UserRole::CUSTOMER()->value));
        });
    }

    /**
     * @test
     */
    public function it_throws_exception_for_invalid_model_type_on_update(): void
    {
        // Arrange
        $customer = Customer::factory()->create();

        // Assert & Act
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected User model');
        
        $this->userService->update($customer, ['first_name' => 'Test']);
    }

    /**
     * @test
     */
    public function it_can_create_user_with_different_roles(): void
    {
        // Arrange
        $adminData = $this->createUserData([
            'email' => 'admin@example.com',
            'role' => UserRole::ADMIN()->value,
        ]);

        // Act
        $adminUser = $this->userService->createWithMedia($adminData);

        // Assert
        $this->assertTrue($adminUser->hasRole(UserRole::ADMIN()->value));
        $this->assertDatabaseHas('users', [
            'id' => $adminUser->id,
            'email' => 'admin@example.com',
        ]);
    }

    /**
     * @test
     */
    public function it_handles_media_functionality_correctly(): void
    {
        // Arrange
        Storage::fake('public');
        $user = User::factory()->create();
        $image = UploadedFile::fake()->image('avatar.jpg');

        // Act - Add media through service
        $updatedUser = $this->userService->update($user, ['image' => $image]);

        // Assert - Verify media was attached
        $this->assertCount(1, $updatedUser->getMedia('images'));
        $this->assertEquals('images', $updatedUser->getMedia('images')->first()->collection_name);
    }
}