<?php

namespace App\Console\Commands;

use App\Services\Role\RoleService;
use Illuminate\Console\Command;

class TestRoleDeleteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role:test-delete {roleId : The ID of the role to test delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test role deletion functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roleId = $this->argument('roleId');
        
        $roleService = app(\App\Services\Role\RoleService::class);
        
        try {
            $role = $roleService->find($roleId);
            
            if (!$role) {
                $this->error("Role with ID {$roleId} not found.");
                return 1;
            }
            
            $this->info("Role found: {$role->name}");
            $this->info("Permissions: " . $role->permissions()->count());
            $this->info("Users: " . $role->users()->count());
            
            if ($role->permissions()->count() > 0) {
                $this->info("Permissions will be automatically detached by event listener before deletion.");
            }
            
            if ($role->users()->count() > 0) {
                $this->warn("Role has users assigned. Cannot delete.");
                return 1;
            }
            
            if ($this->confirm("Are you sure you want to delete role '{$role->name}'?")) {
                $deleted = $roleService->delete($role);
                
                if ($deleted) {
                    $this->info("Role '{$role->name}' deleted successfully!");
                } else {
                    $this->error("Failed to delete role.");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
