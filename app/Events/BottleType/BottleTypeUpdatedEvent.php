<?php

namespace App\Events\BottleType;

use App\DTOs\BottleType\UpdateBottleTypeDTO;
use App\Models\BottleType;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class BottleTypeUpdatedEvent
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public readonly BottleType $bottleType,
        public readonly UpdateBottleTypeDTO $data
    ) {}
}
