<?php

namespace App\Repositories\Eloquent;

use App\Models\Bottle;
use App\Repositories\Contracts\BottleRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BottleRepository implements BottleRepositoryInterface
{
    /**
     * Find a bottle by ID or fail
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): ?Bottle
    {
        return Bottle::findOrFail($id);
    }
}
