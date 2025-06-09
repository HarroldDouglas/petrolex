<?php

namespace App\Repositories\Contracts;

use App\Models\BottleMovement;

interface BottleMovementRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Create a new bottle movement
     */
    public function create(array $data): BottleMovement;
}
