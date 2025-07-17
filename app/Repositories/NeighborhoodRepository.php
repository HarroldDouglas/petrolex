<?php

namespace App\Repositories;

use App\Models\Neighborhood;
use App\Repositories\Contracts\NeighborhoodRepositoryInterface;

class NeighborhoodRepository extends BaseRepository implements NeighborhoodRepositoryInterface
{
    public function __construct(Neighborhood $model)
    {
        parent::__construct($model);
    }

    public function find(int $id): ?Neighborhood
    {
        return $this->model->find($id);
    }

    public function findByCity(int $cityId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('city_id', $cityId)->get();
    }
}
