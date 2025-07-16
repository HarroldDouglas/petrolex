<?php

namespace App\Events;

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
     * @param array<int, array{product_category_id: int, quantity: int, unit_price: float, total_price: float, option: ?string}> $orderItemsData
     */
    public function __construct(
        public Order $order,
        public array $orderItemsData
    ) {}
}