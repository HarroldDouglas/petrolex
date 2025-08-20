<?php

namespace App\Notifications;

use App\Mail\User\PasswordUpdatedMail;
use App\Models\User;
use Illuminate\Notifications\Notification;

final class PasswordUpdatedNotification extends Notification
{
    public function __construct(
        public readonly User $user,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): PasswordUpdatedMail
    {
        return new PasswordUpdatedMail($this->user);
    }
}
