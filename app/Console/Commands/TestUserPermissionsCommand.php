<?php

namespace App\Console\Commands;

use App\Enums\PermissionEnum;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\User\UserPermissionService;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class TestUserPermissionsCommand extends Command
{
    protected $signature = 'test:user-permissions {user_id?}';

    protected $description = 'Test user permission management functionality';

    public function handle(UserPermissionService $userPermissionService)
    {
        $userId = $this->argument('user_id');

        if ($userId) {
            $user = User::findOrFail($userId);
        } else {
            $user = User::whereHas('roles', function ($q) {
                $q->where('name', '!=', UserRole::CUSTOMER()->value)
                    ->where('name', '!=', UserRole::DELIVERY_PERSON()->value);
            })->first();
        }

        if (! $user) {
            $this->error('No suitable user found for testing');

            return;
        }

        $this->info("Testing permissions for user: {$user->full_name} (ID: {$user->id})");
        $this->info('Current role: '.($user->roles->first()?->name ?? 'No role'));

        if (! $userPermissionService->canManageUserPermissions($user)) {
            $this->error('This user cannot have permissions managed (likely customer or delivery person)');

            return;
        }

        // Test getting permission states
        $this->info("\n=== Getting Permission States ===");
        $permissionStates = $userPermissionService->getUserPermissionStates($user);

        foreach ($permissionStates as $module => $moduleData) {
            $this->info("\nModule: {$moduleData['module']}");
            foreach ($moduleData['permissions'] as $permission) {
                $status = "  - {$permission['label']} ({$permission['name']}): {$permission['state']}";
                if ($permission['checked']) {
                    $status .= ' [CHECKED]';
                }
                if ($permission['crossed']) {
                    $status .= ' [CROSSED]';
                }
                $this->line($status);
            }
        }

        // Test permission sources
        $this->info("\n=== Permission Sources ===");
        $sources = $user->getPermissionsBySource();
        $this->info('Role permissions: '.$sources['role_permissions']->count());
        $this->info('Direct permissions: '.$sources['direct_permissions']->count());
        $this->info('Revoked permissions: '.$sources['revoked_permissions']->count());

        // Interactive testing
        if ($this->confirm('Do you want to test permission toggling?')) {
            $this->testPermissionToggling($user, $userPermissionService);
        }
    }

    private function testPermissionToggling(User $user, UserPermissionService $userPermissionService)
    {
        $testPermission = PermissionEnum::USERS_VIEW()->value;

        $this->info("\n=== Testing Permission Toggle ===");
        $this->info("Test permission: {$testPermission}");

        $initialState = $user->getPermissionState($testPermission);
        $this->info("Initial state: {$initialState}");

        // Toggle permission
        $userPermissionService->toggleUserPermission($user, $testPermission);
        $user->refresh();

        $newState = $user->getPermissionState($testPermission);
        $this->info("After toggle: {$newState}");

        // Toggle back
        if ($this->confirm('Toggle back to original state?')) {
            $userPermissionService->toggleUserPermission($user, $testPermission);
            $user->refresh();

            $finalState = $user->getPermissionState($testPermission);
            $this->info("Final state: {$finalState}");
        }

        // Show effective permissions
        $this->info("\n=== Effective Permissions ===");
        $effectivePermissions = $userPermissionService->getEffectiveUserPermissions($user);
        $this->info('Total effective permissions: '.$effectivePermissions->count());

        if ($this->option('verbose')) {
            foreach ($effectivePermissions as $permission) {
                $this->line("  - {$permission}");
            }
        }
    }
}
