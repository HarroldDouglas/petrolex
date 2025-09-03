<?php

declare(strict_types=1);

namespace App\Listeners;

use App\DTOs\BottleMovement\CreateBottleMovementDTO;
use App\Enums\BottleMovementType;
use App\Enums\BottleStatus;
use App\Events\OrderDeliveredEvent;
use App\Repositories\Contracts\BottleMovementRepositoryInterface;
use App\Repositories\Contracts\BottleRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

final class UpdateBottleStatusAndMovementOnOrderDelivered implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly BottleRepositoryInterface $bottleRepository,
        private readonly BottleMovementRepositoryInterface $bottleMovementRepository
    ) {}

    public function handle(OrderDeliveredEvent $event): void
    {
        $order = $event->order;

        $order->load('items.bottles');

        foreach ($order->items as $orderItem) {
            foreach ($orderItem->bottles()->get() as $bottle) {
                /** @var \App\Models\Bottle $bottle */
                try {
                    $this->bottleRepository->update($bottle, ['status' => BottleStatus::WITH_CLIENT()]);

                    $dto = new CreateBottleMovementDTO(
                        bottleId: $bottle->id,
                        type: BottleMovementType::DELIVERY_TO_CUSTOMER(),
                        userId: $order->delivery_person_id ?? auth()->id(),
                        movementDate: now(),
                        notes: 'Bouteille livrée au client avec la commande '.$order->order_number,
                        customerId: $order->customer_id,
                        orderId: $order->id,
                    );
                    Log::info('Created bottle movement for bottle ', [
                        'orderId' => $order->id,
                        'movement' => $dto->toArray(),
                    ]);
                    $this->bottleMovementRepository->create($dto->toArray());
                } catch (\Exception $e) {
                    Log::error('Failed to update bottle status or create movement for bottle '.$bottle->id.' on order '.$order->id, [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }
    }
}
