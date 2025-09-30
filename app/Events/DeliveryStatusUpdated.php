<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\DeliveryTracking;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a delivery status is updated.
 *
 * This event broadcasts real-time updates about delivery status changes
 * to subscribed channels, providing comprehensive tracking information
 * including location data, timing details, and participant information.
 */
final class DeliveryStatusUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    private const CHANNEL_PREFIX_GENERAL = 'delivery-tracking';
    private const CHANNEL_PREFIX_SPECIFIC = 'delivery-';
    private const BROADCAST_EVENT_NAME = 'delivery-status-updated';
    private const EVENT_TYPE = 'status_update';
    private const COMPLETED_STATUS = 'completed';

    private const REQUIRED_RELATIONS = [
        'order.customer',
        'order.deliveryPerson',
        'order.deliveryAddress',
    ];

    /**
     * Create a new delivery status updated event instance.
     *
     * @param  DeliveryTracking  $delivery  The delivery tracking instance
     * @param  string|null  $previousStatus  The previous status before the update
     */
    public function __construct(
        private readonly DeliveryTracking $delivery,
        private readonly ?string $previousStatus = null
    ) {
        $this->loadRequiredRelations();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel(self::CHANNEL_PREFIX_SPECIFIC.$this->delivery->order->order_number),
        ];
    }

    /**
     * Get the broadcaster connection to use
     */
    public function broadcastVia(): array
    {
        return ['reverb'];
    }

    /**
     * Get the broadcast event name.
     */
    public function broadcastAs(): string
    {
        return self::BROADCAST_EVENT_NAME;
    }

    /**
     * Get the data to broadcast with the event.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            ...$this->getOrderInformation(),
            ...$this->getStatusInformation(),
            ...$this->getLocationInformation(),
            ...$this->getDeliveryMetrics(),
            ...$this->getParticipantInformation(),
            ...$this->getTimingInformation(),
            ...$this->getEventMetadata(),
        ];
    }

    /**
     * Load required relationships for the delivery instance.
     */
    private function loadRequiredRelations(): void
    {
        $this->delivery->load(self::REQUIRED_RELATIONS);
    }

    /**
     * Get basic order information.
     *
     * @return array<string, mixed>
     */
    private function getOrderInformation(): array
    {
        return [
            'id' => $this->delivery->id,
            'order_id' => $this->delivery->order_id,
            'order_number' => $this->delivery->order->order_number,
        ];
    }

    /**
     * Get current and previous status information.
     *
     * @return array<string, mixed>
     */
    private function getStatusInformation(): array
    {
        return [
            'status' => [
                'value' => $this->delivery->status->value,
                'label' => $this->delivery->status->label,
            ],
            'previous_status' => $this->previousStatus,
        ];
    }

    /**
     * Get location-related information including current position and destination.
     *
     * @return array<string, mixed>
     */
    private function getLocationInformation(): array
    {
        $driverPosition = $this->getDriverPosition();
        $destination = $this->getDestination();

        return [
            // Legacy format for backward compatibility
            'current_latitude' => $driverPosition['lat'],
            'current_longitude' => $driverPosition['lng'],
            'destination_latitude' => $destination['lat'],
            'destination_longitude' => $destination['lng'],

            // Preferred format
            'driver_position' => $driverPosition,
            'destination' => $destination,
            'destination_address' => $this->getDestinationAddress(),
        ];
    }

    /**
     * Get driver's current position coordinates.
     *
     * @return array<string, float>
     */
    private function getDriverPosition(): array
    {
        return [
            'lat' => (float) $this->delivery->driver_lat,
            'lng' => (float) $this->delivery->driver_lng,
        ];
    }

    /**
     * Get destination coordinates.
     *
     * @return array<string, float>
     */
    private function getDestination(): array
    {
        return [
            'lat' => (float) $this->delivery->order->destination_lat,
            'lng' => (float) $this->delivery->order->destination_lng,
        ];
    }

    /**
     * Get destination address with fallback handling.
     */
    private function getDestinationAddress(): ?string
    {
        return $this->delivery->order->deliveryAddress->full_address
            ?? $this->delivery->order->delivery_address
            ?? null;
    }

    /**
     * Get delivery performance metrics.
     *
     * @return array<string, mixed>
     */
    private function getDeliveryMetrics(): array
    {
        $estimatedDuration = $this->delivery->estimated_duration;

        return [
            'estimated_duration' => $estimatedDuration,
            'estimated_time_remaining' => $estimatedDuration,
            'eta' => $estimatedDuration,
            'distance_remaining' => (float) $this->delivery->distance_remaining,
            'current_speed' => (float) ($this->delivery->current_speed ?? 0),
        ];
    }

    /**
     * Get information about delivery participants (driver and customer).
     *
     * @return array<string, mixed>
     */
    private function getParticipantInformation(): array
    {
        return [
            ...$this->getDriverInformation(),
            ...$this->getCustomerInformation(),
        ];
    }

    /**
     * Get driver information with fallback handling.
     *
     * @return array<string, mixed>
     */
    private function getDriverInformation(): array
    {
        $deliveryPerson = $this->delivery->order->deliveryPerson;

        return [
            'driver_name' => $deliveryPerson->full_name ?? $deliveryPerson->name ?? null,
            'driver_phone' => $deliveryPerson->phone_number ?? null,
        ];
    }

    /**
     * Get customer information with fallback handling.
     *
     * @return array<string, mixed>
     */
    private function getCustomerInformation(): array
    {
        $customer = $this->delivery->order->customer;

        return [
            'customer_name' => $customer->full_name ?? $customer->name ?? null,
            'customer_phone' => $customer->phone_number ?? null,
        ];
    }

    /**
     * Get timing information for the delivery lifecycle.
     *
     * @return array<string, mixed>
     */
    private function getTimingInformation(): array
    {
        return [
            'started_at' => $this->delivery->started_at?->toISOString(),
            'delivered_at' => $this->delivery->delivered_at?->toISOString(),
            'updated_at' => $this->delivery->updated_at->toISOString(),
        ];
    }

    /**
     * Get event metadata for debugging and processing purposes.
     *
     * @return array<string, mixed>
     */
    private function getEventMetadata(): array
    {
        return [
            '_event_type' => self::EVENT_TYPE,
            '_timestamp' => Carbon::now()->toISOString(),
            '_is_completed' => $this->isDeliveryCompleted(),
        ];
    }

    /**
     * Check if the delivery is completed.
     */
    private function isDeliveryCompleted(): bool
    {
        return $this->delivery->status->value === self::COMPLETED_STATUS;
    }
}
