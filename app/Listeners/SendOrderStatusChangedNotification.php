<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendOrderStatusChangedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;
        $recipients = collect();

        // Add customer to recipients
        if ($order->customer && $order->customer->user && $order->customer->user->email) {
            $recipients->push($order->customer->user);
            Log::info('Added customer to recipients', ['email' => $order->customer->user->email]);
        } else {
            Log::warning('Customer not found or has no email', [
                'order_id' => $order->id,
                'has_customer' => (bool) $order->customer,
                'has_user' => $order->customer ? (bool) $order->customer->user : false,
                'has_email' => $order->customer && $order->customer->user ? (bool) $order->customer->user->email : false
            ]);
        }

        // Add distribution center manager to recipients
        $manager = $order->distributionCenter?->manager;
        if ($manager && $manager->user && $manager->user->email) {
            $recipients->push($manager->user);
            Log::info('Added manager to recipients', ['email' => $manager->user->email]);
        } else {
            Log::warning('Manager not found or has no email', [
                'order_id' => $order->id,
                'has_distribution_center' => (bool) $order->distributionCenter,
                'has_manager' => $order->distributionCenter ? (bool) $order->distributionCenter->manager : false,
                'has_user' => $manager ? (bool) $manager->user : false,
                'has_email' => $manager && $manager->user ? (bool) $manager->user->email : false
            ]);
        }

        // Filter recipients to ensure they have email addresses
        $validRecipients = $recipients->filter(function ($user) {
            return $user && $user->email;
        });

        Log::info('Recipients found', ['count' => $validRecipients->count()]);

        if ($validRecipients->isNotEmpty()) {
            Notification::send(
                $validRecipients,
                new OrderStatusChangedNotification(
                    $order,
                    $event->oldStatus,
                    $event->newStatus
                )
            );
            Log::info('Notifications sent successfully');
        } else {
            Log::warning('No valid recipients found for order notification', ['order_id' => $order->id]);
        }
    }
}
