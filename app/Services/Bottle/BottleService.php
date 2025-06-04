<?php

namespace App\Services\Bottle;

use App\Models\Bottle;
use App\Repositories\Contracts\BottleRepositoryInterface;

class BottleService
{
    public function __construct(
        private BottleRepositoryInterface $bottleRepository,
    ) {
        $this->bottleRepository = $bottleRepository;
    }

    /**
     * Find a bottle by ID or fail if not found
     *
     * @return Bottle|null
     */
    public function findOrFail(int $id): Bottle
    {
        return $this->bottleRepository->findOrFail($id);
    }
}
