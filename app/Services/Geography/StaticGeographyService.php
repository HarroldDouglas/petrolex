<?php

namespace App\Services\Geography;

use Illuminate\Support\Facades\Config;

class StaticGeographyService implements GeographyServiceInterface
{
    /**
     * Get country config mapping
     */
    private function getCountryConfigMapping(): array
    {
        return Config::get('geography.authorized-countries', []);
    }

    public function getCountries(): array
    {
        $countriesConfig = $this->getCountryConfigMapping();
        $result = [];

        foreach ($countriesConfig as $code => $countryData) {
            if ($countryData['active'] ?? false) {
                $result[$code] = $countryData['name'];
            }
        }

        return $result;
    }

    public function getCities(string $country): array
    {
        $countryCode = $this->resolveCountryCode($country);
        $countriesConfig = $this->getCountryConfigMapping();

        if (! isset($countriesConfig[$countryCode])) {
            throw new \InvalidArgumentException("Country '{$country}' not supported");
        }

        $countryData = $countriesConfig[$countryCode];
        $citiesConfigFile = "{$countryData['value']}-cities";
        $citiesConfig = Config::get("geography.{$citiesConfigFile}");

        $result = [];
        foreach ($citiesConfig['cities'] ?? [] as $cityKey => $cityData) {
            $result[$cityKey] = $cityData['name'];
        }

        return $result;
    }

    public function getNeighborhoods(string $city): array
    {
        $cityKey = $this->normalizeCityName($city);
        $countriesConfig = $this->getCountryConfigMapping();

        foreach ($countriesConfig as $countryCode => $countryData) {
            if (! ($countryData['active'] ?? false)) {
                continue;
            }

            $citiesConfigFile = "{$countryData['value']}-cities";
            $citiesConfig = Config::get("geography.{$citiesConfigFile}");

            if (isset($citiesConfig['cities'][$cityKey])) {
                $cityData = $citiesConfig['cities'][$cityKey];

                return $cityData['neighborhoods'] ?? [];
            }
        }

        throw new \InvalidArgumentException("City '{$city}' not found");
    }

    public function getCityByKey(string $cityKey): ?array
    {
        $countriesConfig = $this->getCountryConfigMapping();

        foreach ($countriesConfig as $countryCode => $countryData) {
            if (! ($countryData['active'] ?? false)) {
                continue;
            }

            $citiesConfigFile = "{$countryData['value']}-cities";
            $citiesConfig = Config::get("geography.{$citiesConfigFile}");

            if (isset($citiesConfig['cities'][$cityKey])) {
                $cityData = $citiesConfig['cities'][$cityKey];

                return [
                    'name' => $cityData['name'],
                    'key' => $cityKey,
                    'country_code' => $countryCode,
                    'latitude' => $cityData['latitude'] ?? null,
                    'longitude' => $cityData['longitude'] ?? null,
                ];
            }
        }

        return null;
    }

    public function getNeighborhoodByKey(string $cityKey, string $neighborhoodKey): ?array
    {
        $countriesConfig = $this->getCountryConfigMapping();

        foreach ($countriesConfig as $countryCode => $countryData) {
            if (! ($countryData['active'] ?? false)) {
                continue;
            }

            $citiesConfigFile = "{$countryData['value']}-cities";
            $citiesConfig = Config::get("geography.{$citiesConfigFile}");

            if (isset($citiesConfig['cities'][$cityKey]['neighborhoods'][$neighborhoodKey])) {
                $cityData = $citiesConfig['cities'][$cityKey];
                $neighborhoodName = $cityData['neighborhoods'][$neighborhoodKey];

                return [
                    'key' => $neighborhoodKey,
                    'name' => $neighborhoodName,
                    'city_key' => $cityKey,
                    'city_name' => $cityData['name'],
                    'country_code' => $countryCode,
                ];
            }
        }

        return null;
    }

    private function resolveCountryCode(string $country): string
    {
        if (strlen($country) === 2) {
            return strtoupper($country);
        }

        $countriesConfig = $this->getCountryConfigMapping();

        foreach ($countriesConfig as $code => $countryData) {
            if (strtolower($countryData['name']) === strtolower($country)) {
                return $code;
            }
        }

        throw new \InvalidArgumentException("Country '{$country}' not found");
    }

    private function normalizeCityName(string $city): string
    {
        return strtolower(
            str_replace(
                [' ', '-', '_'],
                '-',
                trim(
                    preg_replace('/[^\w\s-]/', '', $city)
                )
            )
        );
    }
}
