<?php

namespace App\Repositories\Eloquent;

use App\Models\BottleMovement;
use App\Repositories\Contracts\BottleMovementRepositoryInterface;
class BottleMovementRepository implements BottleMovementRepositoryInterface
{
    public function __construct(
        protected BottleMovement $model
    ) {}

    /**
     * Create a new bottle movement
     */
    public function create(array $data): BottleMovement
    {
        return $this->model->create($data);
    }

}