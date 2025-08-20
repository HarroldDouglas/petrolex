<?php

namespace App\Events\BottleType;

use App\DTOs\BottleType\CreateBottleTypeDTO;
use App\Models\BottleType;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BottleTypeCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly BottleType $bottleType,
        public readonly CreateBottleTypeDTO $data
    ) {}
}
