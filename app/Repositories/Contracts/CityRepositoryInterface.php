<?php

namespace App\Repositories\Contracts;

use App\Models\Geography\City;

interface CityRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCountry(string $country): \Illuminate\Database\Eloquent\Collection;
}
