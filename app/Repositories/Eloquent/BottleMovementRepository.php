<?php

namespace App\Repositories\Eloquent;

use App\Models\BottleMovement;
use App\Repositories\Contracts\BottleMovementRepositoryInterface;

class BottleMovementRepository extends BaseEloquentRepository implements BottleMovementRepositoryInterface
{
    /**
     * Create a new bottle movement
     */
    public function create(array $data): BottleMovement
    {
        return BottleMovement::create($data);
    }
}
