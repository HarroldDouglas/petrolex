<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\BottleMovementType;
use App\Enums\BottleStatus;
use App\Events\EmptyBottleReturnedEvent;
use App\Repositories\Contracts\BottleMovementRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class CreateBottleMovementForReturnedBottleListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly BottleMovementRepositoryInterface $bottleMovementRepository
    ) {}

    /**
     * Handle the event.
     */
    public function handle(EmptyBottleReturnedEvent $event): void
    {

        $this->bottleMovementRepository->create([
            'bottle_id' => $event->bottle->id,
            'movement_type' => BottleStatus::IN_STOCK(),
            'distribution_center_id' => $event->order->distribution_center_id,
            'type' => BottleMovementType::RETURN_FROM_CUSTOMER(),
            'notes' => 'Empty bottle returned for order #'.$event->order->order_number,
            'user_id' => auth()->id(),
            'customer_id' => $event->order->customer_id,
        ]);
    }
}
