<?php

namespace App\Listeners;

use App\Events\UserDeletedEvent;
use Illuminate\Support\Facades\Log;

class LogUserDeleted
{
    /**
     * Handle the event.
     */
    public function handle(UserDeletedEvent $event): void
    {
        $user = $event->user;

        Log::channel('user-dynamic')->info("User deleted: {$user->full_name} (ID: {$user->id})", [
            'user_id' => $user->id,
            'deleted_at' => now()->toDateTimeString(),
        ]);
    }
}
