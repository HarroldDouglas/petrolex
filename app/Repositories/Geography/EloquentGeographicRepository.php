<?php

namespace App\Repositories\Geography;

use App\Models\Geography\City;
use App\Models\Geography\Country;
use App\Models\Geography\Municipality;
use Illuminate\Database\Eloquent\Collection;

class EloquentGeographicRepository implements GeographyRepositoryInterface
{
    public function getAllCountries(): Collection
    {
        return Country::all();
    }

    public function getCitiesByCountryId(int $countryId): Collection
    {
        $country = Country::find($countryId);

        if (! $country) {
            return new Collection;
        }

        return $country->cities;
    }

    public function getMunicipalitiesByCityId(int $cityId): Collection
    {
        $city = City::find($cityId);

        if (! $city) {
            return new Collection;
        }

        return $city->municipalities;
    }

    public function getNeighborhoodsByCityId(int $cityId): Collection
    {
        $city = City::find($cityId);

        if (! $city) {
            return new Collection;
        }

        return $city->neighborhoods;
    }

    public function getNeighborhoodsByMunicipalityId(int $municipalityId): Collection
    {
        $municipality = Municipality::find($municipalityId);

        if (! $municipality) {
            return new Collection;
        }

        return $municipality->neighborhoods;
    }
}
