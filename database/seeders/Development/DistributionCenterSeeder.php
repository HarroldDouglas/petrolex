<?php

// database/seeders/Development/DistributionCenterSeeder.php

namespace Database\Seeders\Development;

use App\Models\DistributionCenter;
use Illuminate\Database\Seeder;

class DistributionCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating distribution centers...');

        $centers = [
            [
                'name' => 'Centre Principal',
                'country' => 'Cameroun',
                'city' => 'Douala',
                'neighborhood' => 'Bonanjo',
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
                'country' => 'Cameroun',
                'city' => 'Yaoundé',
                'neighborhood' => 'Bastos',
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
                'country' => 'Cameroun',
                'city' => 'Adamaoua',
                'neighborhood' => 'Centre',
                'address' => '78 Avenue Sud, Adamaoua',
                'description' => 'Centre de distribution pour la région du Sud',
                'phone' => '+237634567890',
                'email' => 'centre.sud@petrolex.cm',
                'latitude' => 4.0433,
                'longitude' => 9.7486,
                'is_active' => true,
            ],
        ];

        foreach ($centers as $center) {
            DistributionCenter::firstOrCreate(
                ['email' => $center['email']],
                $center
            );
        }

        $this->command->info('Distribution centers created successfully!');
    }
}
