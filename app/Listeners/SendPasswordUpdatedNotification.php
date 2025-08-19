<?php

namespace App\Listeners;

use App\Events\PasswordUpdatedEvent;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PasswordUpdatedNotification;

final class SendPasswordUpdatedNotification
{
    public function handle(PasswordUpdatedEvent $event): void
    {
        Notification::send($event->user, new PasswordUpdatedNotification($event->user));
    }
}
