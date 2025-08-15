<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\BottleMovementType;
use App\Enums\BottleStatus;
use App\Events\EmptyBottleReturned;
use App\Repositories\Contracts\BottleMovementRepositoryInterface;

final class CreateBottleMovementForReturnedBottleListener
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly BottleMovementRepositoryInterface $bottleMovementRepository
    ) {}

    /**
     * Handle the event.
     */
    public function handle(EmptyBottleReturned $event): void
    {
        $movementType = BottleStatus::IN_STOCK();

        $this->bottleMovementRepository->create([
            'bottle_id' => $event->bottle->id,
            'movement_type' => $movementType,
            'distribution_center_id' => $event->order->distribution_center_id,
            'type' => BottleMovementType::RETURN_FROM_CUSTOMER(),
            'notes' => 'Empty bottle returned for order #'.$event->order->order_number,
            'user_id' => auth()->id(),
            'customer_id' => $event->order->customer_id,
        ]);
    }
}
