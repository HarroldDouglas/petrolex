<?php

namespace App\Events;

use App\Models\Bottle;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BottleStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Bottle $bottle;
    public string $status;
    public ?int $userId;
    public ?string $reason;

    public function __construct(
        Bottle $bottle, 
        string $status, 
        ?int $userId = null, 
    ) {
        $this->bottle = $bottle;
        $this->status = $status;
        $this->userId = $userId ?? auth()->id();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
