<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Bottle;
use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class EmptyBottleReturned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Bottle $bottle,
        public readonly Order $order,
        public readonly int $orderItemId
    ) {}
}
