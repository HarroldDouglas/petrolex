<?php

namespace App\Repositories\Geography;

use Illuminate\Database\Eloquent\Collection;

interface GeographyRepositoryInterface
{
    public function getAllCountries(): Collection;

    public function getCitiesByCountryId(int $countryId): Collection;

    public function getMunicipalitiesByCityId(int $cityId): Collection;

    public function getNeighborhoodsByCityId(int $cityId): Collection;

    public function getNeighborhoodsByMunicipalityId(int $municipalityId): Collection;
}
