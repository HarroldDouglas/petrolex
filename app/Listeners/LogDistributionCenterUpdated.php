<?php

namespace App\Listeners;

use App\Events\DistributionCenterUpdatedEvent;
use Illuminate\Support\Facades\Log;

class LogDistributionCenterUpdated
{
    /**
     * Handle the event.
     */
    public function handle(DistributionCenterUpdatedEvent $event): void
    {
        $distributionCenter = $event->distributionCenter;
        $changes = $event->changes;

        Log::channel('user-dynamic')->info("Distribution Center updated: {$distributionCenter->name} (ID: {$distributionCenter->id})", [
            'distribution_center_id' => $distributionCenter->id,
            'changes' => $changes,
            'updated_at' => now()->toDateTimeString(),
        ]);
    }
}
