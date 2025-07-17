<?php

namespace App\Repositories;

use App\Models\City;
use App\Repositories\Contracts\CityRepositoryInterface;

class CityRepository extends BaseRepository implements CityRepositoryInterface
{
    public function __construct(City $model)
    {
        parent::__construct($model);
    }

    public function find(int $id): ?City
    {
        return $this->model->find($id);
    }

    public function findByCountry(string $country): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('country', $country)->get();
    }
}
