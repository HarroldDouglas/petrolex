<?php

namespace App\Repositories;

use App\Models\Geography\City;
use App\Repositories\Contracts\CityRepositoryInterface;
use App\Repositories\Eloquent\BaseEloquentRepository;

class CityRepository extends BaseEloquentRepository implements CityRepositoryInterface
{
    public function __construct(City $model)
    {
        parent::__construct($model);
    }

    public function findByCountry(string $country): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('country', $country)->get();
    }
}
