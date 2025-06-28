<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasNotifications
{
    public function unreadNotifications(): MorphMany
    {
        return $this->notifications()->unread();
    }

    public function readNotifications(): MorphMany
    {
        return $this->notifications()->read();
    }

    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->unreadNotifications()->count();
    }

    public function markAllNotificationsAsRead(): void
    {
        $this->unreadNotifications()->update(['read_at' => now()]);
    }
}
