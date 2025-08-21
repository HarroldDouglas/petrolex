<?php

namespace App\Repositories;

use App\Models\Geography\Neighborhood;
use App\Repositories\Contracts\NeighborhoodRepositoryInterface;
use App\Repositories\Eloquent\BaseEloquentRepository;

class NeighborhoodRepository extends BaseEloquentRepository implements NeighborhoodRepositoryInterface
{
    public function __construct(Neighborhood $model)
    {
        parent::__construct($model);
    }

    public function findByCity(int $cityId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('city_id', $cityId)->get();
    }
}
