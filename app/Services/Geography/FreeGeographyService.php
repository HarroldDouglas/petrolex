<?php

namespace App\Services\Geography;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class FreeGeographyService implements GeographyServiceInterface
{
    private const REST_COUNTRIES_URL = 'https://restcountries.com/v3.1';
    private const NOMINATIM_URL = 'https://nominatim.openstreetmap.org';

    private const HEADERS = [
        'User-Agent' => 'DistributionApp/1.0',
    ];

    public function getCountries(): array
    {
        try {
            $response = Http::timeout(30)
                ->get(self::REST_COUNTRIES_URL.'/all', [
                    'fields' => 'name,cca2,capital,population',
                ]);

            $response->throw();

            return collect($response->json())
                ->map(fn ($country) => [
                    'code' => $country['cca2'],
                    'name' => $country['name']['common'],
                    'capital' => $country['capital'][0] ?? null,
                    'population' => $country['population'] ?? 0,
                ])
                ->sortBy('name')
                ->values()
                ->all();

        } catch (RequestException $e) {
            throw new \RuntimeException('Failed to fetch countries: '.$e->getMessage());
        }
    }

    public function getRegions(string $countryCode): array
    {
        try {
            sleep(1); // Respecter limite Nominatim

            $response = Http::withHeaders(self::HEADERS)
                ->timeout(30)
                ->get(self::NOMINATIM_URL.'/search', [
                    'countrycodes' => strtolower($countryCode),
                    'featuretype' => 'state',
                    'format' => 'json',
                    'limit' => 20,
                    'addressdetails' => 1,
                ]);

            $response->throw();

            return collect($response->json())
                ->map(fn ($region) => [
                    'id' => $region['place_id'],
                    'name' => $region['address']['state'] ?? $region['display_name'],
                    'latitude' => (float) $region['lat'],
                    'longitude' => (float) $region['lng'],
                ])
                ->unique('name')
                ->values()
                ->all();

        } catch (RequestException $e) {
            throw new \RuntimeException("Failed to fetch regions for {$countryCode}: ".$e->getMessage());
        }
    }

    public function getCities(string $countryCode, ?string $regionId = null): array
    {
        try {
            sleep(1); // Respecter limite Nominatim

            $params = [
                'countrycodes' => strtolower($countryCode),
                'featuretype' => 'city',
                'format' => 'json',
                'limit' => 30,
                'addressdetails' => 1,
            ];

            $response = Http::withHeaders(self::HEADERS)
                ->timeout(30)
                ->get(self::NOMINATIM_URL.'/search', $params);

            $response->throw();

            return collect($response->json())
                ->map(fn ($city) => [
                    'id' => $city['place_id'],
                    'name' => $city['address']['city']
                           ?? $city['address']['town']
                           ?? $city['address']['village']
                           ?? $city['display_name'],
                    'admin_name' => $city['address']['state'] ?? null,
                    'latitude' => (float) $city['lat'],
                    'longitude' => (float) $city['lng'],
                    'population' => 0, // Nominatim ne fournit pas toujours la population
                ])
                ->filter(fn ($city) => ! empty($city['name']))
                ->unique('name')
                ->sortBy('name')
                ->values()
                ->all();

        } catch (RequestException $e) {
            throw new \RuntimeException("Failed to fetch cities for {$countryCode}: ".$e->getMessage());
        }
    }

    public function getDistricts(string $countryCode, string $cityId): array
    {
        try {
            sleep(1); // Respecter limite Nominatim

            // D'abord récupérer les infos de la ville
            $cityResponse = Http::withHeaders(self::HEADERS)
                ->get(self::NOMINATIM_URL.'/lookup', [
                    'osm_ids' => "N{$cityId}",
                    'format' => 'json',
                ]);

            $cityData = $cityResponse->json()[0] ?? null;
            $cityName = $cityData['display_name'] ?? '';

            // Chercher les quartiers/districts
            $response = Http::withHeaders(self::HEADERS)
                ->timeout(30)
                ->get(self::NOMINATIM_URL.'/search', [
                    'q' => $cityName.' neighbourhood',
                    'countrycodes' => strtolower($countryCode),
                    'format' => 'json',
                    'limit' => 50,
                    'addressdetails' => 1,
                ]);

            $response->throw();

            return collect($response->json())
                ->map(fn ($district) => [
                    'id' => $district['place_id'],
                    'name' => $district['address']['neighbourhood']
                           ?? $district['address']['suburb']
                           ?? $district['address']['quarter']
                           ?? $district['display_name'],
                    'latitude' => (float) $district['lat'],
                    'longitude' => (float) $district['lng'],
                    'feature_code' => $district['type'] ?? null,
                ])
                ->filter(fn ($district) => ! empty($district['name']))
                ->unique('name')
                ->values()
                ->all();

        } catch (RequestException $e) {
            throw new \RuntimeException("Failed to fetch districts for city {$cityId}: ".$e->getMessage());
        }
    }

    public function searchCities(string $countryCode, string $query): array
    {
        try {
            sleep(1); // Respecter limite Nominatim

            $response = Http::withHeaders(self::HEADERS)
                ->timeout(30)
                ->get(self::NOMINATIM_URL.'/search', [
                    'q' => $query,
                    'countrycodes' => strtolower($countryCode),
                    'featuretype' => 'city',
                    'format' => 'json',
                    'limit' => 20,
                    'addressdetails' => 1,
                ]);

            $response->throw();

            return collect($response->json())
                ->map(fn ($city) => [
                    'id' => $city['place_id'],
                    'name' => $city['address']['city']
                           ?? $city['address']['town']
                           ?? $city['address']['village']
                           ?? $city['display_name'],
                    'admin_name' => $city['address']['state'] ?? null,
                    'latitude' => (float) $city['lat'],
                    'longitude' => (float) $city['lng'],
                    'population' => 0,
                ])
                ->filter(fn ($city) => ! empty($city['name']))
                ->unique('name')
                ->values()
                ->all();

        } catch (RequestException $e) {
            throw new \RuntimeException("Failed to search cities with query '{$query}': ".$e->getMessage());
        }
    }
}
