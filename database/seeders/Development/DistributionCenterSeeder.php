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

        // Helper pour récupérer un quartier
        $getNeighborhood = function (string $cityName, string $neighborhoodName) use ($cameroon) {
            $city = $cameroon ? City::where('name', $cityName)->where('country_id', $cameroon->id)->first() : null;

            return $city ? Neighborhood::where('name', $neighborhoodName)->whereHas('municipality', function ($query) use ($city) {
                $query->where('city_id', $city->id);
            })->first() : null;
        };

        // ✅ Un centre par municipalité pour une couverture totale
        $centersData = [
            // YAOUNDÉ - 7 municipalités
            ['name' => 'Centre Yaoundé I', 'city' => 'Yaoundé', 'neighborhood' => 'Melen', 'email' => 'yaounde1@petrolex.cm', 'phone' => '+237670001001', 'lat' => 3.856, 'lng' => 11.495],
            ['name' => 'Centre Yaoundé II', 'city' => 'Yaoundé', 'neighborhood' => 'Omnisport', 'email' => 'yaounde2@petrolex.cm', 'phone' => '+237670001002', 'lat' => 3.880, 'lng' => 11.510],
            ['name' => 'Centre Yaoundé III', 'city' => 'Yaoundé', 'neighborhood' => 'Ekounou', 'email' => 'yaounde3@petrolex.cm', 'phone' => '+237670001003', 'lat' => 3.850, 'lng' => 11.550],
            ['name' => 'Centre Yaoundé IV', 'city' => 'Yaoundé', 'neighborhood' => 'Nkoldongo', 'email' => 'yaounde4@petrolex.cm', 'phone' => '+237670001004', 'lat' => 3.840, 'lng' => 11.520],
            ['name' => 'Centre Yaoundé V', 'city' => 'Yaoundé', 'neighborhood' => 'Nlongkak', 'email' => 'yaounde5@petrolex.cm', 'phone' => '+237670001005', 'lat' => 3.870, 'lng' => 11.510],
            ['name' => 'Centre Yaoundé VI', 'city' => 'Yaoundé', 'neighborhood' => 'Bastos', 'email' => 'yaounde6@petrolex.cm', 'phone' => '+237670001006', 'lat' => 3.880, 'lng' => 11.500],

            // DOUALA - 6 municipalités (sauf Douala IV qui est vide)
            ['name' => 'Centre Douala I', 'city' => 'Douala', 'neighborhood' => 'Bonanjo', 'email' => 'douala1@petrolex.cm', 'phone' => '+237670002001', 'lat' => 4.040, 'lng' => 9.690],
            ['name' => 'Centre Douala II', 'city' => 'Douala', 'neighborhood' => 'New Bell', 'email' => 'douala2@petrolex.cm', 'phone' => '+237670002002', 'lat' => 4.030, 'lng' => 9.720],
            ['name' => 'Centre Douala III', 'city' => 'Douala', 'neighborhood' => 'Deido', 'email' => 'douala3@petrolex.cm', 'phone' => '+237670002003', 'lat' => 4.060, 'lng' => 9.700],
            ['name' => 'Centre Douala V', 'city' => 'Douala', 'neighborhood' => 'Makepe', 'email' => 'douala5@petrolex.cm', 'phone' => '+237670002005', 'lat' => 4.080, 'lng' => 9.730],
            ['name' => 'Centre Douala VI', 'city' => 'Douala', 'neighborhood' => 'Bali', 'email' => 'douala6@petrolex.cm', 'phone' => '+237670002006', 'lat' => 4.020, 'lng' => 9.700],

            // BAMENDA - 3 municipalités
            ['name' => 'Centre Bamenda I', 'city' => 'Bamenda', 'neighborhood' => 'Commercial Avenue', 'email' => 'bamenda1@petrolex.cm', 'phone' => '+237670003001', 'lat' => 5.960, 'lng' => 10.150],
            ['name' => 'Centre Bamenda II', 'city' => 'Bamenda', 'neighborhood' => 'Nkwen', 'email' => 'bamenda2@petrolex.cm', 'phone' => '+237670003002', 'lat' => 5.980, 'lng' => 10.160],
            ['name' => 'Centre Bamenda III', 'city' => 'Bamenda', 'neighborhood' => 'Ntarikon', 'email' => 'bamenda3@petrolex.cm', 'phone' => '+237670003003', 'lat' => 5.940, 'lng' => 10.160],

            // BAFOUSSAM - 3 municipalités
            ['name' => 'Centre Bafoussam I', 'city' => 'Bafoussam', 'neighborhood' => 'Centre-ville', 'email' => 'bafoussam1@petrolex.cm', 'phone' => '+237670004001', 'lat' => 5.470, 'lng' => 10.410],
            ['name' => 'Centre Bafoussam II', 'city' => 'Bafoussam', 'neighborhood' => 'Kaptchouo', 'email' => 'bafoussam2@petrolex.cm', 'phone' => '+237670004002', 'lat' => 5.490, 'lng' => 10.430],
            ['name' => 'Centre Bafoussam III', 'city' => 'Bafoussam', 'neighborhood' => 'Tamdja', 'email' => 'bafoussam3@petrolex.cm', 'phone' => '+237670004003', 'lat' => 5.450, 'lng' => 10.440],

            // GAROUA - 1 commune
            ['name' => 'Centre Garoua', 'city' => 'Garoua', 'neighborhood' => 'Centre-ville', 'email' => 'garoua@petrolex.cm', 'phone' => '+237670005001', 'lat' => 9.300, 'lng' => 13.400],

            // MAROUA - 1 commune
            ['name' => 'Centre Maroua', 'city' => 'Maroua', 'neighborhood' => 'Centre-ville', 'email' => 'maroua@petrolex.cm', 'phone' => '+237670006001', 'lat' => 10.590, 'lng' => 14.310],
        ];

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($centersData as $centerData) {
            $neighborhood = $getNeighborhood($centerData['city'], $centerData['neighborhood']);

            if ($neighborhood) {
                DistributionCenter::firstOrCreate(
                    ['email' => $centerData['email']],
                    [
                        'name' => $centerData['name'],
                        'neighborhood_id' => $neighborhood->id,
                        'address' => "{$centerData['neighborhood']}, {$centerData['city']}",
                        'description' => "Centre de distribution - {$centerData['name']}",
                        'phone' => $centerData['phone'],
                        'email' => $centerData['email'],
                        'latitude' => $centerData['lat'],
                        'longitude' => $centerData['lng'],
                        'is_active' => true,
                    ]
                );
                $this->command->info("✅ {$centerData['name']}");
                $createdCount++;
            } else {
                $this->command->warn("⚠️ {$centerData['name']} - quartier '{$centerData['neighborhood']}' introuvable");
                $skippedCount++;
            }
        }

        $this->command->info("\n✅ {$createdCount} centres créés, {$skippedCount} ignorés");
    }
}
