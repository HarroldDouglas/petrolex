<?php

namespace App\Services\Geography;

interface GeographyServiceInterface
{
    /**
     * Get all countries
     */
    public function getCountries(): array;

    /**
     * Get regions for a specific country
     */
    public function getRegions(string $countryCode): array;

    /**
     * Get cities for a specific region
     */
    public function getCities(string $countryCode, string $regionId): array;

    /**
     * Get districts for a specific city
     */
    public function getDistricts(string $countryCode, string $cityId): array;

    /**
     * Search cities by name
     */
    public function searchCities(string $countryCode, string $query): array;
}
