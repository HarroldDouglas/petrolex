<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\DeliveryPerson;
use App\Models\DistributionCenter;
use App\Models\Geography\Country;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a permanent test delivery person account for mobile app testing.
 * This account is used by the mobile dev team and must never be deleted.
 */
class TestDeliveryPersonSeeder extends Seeder
{
    private const TEST_EMAIL = 'delivery1@test.com';
    private const TEST_PASSWORD = 'password';
    private const TEST_PHONE = '+237670000010';
    private const TEST_FIRST_NAME = 'Test';
    private const TEST_LAST_NAME = 'Livreur';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating permanent test delivery person account...');

        $existingUser = User::where('email', self::TEST_EMAIL)->first();

        if ($existingUser) {
            $this->command->warn('Test delivery person already exists. Verifying data integrity...');
            $this->verifyAndComplete($existingUser);

            return;
        }

        $cameroon = Country::where('code', 'CM')->first();

        if (! $cameroon) {
            $this->command->error('Cameroon country not found. Please seed geographic data first.');

            return;
        }

        $user = User::create([
            'first_name' => self::TEST_FIRST_NAME,
            'last_name' => self::TEST_LAST_NAME,
            'email' => self::TEST_EMAIL,
            'phone_number' => self::TEST_PHONE,
            'password' => Hash::make(self::TEST_PASSWORD),
            'country_id' => $cameroon->id,
            'is_active' => true,
            'email_verified_at' => now(),
            'language' => 'fr',
        ]);

        $user->assignRole(UserRole::DELIVERY_PERSON()->value);

        $deliveryPerson = DeliveryPerson::create([
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        $this->assignToFirstCenter($deliveryPerson);

        $this->printCredentials($user);
    }

    private function verifyAndComplete(User $user): void
    {
        $user->password = Hash::make(self::TEST_PASSWORD);
        $user->is_active = true;
        $user->email_verified_at = $user->email_verified_at ?? now();
        $user->save();
        $this->command->info('✓ Password and status verified');

        if (! $user->hasRole(UserRole::DELIVERY_PERSON()->value)) {
            $user->assignRole(UserRole::DELIVERY_PERSON()->value);
            $this->command->info('✓ Assigned DELIVERY_PERSON role');
        }

        if (! $user->deliveryPerson) {
            $deliveryPerson = DeliveryPerson::create([
                'user_id' => $user->id,
                'is_active' => true,
            ]);
            $this->assignToFirstCenter($deliveryPerson);
            $this->command->info('✓ Created delivery person record');
        } else {
            $deliveryPerson = $user->deliveryPerson;
            if ($deliveryPerson->distributionCenters()->count() === 0) {
                $this->assignToFirstCenter($deliveryPerson);
            }
        }

        $this->printCredentials($user);
    }

    private function assignToFirstCenter(DeliveryPerson $deliveryPerson): void
    {
        $center = DistributionCenter::first();

        if (! $center) {
            $this->command->warn('No distribution centers found. Delivery person created without center assignment.');

            return;
        }

        if (! $deliveryPerson->distributionCenters()->where('distribution_center_id', $center->id)->exists()) {
            $deliveryPerson->distributionCenters()->attach($center->id, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("✓ Assigned to distribution center: {$center->name}");
    }

    private function printCredentials(User $user): void
    {
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('Email: ' . self::TEST_EMAIL);
        $this->command->info('Password: ' . self::TEST_PASSWORD);
        $this->command->info('Phone: ' . ($user->phone_number ?? self::TEST_PHONE));
        $this->command->info('═══════════════════════════════════════════');
        $this->command->warn('⚠️  DO NOT DELETE this account - it is used for mobile app testing');
    }
}
