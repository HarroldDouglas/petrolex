<?php

namespace App\Events;

use App\Models\Bottle;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BottleStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Bottle $bottle,
        public string $status,
        public ?int $userId = null,
    ) {
        $this->bottle = $bottle;
        $this->status = $status;
        $this->userId = $userId ?? auth()->id();
    }
}
