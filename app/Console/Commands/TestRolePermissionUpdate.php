<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use App\Services\Role\RoleService;
use Illuminate\Support\Facades\Log;

class TestRolePermissionUpdate extends Command
{
    protected $signature = 'test:role-permissions {role_id}';
    protected $description = 'Test role permission update functionality';

    public function handle(RoleService $roleService)
    {
        $roleId = $this->argument('role_id');
        $role = Role::find($roleId);
        
        if (!$role) {
            $this->error("Role with ID {$roleId} not found");
            return;
        }

        $this->info("Testing role: {$role->name} (ID: {$role->id})");
        $this->info("Current permissions: " . $role->permissions->pluck('name')->implode(', '));

        // Test with a few permissions
        $testPermissions = ['roles.create', 'roles.edit'];
        
        $this->info("Updating role with permissions: " . implode(', ', $testPermissions));
        
        $data = [
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'permissions' => $testPermissions
        ];

        $updatedRole = $roleService->update($role, $data);
        
        // Refresh and check
        $updatedRole->refresh();
        $this->info("Permissions after update: " . $updatedRole->permissions->pluck('name')->implode(', '));
        
        return Command::SUCCESS;
    }
}