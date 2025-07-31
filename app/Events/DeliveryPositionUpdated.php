<?php

namespace App\Events;

use App\Models\DeliveryTracking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryPositionUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $delivery;

    public function __construct(DeliveryTracking $delivery)
    {
        $this->delivery = $delivery;
    }

    public function broadcastOn()
    {
        return [
            new Channel('delivery-tracking'),
            new Channel('delivery-' . $this->delivery->order_number),
        ];
    }

    public function broadcastAs()
    {
        return 'position.updated';
    }

    public function broadcastWith()
    {
        return [
            'order_number' => $this->delivery->order_number,
            'driver_position' => [
                'lat' => $this->delivery->driver_lat,
                'lng' => $this->delivery->driver_lng
            ],
            'destination' => [
                'lat' => $this->delivery->destination_lat,
                'lng' => $this->delivery->destination_lng
            ],
            'estimated_duration' => $this->delivery->estimated_duration,
            'distance_remaining' => $this->delivery->distance_remaining,
            'status' => $this->delivery->status,
            'driver_name' => $this->delivery->driver_name,
            'customer_name' => $this->delivery->customer_name,
            'route_geometry' => $this->delivery->route_geometry
        ];
    }
}
