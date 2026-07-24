<?php

namespace App\Listeners\Order;

use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Listeners\BaseListener;
use App\Models\Order;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendOrderStatusChangedNotification extends BaseListener
{
    /**
     * Get unique identifiers for this event
     *
     * @param  OrderStatusChanged  $event
     * @return array{order_id: int, old_status: string|null, new_status: string, event_type: string}
     */
    protected function getEventIdentifiers($event): array
    {
        return [
            'order_id' => $event->order->id,
            'old_status' => $event->oldStatus?->value,
            'new_status' => $event->newStatus->value,
            'event_type' => 'order_status_changed_notification',
        ];
    }

    /**
     * Handle the event - Send notifications when order status changes
     *
     * @param  OrderStatusChanged  $event
     */
    protected function handleEvent($event): void
    {
        /** @var OrderStatusChanged $event */
        /** @var Order $order */
        $order = $event->order;
        $recipients = $this->getRecipients($order);
        $validRecipients = $this->filterValidRecipients($recipients);

        Log::info('Order Recipients found', ['count' => $validRecipients->count(),
            'recipients' => $validRecipients->pluck('email')->toArray(),
        ]);

        if ($validRecipients->isNotEmpty()) {
            $this->sendNotification($validRecipients, $order, $event->oldStatus, $event->newStatus);
            Log::info('Notifications sent successfully');
        } else {
            Log::warning('No valid recipients found for order notification', ['order_id' => $order->id]);
        }
    }

    private function getRecipients(Order $order)
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
     * Add the delivery person to the recipients list when status is relevant to them.
     */
    private function addDeliveryPersonToRecipientsIfNeeded($recipients, $order): void
    {
        $statusesRelevantToDeliveryPerson = [
            OrderStatus::PAID()->value,
            OrderStatus::PROCESSING()->value,
            OrderStatus::DELIVERED()->value,
            OrderStatus::CANCELLED()->value,
        ];

        if (! in_array($order->status, $statusesRelevantToDeliveryPerson)) {
            return;
        }

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
    private function sendNotification(Collection $recipients, Order $order, ?OrderStatus $oldStatus, ?OrderStatus $newStatus): void
    {
        // A notification must NEVER be able to break the caller. This listener
        // runs inside the payment DB transaction, and an uncaught mail/queue
        // failure here would roll back the whole payment confirmation.
        try {
            Notification::send(
                $recipients,
                new OrderStatusChangedNotification(
                    $order,
                    $oldStatus,
                    $newStatus
                )
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send order status notification (swallowed to protect the transaction)', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'new_status' => $newStatus?->value,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
