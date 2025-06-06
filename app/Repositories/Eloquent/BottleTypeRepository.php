<?php

namespace App\Repositories\Eloquent;

use App\Models\BottleType;
use App\Repositories\Contracts\BottleTypeRepositoryInterface;

class BottleTypeRepository extends BaseEloquentRepository implements BottleTypeRepositoryInterface
{
    /**
     * Create a new bottle movement
     */
    public function create(array $data): BottleType
    {
        return BottleType::create($data);
    }
}
