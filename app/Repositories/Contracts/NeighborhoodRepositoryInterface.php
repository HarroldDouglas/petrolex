<?php

namespace App\Repositories\Contracts;

use App\Models\Neighborhood;

interface NeighborhoodRepositoryInterface extends BaseRepositoryInterface
{
    public function find(int $id): ?Neighborhood;
    public function findByCity(int $cityId): \Illuminate\Database\Eloquent\Collection;
}
