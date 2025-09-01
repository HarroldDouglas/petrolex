<?php

namespace Tests\Feature\Services\Permissions;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Permissions\PermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    private PermissionService $permissionService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create all necessary roles
        foreach (UserRole::cases() as $role) {
            Role::create(['name' => $role->value]);
        }

        $this->permissionService = $this->app->make(PermissionService::class);
    }

    public function test_super_admin_can_see_all_roles(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(UserRole::SUPER_ADMIN()->value);

        $availableRoles = $this->permissionService->getAvailableRolesForUser($superAdmin);

        $this->assertCount(count(UserRole::cases()), $availableRoles);

        foreach (UserRole::cases() as $role) {
            $this->assertTrue($availableRoles->contains(fn ($r) => $r->value === $role->value));
        }
    }

    public function test_admin_can_see_all_roles_except_super_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::ADMIN()->value);

        $availableRoles = $this->permissionService->getAvailableRolesForUser($admin);

        $this->assertCount(count(UserRole::cases()) - 1, $availableRoles);
        $this->assertFalse($availableRoles->contains(fn ($role) => $role->value === UserRole::SUPER_ADMIN()->value));
        $this->assertTrue($availableRoles->contains(fn ($role) => $role->value === UserRole::ADMIN()->value));
        $this->assertTrue($availableRoles->contains(fn ($role) => $role->value === UserRole::MANAGER()->value));
    }

    public function test_manager_can_see_limited_roles(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole(UserRole::MANAGER()->value);

        $availableRoles = $this->permissionService->getAvailableRolesForUser($manager);

        $this->assertCount(3, $availableRoles);
        $this->assertTrue($availableRoles->contains(fn ($role) => $role->value === UserRole::CENTER_MANAGER()->value));
        $this->assertTrue($availableRoles->contains(fn ($role) => $role->value === UserRole::DELIVERY_PERSON()->value));
        $this->assertTrue($availableRoles->contains(fn ($role) => $role->value === UserRole::CUSTOMER()->value));
        $this->assertFalse($availableRoles->contains(fn ($role) => $role->value === UserRole::SUPER_ADMIN()->value));
        $this->assertFalse($availableRoles->contains(fn ($role) => $role->value === UserRole::ADMIN()->value));
    }

    public function test_center_manager_can_see_minimal_roles(): void
    {
        $centerManager = User::factory()->create();
        $centerManager->assignRole(UserRole::CENTER_MANAGER()->value);

        $availableRoles = $this->permissionService->getAvailableRolesForUser($centerManager);

        $this->assertCount(2, $availableRoles);
        $this->assertTrue($availableRoles->contains(fn ($role) => $role->value === UserRole::DELIVERY_PERSON()->value));
        $this->assertTrue($availableRoles->contains(fn ($role) => $role->value === UserRole::CUSTOMER()->value));
        $this->assertFalse($availableRoles->contains(fn ($role) => $role->value === UserRole::MANAGER()->value));
        $this->assertFalse($availableRoles->contains(fn ($role) => $role->value === UserRole::CENTER_MANAGER()->value));
    }

    public function test_delivery_person_cannot_see_any_roles(): void
    {
        $deliveryPerson = User::factory()->create();
        $deliveryPerson->assignRole(UserRole::DELIVERY_PERSON()->value);

        $availableRoles = $this->permissionService->getAvailableRolesForUser($deliveryPerson);

        $this->assertCount(0, $availableRoles);
    }

    public function test_customer_cannot_see_any_roles(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole(UserRole::CUSTOMER()->value);

        $availableRoles = $this->permissionService->getAvailableRolesForUser($customer);

        $this->assertCount(0, $availableRoles);
    }

    public function test_user_without_role_cannot_see_any_roles(): void
    {
        $user = User::factory()->create();

        $availableRoles = $this->permissionService->getAvailableRolesForUser($user);

        $this->assertCount(0, $availableRoles);
    }

    public function test_super_admin_can_assign_any_role(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(UserRole::SUPER_ADMIN()->value);

        foreach (UserRole::cases() as $role) {
            $canAssign = $this->permissionService->canAssignRole($superAdmin, $role->value);
            $this->assertTrue($canAssign, "Super admin should be able to assign {$role->value}");
        }
    }

    public function test_admin_can_assign_all_roles_except_super_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::ADMIN()->value);

        foreach (UserRole::cases() as $role) {
            $canAssign = $this->permissionService->canAssignRole($admin, $role->value);

            if ($role->value === UserRole::SUPER_ADMIN()->value) {
                $this->assertFalse($canAssign, 'Admin should not be able to assign SUPER_ADMIN');
            } else {
                $this->assertTrue($canAssign, "Admin should be able to assign {$role->value}");
            }
        }
    }

    public function test_manager_can_assign_specific_roles_only(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole(UserRole::MANAGER()->value);

        $allowedRoles = [
            UserRole::CENTER_MANAGER()->value,
            UserRole::DELIVERY_PERSON()->value,
            UserRole::CUSTOMER()->value,
        ];

        foreach (UserRole::cases() as $role) {
            $canAssign = $this->permissionService->canAssignRole($manager, $role->value);

            if (in_array($role->value, $allowedRoles)) {
                $this->assertTrue($canAssign, "Manager should be able to assign {$role->value}");
            } else {
                $this->assertFalse($canAssign, "Manager should not be able to assign {$role->value}");
            }
        }
    }

    public function test_center_manager_can_assign_specific_roles_only(): void
    {
        $centerManager = User::factory()->create();
        $centerManager->assignRole(UserRole::CENTER_MANAGER()->value);

        $allowedRoles = [
            UserRole::DELIVERY_PERSON()->value,
            UserRole::CUSTOMER()->value,
        ];

        foreach (UserRole::cases() as $role) {
            $canAssign = $this->permissionService->canAssignRole($centerManager, $role->value);

            if (in_array($role->value, $allowedRoles)) {
                $this->assertTrue($canAssign, "Center manager should be able to assign {$role->value}");
            } else {
                $this->assertFalse($canAssign, "Center manager should not be able to assign {$role->value}");
            }
        }
    }

    public function test_delivery_person_cannot_assign_any_roles(): void
    {
        $deliveryPerson = User::factory()->create();
        $deliveryPerson->assignRole(UserRole::DELIVERY_PERSON()->value);

        foreach (UserRole::cases() as $role) {
            $canAssign = $this->permissionService->canAssignRole($deliveryPerson, $role->value);
            $this->assertFalse($canAssign, "Delivery person should not be able to assign {$role->value}");
        }
    }

    public function test_customer_cannot_assign_any_roles(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole(UserRole::CUSTOMER()->value);

        foreach (UserRole::cases() as $role) {
            $canAssign = $this->permissionService->canAssignRole($customer, $role->value);
            $this->assertFalse($canAssign, "Customer should not be able to assign {$role->value}");
        }
    }

    public function test_can_assign_role_with_non_existent_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::ADMIN()->value);

        $canAssign = $this->permissionService->canAssignRole($admin, 'non_existent_role');

        $this->assertFalse($canAssign);
    }

    public function test_user_with_multiple_roles_gets_highest_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::DELIVERY_PERSON()->value);
        $user->assignRole(UserRole::ADMIN()->value);

        $availableRoles = $this->permissionService->getAvailableRolesForUser($user);

        $this->assertGreaterThan(0, $availableRoles->count());
        $this->assertTrue($availableRoles->contains(fn ($role) => $role->value === UserRole::ADMIN()->value));
        $this->assertFalse($availableRoles->contains(fn ($role) => $role->value === UserRole::SUPER_ADMIN()->value));
    }
}
