<?php

namespace App\Listeners;

use App\Events\PasswordUpdatedEvent;
use App\Notifications\PasswordUpdatedNotification;
use Illuminate\Support\Facades\Notification;

final class SendPasswordUpdatedNotification
{
    public function handle(PasswordUpdatedEvent $event): void
    {
        Notification::send($event->user, new PasswordUpdatedNotification($event->user));
    }
}
