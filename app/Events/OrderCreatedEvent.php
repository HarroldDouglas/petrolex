<?php

namespace App\Events;

use App\DTOs\Order\OrderItemDTO;
use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  array<OrderItemDTO>  $orderItemsData
     */
    public function __construct(
        public Order $order,
        public array $orderItemsData
    ) {}
}
