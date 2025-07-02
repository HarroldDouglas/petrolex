<?php

namespace App\Listeners;

use App\Events\ProfileUpdatedEvent;
use Illuminate\Support\Facades\Log;

class UpdateProfileRelatedEntitiesListener
{
    /**
     * Handle the event.
     */
    public function handle(ProfileUpdatedEvent $event): void
    {
        $user = $event->user;

        Log::channel('user-dynamic')
            ->info("Profile informations updated for User: {$user->full_name} (ID: {$user->id})", [
                'user_id' => $user->id,
                'user_full_name' => $user->full_name,
                'created_at' => now()->toDateTimeString(),
            ]);
    }
}
