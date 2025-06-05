<?php

namespace App\Listeners;

use App\DTOs\BottleMovement\CreateBottleMovementDTO;
use App\Enums\BottleMovementType;
use App\Enums\BottleStatus;
use App\Events\BottleStatusUpdated;
use App\Repositories\Eloquent\BottleMovementRepository;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CreateBottleMovement
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(protected BottleMovementRepository $bottleMovementRepository) {}

    /**
     * Handle the event.
     */
    public function handle(BottleStatusUpdated $event): void
    {
        try {
            $notes = null;
            $movementType = BottleStatus::IN_STOCK()->value;
            if ($event->status === BottleStatus::IN_STOCK()->value) {
                $movementType = BottleMovementType::SUPPLIER_DELIVERY();
            } elseif ($event->status === BottleStatus::LOST_STOLEN()->value) {
                $movementType = BottleMovementType::DECLARE_LOST_STOLEN();
            } elseif ($event->status === BottleStatus::WITH_DELIVERY_PERSON()->value) {
                $movementType = BottleMovementType::ASSIGNMENT_TO_DELIVERY();
            } elseif ($event->status === BottleStatus::WITH_CLIENT()->value) {
                $movementType = BottleMovementType::DELIVERY_TO_CUSTOMER();
            } elseif ($event->status === BottleStatus::RETURNED_TO_SUPPLIER()->value) {
                $movementType = BottleMovementType::RETURN_TO_SUPPLIER();
            }

            $createBottleMovementDto = new CreateBottleMovementDTO(
                $event->bottle->id,
                $movementType,
                auth()->user()->id,
                now(),
                $notes
            );

            $this->bottleMovementRepository->create($createBottleMovementDto->toArray());

        } catch (\Exception $e) {
            Log::error('Failed to create bottle movement', [
                'bottle_id' => $event->bottle->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function failed(BottleStatusUpdated $event, \Throwable $exception): void
    {
        Log::error('CreateBottleMovement listener failed', [
            'bottle_id' => $event->bottle->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
