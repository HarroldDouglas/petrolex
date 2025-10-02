<?php

namespace App\Listeners\Role;

use App\Events\Role\RoleDeletingEvent;
use Illuminate\Support\Facades\Log;

class DetachPermissionsFromDeletingRoleListener
{
    /**
     * Handle the event - detach all permissions from role before deletion.
     */
    public function handle(RoleDeletingEvent $event): void
    {
        try {
            $role = $event->role;
            
            Log::info("DetachPermissionsFromDeletingRoleListener: Starting permission detachment", [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'permissions_count' => $role->permissions()->count()
            ]);

            // Get the permissions before detachment for logging
            $permissionNames = $role->permissions->pluck('name')->toArray();

            // Detach all permissions from the role
            $detachedCount = $role->permissions()->detach();

            Log::info("DetachPermissionsFromDeletedRoleListener: Permissions successfully detached", [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'detached_permissions_count' => $detachedCount,
                'detached_permissions' => $permissionNames
            ]);

        } catch (\Exception $e) {
            Log::error("DetachPermissionsFromDeletedRoleListener: Failed to detach permissions", [
                'role_id' => $event->role->id,
                'role_name' => $event->role->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw the exception to ensure the transaction fails
            throw $e;
        }
    }
}