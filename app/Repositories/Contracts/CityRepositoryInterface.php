<?php

namespace App\Repositories\Contracts;

interface CityRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCountry(string $country): \Illuminate\Database\Eloquent\Collection;
}
