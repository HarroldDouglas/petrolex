<?php

namespace App\Listeners;

use App\Events\PasswordUpdatedEvent;
use App\Mail\User\PasswordUpdatedMail;
use Illuminate\Support\Facades\Mail;

final class SendPasswordUpdatedNotification
{
    /**
     * Handle the event.
     */
    public function handle(PasswordUpdatedEvent $event): void
    {
        Mail::to($event->user->email)->send(new PasswordUpdatedMail($event->user));
    }
}
