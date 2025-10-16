<?php

namespace App\Listeners\Role;

use App\Events\Role\RolePermissionsUpdatedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AssignPermissionsToRoleListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(RolePermissionsUpdatedEvent $event): void
    {
        try {
            $event->role->syncPermissions($event->permissions);
            
            Log::info("Permissions assigned to role: {$event->role->name}", [
                'role_id' => $event->role->id,
                'permissions_count' => count($event->permissions),
                'permissions' => $event->permissions
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