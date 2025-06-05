<?php

namespace App\Repositories\Contracts;

use App\Models\Bottle;
use Illuminate\Database\Eloquent\Collection;

interface BottleRepositoryInterface
{
    /**
     * Find a bottle by its ID or throw an exception if not found.
     *
     * @param  int  $id  The ID of the bottle to find
     * @return Bottle The found bottle instance
     */
    public function find(int $id): ?Bottle;

    public function getBottleHistory($bottleId): Collection;
}
