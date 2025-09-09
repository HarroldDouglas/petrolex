<?php

namespace App\Listeners;

use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Notifications\OrderStatusChangedNotification;
use Doctrine\Common\Annotations\Annotation\Enum;
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

        $recipients = $this->getRecipients($order);

        $validRecipients = $this->filterValidRecipients($recipients);

        Log::info('Recipients found', ['count' => $validRecipients->count(),
            'recipients' => $validRecipients->pluck('email')->toArray(),
        ]);

        if ($validRecipients->isNotEmpty()) {
            $this->sendNotification($validRecipients, $order, $event->oldStatus, $event->newStatus);
            Log::info('Notifications sent successfully');
        } else {
            Log::warning('No valid recipients found for order notification', ['order_id' => $order->id]);
        }
    }

    private function getRecipients($order)
    {
        $recipients = collect();

        $this->addCustomerToRecipients($recipients, $order);
        $this->addDistributionCenterManagerToRecipients($recipients, $order);
        $this->addDeliveryPersonToRecipientsIfNeeded($recipients, $order);

        return $recipients;
    }

    /**
     * Add the customer to the recipients list if they have a valid email.
     */
    private function addCustomerToRecipients($recipients, $order): void
    {
        $customerUser = $order->customer?->user;
        if ($customerUser?->email) {
            $recipients->push($customerUser);
            Log::info('Added customer to recipients', [
                'email' => $customerUser->email,
                'order_id' => $order->id,
            ]);
        } else {
            Log::warning('Customer not found or has no email', [
                'order_id' => $order->id,
                'has_customer' => (bool) $order->customer,
                'has_user' => $order->customer ? (bool) $order->customer->user : false,
                'has_email' => $order->customer && $order->customer->user ? (bool) $order->customer->user->email : false,
            ]);
        }
    }

    /**
     * Add the distribution center manager to the recipients list if they have a valid email.
     */
    private function addDistributionCenterManagerToRecipients($recipients, $order): void
    {
        $manager = $order->distributionCenter?->manager;
       
        if ($manager?->email) {
            $recipients->push($manager);
            Log::info('Added distribution center manager to recipients', [
                'email' => $manager->email,
                'order_id' => $order->id,
                'distribution_center_id' => $order->distributionCenter->id,
            ]);
        } else {
            Log::warning('Distribution center manager not found or has no email', [
                'order_id' => $order->id,
                'has_distribution_center' => (bool) $order->distributionCenter,
                'has_manager' => $order->distributionCenter ? (bool) $order->distributionCenter->manager : false,
                'has_email' => $manager ? (bool) $manager->email : false,
                'manager_id' => $manager ? $manager->id : null,
            ]);
        }
    }

    /**
     * Add the delivery person to the recipients list if the order status is changing to in_progress
     * and they have a valid email.
     */
    private function addDeliveryPersonToRecipientsIfNeeded($recipients, $order): void
    {
        // Only add delivery person if order is In Progress and has one assigned and has a valid email
        if($order->status === OrderStatus::InProgress()->value) {
            $deliveryPerson = $order->deliveryPerson?->user;
            if ($deliveryPerson?->email) {
                $recipients->push($deliveryPerson);
                Log::info('Added delivery person to recipients', [
                    'email' => $deliveryPerson->email,
                    'order_id' => $order->id,
                ]);
            } else {
                Log::info('Delivery person not assigned or has no email', [
                    'order_id' => $order->id,
                    'has_delivery_person' => (bool) $order->deliveryPerson,
                    'has_user' => $order->deliveryPerson ? (bool) $order->deliveryPerson->user : false,
                        'has_email' => $order->deliveryPerson && $order->deliveryPerson->user ? (bool) $order->deliveryPerson->user->email : false,
                ]);
            }

        }
    }

    /**
     * Filter recipients to only include valid users with email addresses.
     */
    private function filterValidRecipients($recipients)
    {
        return $recipients->filter(function ($user) {
            return $user && $user->email;
        });
    }

    /**
     * Send the notification to all valid recipients.
     */
    private function sendNotification($recipients, $order, $oldStatus, $newStatus): void
    {
        Notification::send(
            $recipients,
            new OrderStatusChangedNotification(
                $order,
                $oldStatus,
                $newStatus
            )
        );
    }
}
