<?php

namespace Database\Seeders\Development;

use App\Models\DistributionCenter;
use App\Models\Geography\City;
use App\Models\Geography\Country;
use App\Models\Geography\Neighborhood;
use Illuminate\Database\Seeder;

class DistributionCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating distribution centers...');

        $cameroon = Country::where('code', 'CM')->first();
        $douala = $cameroon ? City::where('name', 'Douala')->where('country_id', $cameroon->id)->first() : null;
        $bonanjo = $douala ? Neighborhood::where('name', 'Bonanjo')->whereHas('municipality', function ($query) use ($douala) {
            $query->where('city_id', $douala->id);
        })->first() : null;

        $yaounde = $cameroon ? City::where('name', 'Yaoundé')->where('country_id', $cameroon->id)->first() : null;
        $bastos = $yaounde ? Neighborhood::where('name', 'Bastos')->whereHas('municipality', function ($query) use ($yaounde) {
            $query->where('city_id', $yaounde->id);
        })->first() : null;
        $nkoabang = $yaounde ? Neighborhood::where('name', 'Nkoabang')->whereHas('municipality', function ($query) use ($yaounde) {
            $query->where('city_id', $yaounde->id);
        })->first() : null;
        $mimboman = $yaounde ? Neighborhood::where('name', 'Mimboman')->whereHas('municipality', function ($query) use ($yaounde) {
            $query->where('city_id', $yaounde->id);
        })->first() : null;
        $omnisport = $yaounde ? Neighborhood::where('name', 'Omnisport')->whereHas('municipality', function ($query) use ($yaounde) {
            $query->where('city_id', $yaounde->id);
        })->first() : null;
        $odza = $yaounde ? Neighborhood::where('name', 'Odza')->whereHas('municipality', function ($query) use ($yaounde) {
            $query->where('city_id', $yaounde->id);
        })->first() : null;
        $mokolo = $yaounde ? Neighborhood::where('name', 'Mokolo')->whereHas('municipality', function ($query) use ($yaounde) {
            $query->where('city_id', $yaounde->id);
        })->first() : null;

        $maroua = $cameroon ? City::where('name', 'Maroua')->where('country_id', $cameroon->id)->first() : null;
        $marouaNeighborhood = null;
        if ($maroua) {
            $marouaMunicipality = $maroua->municipalities->first();
            if ($marouaMunicipality) {
                $marouaNeighborhood = $marouaMunicipality->neighborhoods->first();
            }
        }

        $centersData = [
            [
                'name' => 'Centre Principal Douala',
                'neighborhood_id' => $bonanjo->id ?? null,
                'address' => '123 Rue Principale, Douala',
                'description' => 'Centre de distribution principal avec toutes les commodités',
                'phone' => '+237612345678',
                'email' => 'bonanjo@petrolex.cm',
                'latitude' => 4.0511,
                'longitude' => 9.7679,
                'is_active' => true,
            ],
            [
                'name' => 'Centre Bastos Yaoundé',
                'neighborhood_id' => $bastos->id ?? null,
                'address' => '45 Avenue Nord, Yaoundé',
                'description' => 'Centre de distribution pour la région du Nord',
                'phone' => '+237623456789',
                'email' => 'bastos@petrolex.cm',
                'latitude' => 3.8667,
                'longitude' => 11.5167,
                'is_active' => true,
            ],
            [
                'name' => 'Centre Nkoabang Yaoundé',
                'neighborhood_id' => $nkoabang->id ?? null,
                'address' => 'Rue Nkoabang, Yaoundé',
                'description' => 'Centre de distribution à Nkoabang',
                'phone' => '+237690123456',
                'email' => 'nkoabang@petrolex.cm',
                'latitude' => 4.1,
                'longitude' => 12.3167,
                'is_active' => true,
            ],
            [
                'name' => 'Centre Mimboman Yaoundé',
                'neighborhood_id' => $mimboman->id ?? null,
                'address' => 'Rue Mimboman, Yaoundé',
                'description' => 'Centre de distribution à Mimboman',
                'phone' => '+237690123457',
                'email' => 'mimboman@petrolex.cm',
                'latitude' => 3.85,
                'longitude' => 11.5375,
                'is_active' => true,
            ],
            [
                'name' => 'Centre Omnisport Yaoundé',
                'neighborhood_id' => $omnisport->id ?? null,
                'address' => 'Stade Omnisport, Yaoundé',
                'description' => 'Centre de distribution à Omnisport',
                'phone' => '+237690123458',
                'email' => 'omnisport@petrolex.cm',
                'latitude' => 3.8856,
                'longitude' => 11.5406,
                'is_active' => true,
            ],
            [
                'name' => 'Centre Odza Yaoundé',
                'neighborhood_id' => $odza->id ?? null,
                'address' => 'Rue Odza, Yaoundé',
                'description' => 'Centre de distribution à Odza',
                'phone' => '+237690123459',
                'email' => 'odza@petrolex.cm',
                'latitude' => 3.7833,
                'longitude' => 11.5333,
                'is_active' => true,
            ],
            [
                'name' => 'Centre Mokolo Yaoundé',
                'neighborhood_id' => $mokolo->id ?? null,
                'address' => 'Marché Mokolo, Yaoundé',
                'description' => 'Centre de distribution à Mokolo',
                'phone' => '+237690123460',
                'email' => 'mokolo@petrolex.cm',
                'latitude' => 3.8747,
                'longitude' => 11.4997,
                'is_active' => true,
            ],
            [
                'name' => 'Centre Sud Maroua',
                'neighborhood_id' => $marouaNeighborhood->id ?? null,
                'address' => '78 Avenue Sud, Maroua',
                'description' => 'Centre de distribution pour la région du Sud',
                'phone' => '+237634567890',
                'email' => 'maroua@petrolex.cm',
                'latitude' => 10.5897,
                'longitude' => 14.3267,
                'is_active' => true,
            ],
        ];

        foreach ($centersData as $center) {
            if ($center['neighborhood_id']) {
                DistributionCenter::firstOrCreate(
                    ['email' => $center['email']],
                    $center
                );
            } else {
                $this->command->warn('Skipping creation of '.$center['name'].' due to missing geographic data.');
            }
        }

        $this->command->info('Distribution centers created successfully!');
    }
}
