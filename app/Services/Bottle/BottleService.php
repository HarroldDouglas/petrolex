<?php

namespace App\Services\Bottle;

use App\Enums\BottleStatus;
use App\Models\Bottle;
use \Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\BottleRepositoryInterface;

class BottleService
{
    public function __construct(
        private BottleRepositoryInterface $bottleRepository,
    ) {
        $this->bottleRepository = $bottleRepository;
    }
    public function find($bottleId): ?Bottle
    {
        return  $this->bottleRepository->find($bottleId);
    }
    public function getBottleHistory($bottleId): Collection
    {
        return  $this->bottleRepository->getBottleHistory($bottleId);
    }
    public function changeStatus($bottleId, BottleStatus $status): void{
        $this->bottleRepository->changeStatus($bottleId, $status);
    }
}
