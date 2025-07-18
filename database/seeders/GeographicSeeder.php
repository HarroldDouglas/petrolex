<?php

namespace Database\Seeders;

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
        // Create Country: Cameroun
        $cameroon = Country::firstOrCreate(
            ['code' => 'CM'],
            ['name' => 'Cameroun', 'is_active' => true]
        );

        // Get cities data from config
        $citiesConfig = Config::get('geography.cameroon-cities.cities');

        foreach ($citiesConfig as $cityKey => $cityData) {
            $city = City::firstOrCreate(
                ['name' => $cityData['name'], 'country_id' => $cameroon->id],
            );

            foreach ($cityData['municipalities'] as $municipalityKey => $municipalityData) {
                $municipality = Municipality::firstOrCreate(
                    ['name' => $municipalityData['name'], 'city_id' => $city->id]
                );

                foreach ($municipalityData['neighborhoods'] as $neighborhoodKey => $neighborhoodName) {
                    Neighborhood::firstOrCreate(
                        ['name' => $neighborhoodName, 'municipality_id' => $municipality->id]
                    );
                }
            }
        }
    }
}
