<?php

namespace App\Repositories\Eloquent;

use App\Models\Geography\Municipality;
use App\Models\Geography\Neighborhood;
use App\Repositories\Contracts\MunicipalityRepositoryInterface;
use App\Repositories\Eloquent\BaseEloquentRepository;

class MunicipalityRepository extends BaseEloquentRepository implements MunicipalityRepositoryInterface
{
    public function __construct(Municipality $model)
    {
        parent::__construct($model);
    }

    public function create(array $attributes): Municipality
    {
        /** @var Municipality $municipality */
        $municipality = $this->model->create($attributes);

        return $municipality;
    }

    public function attachNeighborhoods(Municipality $municipality, array $neighborhoodIds): void
    {
        Neighborhood::whereIn('id', $neighborhoodIds)->update(['municipality_id' => $municipality->id]);
    }

    public function syncNeighborhoods(Municipality $municipality, array $neighborhoodIds): void
    {
        // 1. Unset municipality_id for neighborhoods that are no longer associated
        $municipality->neighborhoods()->whereNotIn('id', $neighborhoodIds)->update(['municipality_id' => null]);

        // 2. Set municipality_id for the new list of neighborhoods
        Neighborhood::whereIn('id', $neighborhoodIds)->update(['municipality_id' => $municipality->id]);
    }
}
