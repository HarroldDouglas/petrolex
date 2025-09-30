<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TestWebSocketEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $timestamp;

    public function __construct()
    {
        $this->message = 'Test WebSocket - '.now()->format('H:i:s');
        $this->timestamp = now()->toISOString();

        Log::info('TestWebSocketEvent triggered', [
            'message' => $this->message,
            'timestamp' => $this->timestamp,
        ]);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('test-channel'),
        ];
    }

    public function broadcastVia(): array
    {
        return ['reverb'];
    }

    public function broadcastAs(): string
    {
        return 'test-message';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'timestamp' => $this->timestamp,
            'random' => rand(1, 1000),
        ];
    }
}
