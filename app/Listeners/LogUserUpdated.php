<?php

namespace App\Listeners;

use App\Events\UserUpdatedEvent;
use Illuminate\Support\Facades\Log;

class LogUserUpdated
{
    /**
     * Handle the event.
     */
    public function handle(UserUpdatedEvent $event): void
    {
        $user = $event->user;
        $changes = $event->changes;

        Log::channel('user-dynamic')->info("User updated: {$user->full_name} (ID: {$user->id})", [
            'user_id' => $user->id,
            'changes' => $changes,
            'updated_at' => now()->toDateTimeString(),
        ]);
    }
}
