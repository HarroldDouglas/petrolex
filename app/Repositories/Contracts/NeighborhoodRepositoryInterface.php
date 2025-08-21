<?php

namespace App\Repositories\Contracts;

use App\Models\Geography\Neighborhood;

interface NeighborhoodRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCity(int $cityId): \Illuminate\Database\Eloquent\Collection;
}
