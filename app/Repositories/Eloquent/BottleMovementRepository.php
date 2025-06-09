<?php

namespace App\Repositories\Eloquent;

use App\Models\BottleMovement;
use App\Repositories\Contracts\BottleMovementRepositoryInterface;

class BottleMovementRepository extends BaseEloquentRepository implements BottleMovementRepositoryInterface
{
    public function __construct(BottleMovement $bottle)
    {
        $this->model = $bottle;
    }

    /**
     * Create a new bottle movement
     */
    public function create(array $data): BottleMovement
    {
        return BottleMovement::create($data);
    }
}
