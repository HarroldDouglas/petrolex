<?php

namespace App\Listeners\Role;

use App\Events\Role\RolePermissionUpdatedEvent;
use Illuminate\Support\Facades\Log;

class AssignPermissionsToRoleListener
{

    /**
     * Handle the event.
     */
    public function handle(RolePermissionUpdatedEvent $event): void
    {
        try {
            // Log before syncing
            Log::info("Starting permission sync for role: {$event->role->name}", [
                'role_id' => $event->role->id,
                'current_permissions' => $event->role->permissions->pluck('name')->toArray(),
                'new_permissions' => $event->permissions,
                'permissions_count' => count($event->permissions)
            ]);

            $event->role->syncPermissions($event->permissions);
            
            // Log after syncing to confirm
            $event->role->refresh();
            Log::info("Permissions successfully synced to role: {$event->role->name}", [
                'role_id' => $event->role->id,
                'final_permissions' => $event->role->permissions->pluck('name')->toArray(),
                'permissions_count' => count($event->role->permissions)
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to assign permissions to role: {$event->role->name}", [
                'role_id' => $event->role->id,
                'error' => $e->getMessage(),
                'permissions' => $event->permissions
            ]);
            
            throw $e;
        }
    }
}