<?php

namespace App\Events;

use App\Enums\BottleStatus;
use App\Models\Bottle;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BottleStatusUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Bottle $bottle,
        public BottleStatus $status,
        public ?int $userId = null,
    ) {}
}
