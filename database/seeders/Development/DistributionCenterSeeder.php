<?php

// database/seeders/Development/DistributionCenterSeeder.php

namespace Database\Seeders\Development;

use App\Models\DistributionCenter;
use App\Models\Geography\City;
use App\Models\Geography\Country;
use App\Models\Geography\Municipality;
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

        // Retrieve geographic IDs
        $cameroon = Country::where('code', 'CM')->first();
        $douala = $cameroon ? City::where('name', 'Douala')->where('country_id', $cameroon->id)->first() : null;
        $bonanjo = $douala ? Neighborhood::where('name', 'Bonanjo')->whereHas('municipality', function ($query) use ($douala) {
            $query->where('city_id', $douala->id);
        })->first() : null;

        $yaounde = $cameroon ? City::where('name', 'Yaoundé')->where('country_id', $cameroon->id)->first() : null;
        $bastos = $yaounde ? Neighborhood::where('name', 'Bastos')->whereHas('municipality', function ($query) use ($yaounde) {
            $query->where('city_id', $yaounde->id);
        })->first() : null;

        // Use Maroua instead of Adamaoua
        $maroua = $cameroon ? City::where('name', 'Maroua')->where('country_id', $cameroon->id)->first() : null;
        $marouaNeighborhood = null;
        if ($maroua) {
            $marouaMunicipality = $maroua->municipalities->first(); // Get the first municipality of Maroua
            if ($marouaMunicipality) {
                $marouaNeighborhood = $marouaMunicipality->neighborhoods->first(); // Get the first neighborhood of that municipality
            }
        }

        $centersData = [
            [
                'name' => 'Centre Principal',
                'neighborhood_id' => $bonanjo->id ?? null,
                'address' => '123 Rue Principale, Douala',
                'description' => 'Centre de distribution principal avec toutes les commodités',
                'phone' => '+237612345678',
                'email' => 'centre.principal@petrolex.cm',
                'latitude' => 4.0511,
                'longitude' => 9.7679,
                'is_active' => true,
            ],
            [
                'name' => 'Centre Nord',
                'neighborhood_id' => $bastos->id ?? null,
                'address' => '45 Avenue Nord, Yaoundé',
                'description' => 'Centre de distribution pour la région du Nord',
                'phone' => '+237623456789',
                'email' => 'centre.nord@petrolex.cm',
                'latitude' => 4.0622,
                'longitude' => 9.7895,
                'is_active' => true,
            ],
            [
                'name' => 'Centre Sud',
                'neighborhood_id' => $marouaNeighborhood->id ?? null,
                'address' => '78 Avenue Sud, Maroua',
                'description' => 'Centre de distribution pour la région du Sud',
                'phone' => '+237634567890',
                'email' => 'centre.sud@petrolex.cm',
                'latitude' => 4.0433,
                'longitude' => 9.7486,
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
