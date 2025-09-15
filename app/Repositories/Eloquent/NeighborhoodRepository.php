<?php

namespace App\Repositories\Eloquent;

use App\Models\Geography\Neighborhood;
use App\Repositories\Contracts\NeighborhoodRepositoryInterface;

class NeighborhoodRepository extends BaseEloquentRepository implements NeighborhoodRepositoryInterface
{
    public function __construct(Neighborhood $model)
    {
        parent::__construct($model);
    }

    public function findByCity(int $cityId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->whereHas('municipality', function ($query) use ($cityId) {
            $query->where('city_id', $cityId);
        })->orderBy('name', 'asc')->get();
    }
}
