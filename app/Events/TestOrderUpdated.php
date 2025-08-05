<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestOrderUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $orderId;
    public $status;
    public $driverLocation;
    public $estimatedArrival;

    public function __construct($orderId, $status, $driverLocation, $estimatedArrival)
    {
        $this->orderId = $orderId;
        $this->status = $status;
        $this->driverLocation = $driverLocation;
        $this->estimatedArrival = $estimatedArrival;
    }

    public function broadcastOn()
    {
        return [
            new Channel('delivery-tracking'),
        ];
    }

    public function broadcastAs()
    {
        return 'order-updated';
    }

    public function broadcastWith()
    {
        return [
            'order_id' => $this->orderId,
            'status' => $this->status,
            'driver_location' => $this->driverLocation,
            'estimated_arrival' => $this->estimatedArrival,
        ];
    }
}
