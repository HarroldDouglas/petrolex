<?php

namespace Database\Seeders;

use App\Enums\Currency;
use App\Models\Geography\City;
use App\Models\Geography\Country;
use App\Models\Geography\Municipality;
use App\Models\Geography\Neighborhood;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class GeographicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cameroon = Country::firstOrCreate(
            ['code' => 'CM'],
            [
                'name' => 'Cameroun',
                'phone_code' => '+237',
                'currency' => Currency::XAF(),
                'is_active' => true,
            ]
        );

        $citiesConfig = Config::get('geography.cameroon-cities.cities');

        foreach ($citiesConfig as $cityKey => $cityData) {
            $city = City::firstOrCreate(
                ['name' => $cityData['name'], 'country_id' => $cameroon->id],
            );

            foreach ($cityData['municipalities'] as $municipalityKey => $municipalityData) {
                $municipality = Municipality::firstOrCreate(
                    ['name' => $municipalityData['name'], 'city_id' => $city->id]
                );

                foreach ($municipalityData['neighborhoods'] as $neighborhoodKey => $neighborhoodData) {
                    Neighborhood::firstOrCreate(
                        ['name' => $neighborhoodData['name'], 'municipality_id' => $municipality->id,
                            'latitude' => $neighborhoodData['latitude'] ?? null, 'longitude' => $neighborhoodData['longitude'] ?? null]
                    );
                }
            }
        }
    }
}
