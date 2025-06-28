<?php

namespace App\Livewire\Components;

use App\Enums\NotificationType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationHeader extends Component
{
    public Collection $notifications;
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        $user = auth()->user();

        // On ne charge que les notifications non lues
        $this->notifications = $user->unreadNotifications()
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                if (isset($notification->data['type'])) {
                    $notification->type = NotificationType::from($notification->data['type']);
                }

                return $notification;
            });

        $this->unreadCount = $this->notifications->count();
    }

    public function markAsRead(string $notificationId): void
    {
        Log::info('Tentative de marquer la notification comme lue', ['id' => $notificationId]);

        $notification = auth()->user()->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            Log::info('Notification marquée comme lue avec succès', ['id' => $notificationId]);
            $this->loadNotifications(); // Recharger la liste pour la mettre à jour
            $this->dispatch('notification-updated');
        } else {
            Log::warning('Notification non trouvée', ['id' => $notificationId]);
        }
    }

    public function openNotification(string $notificationId): void
    {
        $notification = $this->notifications->firstWhere('id', $notificationId);

        if ($notification) {
            $this->markAsRead($notificationId);

            if (isset($notification->data['url'])) {
                $this->redirect($notification->data['url'], navigate: true);
            }
        }
    }

    #[On('refresh-notifications')]
    public function refreshNotifications(): void
    {
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.components.notification-header', [
            'notifications' => $this->notifications,
            'unreadCount' => $this->unreadCount,
        ]);
    }
}
