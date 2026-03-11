<?php

namespace Database\Seeders\Production;

use App\Enums\UserRole;
use App\Models\DeliveryPerson;
use App\Models\DistributionCenter;
use App\Models\Geography\Country;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DeliveryPersonSeeder extends Seeder
{
    private const DELIVERY_EMAIL = 'livreur.douala@isogaz.net';
    private const DELIVERY_PASSWORD = 'Livreur@2026!';
    private const DELIVERY_PHONE = '650000001';

    public function run(): void
    {
        $this->command->info('Creating test delivery person...');

        $cameroon = Country::where('code', 'CM')->first();
        $center = DistributionCenter::first();

        if (! $center) {
            $this->command->error('No distribution center found. Run DistributionCenterSeeder first.');
            return;
        }

        $user = User::updateOrCreate(
            ['email' => self::DELIVERY_EMAIL],
            [
                'first_name'        => 'Jean',
                'last_name'         => 'Mbarga',
                'phone_number'      => self::DELIVERY_PHONE,
                'password'          => Hash::make(self::DELIVERY_PASSWORD),
                'country_id'        => $cameroon?->id,
                'is_active'         => true,
                'email_verified_at' => now(),
                'language'          => 'fr',
            ]
        );

        if (! $user->hasRole(UserRole::DELIVERY_PERSON()->value)) {
            $user->assignRole(UserRole::DELIVERY_PERSON()->value);
        }

        $deliveryPerson = DeliveryPerson::firstOrCreate(['user_id' => $user->id]);

        // Link to distribution center
        $user->accessibleDistributionCenters()->syncWithoutDetaching([$center->id]);

        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('Email    : ' . self::DELIVERY_EMAIL);
        $this->command->info('Password : ' . self::DELIVERY_PASSWORD);
        $this->command->info('Phone    : ' . self::DELIVERY_PHONE);
        $this->command->info('Center   : ' . $center->name);
        $this->command->info('═══════════════════════════════════════════');
    }
}
