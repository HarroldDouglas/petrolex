<?php

namespace App\Listeners;

use App\Enums\UserRole;
use App\Events\UserUpdatedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateUserRelatedEntitiesListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(UserUpdatedEvent $event): void
    {
        $user = $event->user;
        $distribution_center_ids = $event->distribution_center_ids;
        $role = $event->role;

        if ($role) {
            $user->syncRoles($role);
        }

        if (count($distribution_center_ids)) {
            $user->accessibleDistributionCenters()->sync($distribution_center_ids);
            if ($role === UserRole::DELIVERY_PERSON()->value) {
                $user->deliveryPerson()->create();
            }
        }

        Log::channel('user-dynamic')
            ->info("Role and distribution center added to User: {$user->full_name} (ID: {$user->id})", [
                'user_id' => $user->id,
                'user_full_name' => $user->full_name,
                'created_at' => now()->toDateTimeString(),
                'role' => $role,
            ]);
    }
}
