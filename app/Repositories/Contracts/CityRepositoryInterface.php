<?php

namespace App\Repositories\Contracts;

interface CityRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Geography\City>
     */
    public function findByCountry(string $country): \Illuminate\Database\Eloquent\Collection;
}
