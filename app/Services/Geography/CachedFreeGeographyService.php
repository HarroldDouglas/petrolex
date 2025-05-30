<?php

namespace App\Services\Geography;

use App\Services\Shared\Cache\CacheServiceInterface;

class CachedFreeGeographyService implements GeographyServiceInterface
{
    private const DEFAULT_TTL = 86400; // 24 hours

    public function __construct(
        private readonly GeographyServiceInterface $geographyService,
        private readonly CacheServiceInterface $cacheService,
        private readonly int $ttl = self::DEFAULT_TTL
    ) {}

    public function getCountries(): array
    {
        return $this->cacheService->remember(
            'countries',
            $this->ttl,
            fn () => $this->geographyService->getCountries()
        );
    }

    public function getRegions(string $countryCode): array
    {
        return $this->cacheService->remember(
            "regions:{$countryCode}",
            $this->ttl,
            fn () => $this->geographyService->getRegions($countryCode)
        );
    }

    public function getCities(string $countryCode, string $regionId): array
    {
        return $this->cacheService->remember(
            "cities:{$countryCode}:{$regionId}",
            $this->ttl,
            fn () => $this->geographyService->getCities($countryCode, $regionId)
        );
    }

    public function getDistricts(string $countryCode, string $cityId): array
    {
        return $this->cacheService->remember(
            "districts:{$countryCode}:{$cityId}",
            $this->ttl,
            fn () => $this->geographyService->getDistricts($countryCode, $cityId)
        );
    }

    public function searchCities(string $countryCode, string $query): array
    {
        // Cache searches for shorter period
        return $this->cacheService->remember(
            "search_cities:{$countryCode}:".md5($query),
            3600, // 1 hour
            fn () => $this->geographyService->searchCities($countryCode, $query)
        );
    }
}
