<?php

namespace App\Services\Bottle;

use App\Enums\BottleStatus;
use App\Events\BottleStatusUpdated;
use App\Models\Bottle;
use App\Repositories\Contracts\BottleMovementRepositoryInterface;
use App\Repositories\Contracts\BottleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BottleService
{
    public function __construct(
        private BottleRepositoryInterface $bottleRepository,
        private BottleMovementRepositoryInterface $bottleMovementRepository,
    ) {
        $this->bottleRepository = $bottleRepository;
        $this->bottleMovementRepository = $bottleMovementRepository;
    }

    /**
     * Find a bottle by its ID
     */
    public function find(int $bottleId): ?Bottle
    {
        /** @var Bottle|null */
        return $this->bottleRepository->find($bottleId);
    }

    public function getBottleHistory($bottleId): Collection
    {
        return $this->bottleRepository->getBottleHistory($bottleId);
    }

    public function updateStatus($bottleId, BottleStatus $status): void
    {
        $this->bottleRepository->updateStatus($bottleId, $status);
        $bottle = $this->bottleRepository->find($bottleId);
        if ($bottle) {
            event(new BottleStatusUpdated(
                bottle: $bottle,
                status: $status,
                userId: auth()->id()
            ));
        }
    }
}
