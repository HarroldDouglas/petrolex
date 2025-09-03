<?php

namespace App\Listeners;

use App\Events\UserDeletedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogUserDeleted implements ShouldQueue
{
    use InteractsWithQueue;

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
