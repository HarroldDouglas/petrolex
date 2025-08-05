<?php

namespace App\Events;

use App\Models\DeliveryTracking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryStatusUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $delivery;
    public $previousStatus;

    public function __construct(DeliveryTracking $delivery, ?string $previousStatus = null)
    {
        // Charger les relations nécessaires
        $this->delivery = $delivery->load(['order.customer', 'order.deliveryPerson', 'order.deliveryAddress']);
        $this->previousStatus = $previousStatus;
    }

    public function broadcastOn()
    {
        return [
            new Channel('delivery-tracking'),
            new Channel('delivery-'.$this->delivery->order->order_number),
        ];
    }

    public function broadcastAs()
    {
        return 'delivery-status-updated';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->delivery->id,
            'order_id' => $this->delivery->order_id,
            'order_number' => $this->delivery->order->order_number,

            // Statut
            'status' => [
                'value' => $this->delivery->status->value,
                'label' => $this->delivery->status->label,
            ],
            'previous_status' => $this->previousStatus,

            // Position actuelle
            'current_latitude' => (float) $this->delivery->driver_lat,
            'current_longitude' => (float) $this->delivery->driver_lng,
            'driver_position' => [
                'lat' => (float) $this->delivery->driver_lat,
                'lng' => (float) $this->delivery->driver_lng,
            ],

            // Destination
            'destination_latitude' => (float) $this->delivery->order->destination_lat,
            'destination_longitude' => (float) $this->delivery->order->destination_lng,
            'destination' => [
                'lat' => (float) $this->delivery->order->destination_lat,
                'lng' => (float) $this->delivery->order->destination_lng,
            ],
            'destination_address' => $this->delivery->order->deliveryAddress->full_address ??
                                   $this->delivery->order->delivery_address ?? null,

            // Données de livraison
            'estimated_duration' => $this->delivery->estimated_duration,
            'estimated_time_remaining' => $this->delivery->estimated_duration,
            'eta' => $this->delivery->estimated_duration,
            'distance_remaining' => (float) $this->delivery->distance_remaining,
            'current_speed' => (float) ($this->delivery->current_speed ?? 0),

            // Informations du livreur
            'driver_name' => $this->delivery->order->deliveryPerson->full_name ??
                           $this->delivery->order->deliveryPerson->name ?? null,
            'driver_phone' => $this->delivery->order->deliveryPerson->phone_number ?? null,

            // Informations du client
            'customer_name' => $this->delivery->order->customer->full_name ??
                             $this->delivery->order->customer->name ?? null,
            'customer_phone' => $this->delivery->order->customer->phone_number ?? null,

            // Timing
            'started_at' => $this->delivery->started_at?->toISOString(),
            'delivered_at' => $this->delivery->delivered_at?->toISOString(),
            'updated_at' => $this->delivery->updated_at->toISOString(),

            // Métadonnées
            '_event_type' => 'status_update',
            '_timestamp' => now()->toISOString(),
            '_is_completed' => $this->delivery->status->value === 'completed',
        ];
    }
}
