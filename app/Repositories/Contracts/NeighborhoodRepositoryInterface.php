<?php

namespace App\Repositories\Contracts;

interface NeighborhoodRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCity(int $cityId): \Illuminate\Database\Eloquent\Collection;
}
