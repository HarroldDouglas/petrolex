<?php

namespace App\Repositories\Contracts;

use App\Models\City;

interface CityRepositoryInterface extends BaseRepositoryInterface
{
    public function find(int $id): ?City;
    public function findByCountry(string $country): \Illuminate\Database\Eloquent\Collection;
}
