<?php

namespace App\Listeners;

use App\DTOs\Order\OrderItemDTO;
use App\Events\OrderCreatedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AddOrderItemsToOrderListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(OrderCreatedEvent $event): void
    {
        // Prevent duplicate execution by checking if items already exist
        if ($event->order->items()->count() > 0) {
            return;
        }

        $itemsForCreation = array_map(function (OrderItemDTO $dto) {
            $data = $dto->toArray();

            // Map 'option' field to 'bottle_type' for the database
            if (isset($data['option'])) {
                $data['bottle_type'] = $data['option'];
                unset($data['option']);
            }

            return $data;
        }, $event->orderItemsData);

        $event->order->items()->createMany($itemsForCreation);
    }
}
