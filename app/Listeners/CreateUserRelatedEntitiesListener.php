<?php

namespace App\Listeners;

use App\Enums\UserRole;
use App\Events\UserCreatedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CreateUserRelatedEntitiesListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(UserCreatedEvent $event): void
    {
        $user = $event->user;
        $distribution_center_ids = $event->distribution_center_ids;
        $role = $event->role;

        $user->assignRole($role);
        $user->accessibleDistributionCenters()->sync($distribution_center_ids);
        if ($role === UserRole::DELIVERY_PERSON()->value) {
            $user->deliveryPerson()->firstOrCreate([]);
            $user->email_verified_at = now();
            $user->phone_verified_at = now();
        } elseif ($role === UserRole::CUSTOMER()->value) {
            $user->customer()->firstOrCreate([], ['current_balance' => 0]);
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
