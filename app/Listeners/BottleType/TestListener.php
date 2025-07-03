<?php

namespace App\Listeners\BottleType;

use App\Events\BottleType\BottleTypeUpdatedEvent;
use Illuminate\Support\Facades\Log;

class TestListener
{
    public function handle(BottleTypeUpdatedEvent $event): void
    {
        Log::debug('=== TEST LISTENER fdsfsdfsd===');
    }
}
