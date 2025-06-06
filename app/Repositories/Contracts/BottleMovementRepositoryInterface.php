<?php

namespace App\Repositories\Contracts;

use App\Models\BottleMovement;

interface BottleMovementRepositoryInterface extends baseRepositoryInterface
{
    /**
     * Create a new bottle movement
     */
    public function create(array $data): BottleMovement;
}
