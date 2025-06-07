<?php

namespace App\Services\Geography;

interface GeographyServiceInterface
{
    /**
     * Get all countries
     */
    public function getCountries(): array;

    /**
     * Get cities for a specific region
     */
    public function getCities(string $countryCode): array;

    /**
     * Get neighborhoods for a specific city
     */
    public function getNeighborhoods(string $city): array;
}
