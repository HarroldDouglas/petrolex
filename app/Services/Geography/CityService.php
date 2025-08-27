<?php

namespace App\Services\Geography;

use App\Models\Geography\City;
use App\Repositories\Contracts\CityRepositoryInterface;
use App\Services\BaseServiceForEntity;

class CityService extends BaseServiceForEntity
{
    public function __construct(protected CityRepositoryInterface $cityRepository)
    {
        parent::__construct($cityRepository);
    }

    /**
     * Get all cities for a given country.
     *
     * @return \Illuminate\Database\Eloquent\Collection|City[]
     */
    public function getCitiesByCountry(string $country)
    {
        return $this->cityRepository->findByCountry($country);
    }

    /**
     * Find a city by its ID.
     *
     * @return City The found city model.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the city is not found.
     */
    public function find(int $cityId): City
    {
        /** @var City $city */
        $city = $this->cityRepository->find($cityId);

        if (! $city) {
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException)->setModel(City::class, [$cityId]);
        }

        return $city;
    }

    protected function getModel(): string
    {
        return City::class;
    }
}
