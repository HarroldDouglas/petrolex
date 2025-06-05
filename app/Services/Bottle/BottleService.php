<?php

namespace App\Services\Bottle;

use App\DTOs\BottleMovement\CreateBottleMovementDTO;
use App\Enums\BottleMovementType;
use App\Enums\BottleStatus;
use App\Events\BottleStatusUpdated;
use App\Models\Bottle;
use \Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\BottleRepositoryInterface;
use App\Repositories\Contracts\BottleMovementRepositoryInterface;

class BottleService
{
    public function __construct(
        private BottleRepositoryInterface $bottleRepository,
        private BottleMovementRepositoryInterface $bottleMovementRepository,
    ) {
        $this->bottleRepository = $bottleRepository;
        $this->bottleMovementRepository = $bottleMovementRepository;
    }
    public function find($bottleId): ?Bottle
    {
        return  $this->bottleRepository->find($bottleId);
    }
    public function getBottleHistory($bottleId): Collection
    {
        return  $this->bottleRepository->getBottleHistory($bottleId);
    }
    public function updateStatus($bottleId, BottleStatus $status): void{
        $this->bottleRepository->updateStatus($bottleId, $status);
        $bottle = $this->bottleRepository->find($bottleId);
        
        event(new BottleStatusUpdated(
            bottle: $bottle,
            status: $status,
            userId: auth()->id()
        ));
    }
}
