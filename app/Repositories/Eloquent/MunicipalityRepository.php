<?php

namespace App\Repositories\Eloquent;

use App\Models\Geography\Municipality;
use App\Models\Geography\Neighborhood;
use App\Repositories\Contracts\MunicipalityRepositoryInterface;

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

        if (! $municipality->id) {
            throw new \RuntimeException('Failed to create municipality - no ID assigned after creation.');
        }

        return $municipality;
    }

    public function attachNeighborhoods(Municipality $municipality, array $neighborhoodIds): void
    {
        if (! $municipality->id) {
            throw new \InvalidArgumentException('Municipality must have an ID before attaching neighborhoods. Municipality: '.json_encode($municipality->toArray()));
        }

        Neighborhood::whereIn('id', $neighborhoodIds)->update(['municipality_id' => $municipality->id]);
    }

    public function all(array $columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->orderBy('name', 'asc')->get($columns);
    }

    public function syncNeighborhoods(Municipality $municipality, array $neighborhoodIds): void
    {
        if (! $municipality->id) {
            throw new \InvalidArgumentException('Municipality must have an ID before syncing neighborhoods. Municipality: '.json_encode($municipality->toArray()));
        }

        $currentNeighborhoodIds = $municipality->neighborhoods()->pluck('id')->toArray();

        $toUnassign = array_diff($currentNeighborhoodIds, $neighborhoodIds);

        if (! empty($toUnassign)) {
            $defaultMunicipality = Municipality::firstOrCreate([
                'name' => 'Non assigné',
                'city_id' => $municipality->city_id, // Same city as the current municipality
            ]);

            Neighborhood::whereIn('id', $toUnassign)->update(['municipality_id' => $defaultMunicipality->id]);
        }

        if (! empty($neighborhoodIds)) {
            Neighborhood::whereIn('id', $neighborhoodIds)->update(['municipality_id' => $municipality->id]);
        }
    }
}
