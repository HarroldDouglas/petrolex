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
            $query->where('city_id', $cityId)
                ->whereHas('neighborhoods', function ($q) {
                    $q->whereExists(function ($sub) {
                        $sub->from('distribution_centers')
                            ->whereColumn('distribution_centers.neighborhood_id', 'neighborhoods.id')
                            ->where('distribution_centers.is_active', true);
                    });
                });
        })->orderBy('name', 'asc')->get();
    }
}
