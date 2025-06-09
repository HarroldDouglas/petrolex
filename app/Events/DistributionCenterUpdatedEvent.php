<?php

namespace App\Events;

use App\Models\DistributionCenter;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DistributionCenterUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public DistributionCenter $distributionCenter,
        public array $changes
    ) {}
}
