<?php

namespace App\Repositories\Eloquent;

use App\Models\Geography\City;
use App\Repositories\Contracts\CityRepositoryInterface;

class CityRepository extends BaseEloquentRepository implements CityRepositoryInterface
{
    public function __construct(City $model)
    {
        parent::__construct($model);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Geography\City>
     */
    public function findByCountry(string $country): \Illuminate\Database\Eloquent\Collection
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Geography\City> */
        return $this->model->where('country_id', $country)
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();
    }
}
