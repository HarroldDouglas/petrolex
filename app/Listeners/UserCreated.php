<?php

namespace App\Listeners;

use App\Enums\UserRole;
use App\Events\UserCreatedEvent;
use Illuminate\Support\Facades\Log;

class UserCreated
{
    /**
     * Handle the event.
     */
    public function handle(UserCreatedEvent $event): void
    {
        $user = $event->user;
        $distribution_centers = $event->distribution_centers;
        $role = $event->role;

        $user->assignRole($role);
        $user->accessibleDistributionCenters()->sync($distribution_centers);
        if ($role === UserRole::DELIVERY_PERSON()->value) {
            $user->deliveryPerson()->create();
        } elseif ($role === UserRole::CUSTOMER()->value) {
            $user->customer()->create(['current_balance' => 0]);
        }

        $user->save();

        Log::channel('user-dynamic')
            ->info("Role and distribution center added to User: {$user->full_name} (ID: {$user->id})", [
                'user_id' => $user->id,
                'user_full_name' => $user->full_name,
                'created_at' => now()->toDateTimeString(),
                'role' => $role,
            ]);
    }
}
