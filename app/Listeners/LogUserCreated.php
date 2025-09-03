<?php

namespace App\Listeners;

use App\Events\UserCreatedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogUserCreated implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(UserCreatedEvent $event): void
    {
        $user = $event->user;

        Log::channel('user-dynamic')
            ->info("User created: {$user->full_name} (ID: {$user->id})", [
                'user_id' => $user->id,
                'user_full_name' => $user->full_name,
                'created_at' => now()->toDateTimeString(),
            ]);
    }
}
