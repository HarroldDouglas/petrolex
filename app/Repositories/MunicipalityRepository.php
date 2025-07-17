<?php

namespace App\Repositories;

use App\Models\Municipality;
use App\Repositories\Contracts\MunicipalityRepositoryInterface;

class MunicipalityRepository extends BaseRepository implements MunicipalityRepositoryInterface
{
    public function __construct(Municipality $model)
    {
        parent::__construct($model);
    }

    public function find(int $id): ?Municipality
    {
        return $this->model->find($id);
    }

    public function create(array $attributes): Municipality
    {
        /** @var Municipality $municipality */
        $municipality = $this->model->create($attributes);

        return $municipality;
    }

    public function update(Municipality $municipality, array $attributes): Municipality
    {
        $municipality->update($attributes);

        return $municipality;
    }

    public function delete(Municipality $municipality): bool
    {
        return $municipality->delete();
    }

    public function attachNeighborhoods(Municipality $municipality, array $neighborhoodIds): void
    {
        $municipality->neighborhoods()->attach($neighborhoodIds);
    }

    public function syncNeighborhoods(Municipality $municipality, array $neighborhoodIds): void
    {
        $municipality->neighborhoods()->sync($neighborhoodIds);
    }
}
