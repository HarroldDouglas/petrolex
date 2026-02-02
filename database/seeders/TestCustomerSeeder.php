<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Geography\Country;
use App\Models\Geography\Neighborhood;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a permanent test customer account for mobile app testing.
 * This account is used by Google Play Store robots for automated testing
 * and must never be deleted across database updates.
 */
class TestCustomerSeeder extends Seeder
{
    // Test customer credentials - DO NOT CHANGE these values
    private const TEST_EMAIL = 'test.customer@petrolex.com';
    private const TEST_PASSWORD = 'TestPetrolex2026!';
    private const TEST_PHONE = '+237600000001';
    private const TEST_FIRST_NAME = 'Test';
    private const TEST_LAST_NAME = 'Customer';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating permanent test customer account...');

        // Check if test customer already exists
        $existingUser = User::where('email', self::TEST_EMAIL)->first();

        if ($existingUser) {
            $this->command->warn('Test customer already exists. Verifying data integrity...');
            $this->verifyAndCompleteTestCustomer($existingUser);

            return;
        }

        // Get Cameroon country
        $cameroon = Country::where('code', 'CM')->first();

        if (! $cameroon) {
            $this->command->error('Cameroon country not found. Please seed geographic data first.');

            return;
        }

        // Create user with all required fields
        $user = User::create([
            'first_name' => self::TEST_FIRST_NAME,
            'last_name' => self::TEST_LAST_NAME,
            'email' => self::TEST_EMAIL,
            'phone_number' => self::TEST_PHONE,
            'password' => Hash::make(self::TEST_PASSWORD),
            'country_id' => $cameroon->id,
            'is_active' => true,
            'email_verified_at' => now(),
            'language' => 'fr', // French by default
        ]);

        // Assign customer role
        $user->assignRole(UserRole::CUSTOMER()->value);

        // Create customer record with zero balance
        $customer = Customer::create([
            'user_id' => $user->id,
            'current_balance' => 0.00,
        ]);

        // Try to create a default delivery address if neighborhood exists
        $this->createDefaultDeliveryAddress($customer);

        $this->command->info('Test customer account created successfully!');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('Email: '.self::TEST_EMAIL);
        $this->command->info('Password: '.self::TEST_PASSWORD);
        $this->command->info('Phone: '.self::TEST_PHONE);
        $this->command->info('═══════════════════════════════════════════');
        $this->command->warn('⚠️  DO NOT DELETE this account - it is used for automated testing');
    }

    /**
     * Verify and complete test customer data if account already exists
     */
    private function verifyAndCompleteTestCustomer(User $user): void
    {
        $updated = false;

        // ALWAYS update password to ensure it matches expected credentials
        $user->password = Hash::make(self::TEST_PASSWORD);
        $user->save();
        $this->command->info('✓ Password updated');
        $updated = true;

        // Ensure phone number is correct
        if ($user->phone_number !== self::TEST_PHONE) {
            $user->phone_number = self::TEST_PHONE;
            $user->save();
            $this->command->info('✓ Phone number updated');
            $updated = true;
        }

        // Ensure user has customer role
        if (! $user->hasRole(UserRole::CUSTOMER()->value)) {
            $user->assignRole(UserRole::CUSTOMER()->value);
            $this->command->info('✓ Assigned CUSTOMER role');
            $updated = true;
        }

        // Ensure customer record exists
        if (! $user->customer) {
            Customer::create([
                'user_id' => $user->id,
                'current_balance' => 0.00,
            ]);
            $this->command->info('✓ Created customer record');
            $updated = true;
        }

        // Ensure customer has at least one delivery address
        if ($user->customer && $user->customer->deliveryAddresses()->count() === 0) {
            $this->createDefaultDeliveryAddress($user->customer);
            $updated = true;
        }

        // Ensure email is verified
        if (! $user->email_verified_at) {
            $user->email_verified_at = now();
            $user->save();
            $this->command->info('✓ Email verified');
            $updated = true;
        }

        // Ensure user is active
        if (! $user->is_active) {
            $user->is_active = true;
            $user->save();
            $this->command->info('✓ User activated');
            $updated = true;
        }

        if ($updated) {
            $this->command->info('Test customer data updated successfully!');
        } else {
            $this->command->info('Test customer data is already complete.');
        }

        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('Email: '.self::TEST_EMAIL);
        $this->command->info('Password: '.self::TEST_PASSWORD);
        $this->command->info('Phone: '.$user->phone_number);
        $this->command->info('═══════════════════════════════════════════');
    }

    /**
     * Create a default delivery address for the test customer
     */
    private function createDefaultDeliveryAddress(Customer $customer): void
    {
        // Try to find Melen neighborhood in Yaoundé I
        $neighborhood = Neighborhood::where('name', 'Melen')
            ->whereHas('municipality', function ($q) {
                $q->where('name', 'Yaoundé I');
            })
            ->first();

        // If Melen not found, try to get any neighborhood in Yaoundé
        if (! $neighborhood) {
            $neighborhood = Neighborhood::whereHas('municipality', function ($q) {
                $q->where('name', 'like', 'Yaoundé%');
            })->first();
        }

        // If still no neighborhood found, try to get the first available neighborhood
        if (! $neighborhood) {
            $neighborhood = Neighborhood::first();
        }

        if (! $neighborhood) {
            $this->command->warn('No neighborhoods found. Test customer created without delivery address.');

            return;
        }

        // Create default delivery address
        $customer->deliveryAddresses()->create([
            'label' => 'Adresse Test',
            'address' => 'Adresse de test pour validation Google Play',
            'latitude' => 3.8617882,
            'longitude' => 11.5835694,
            'phone' => self::TEST_PHONE,
            'phone_country_code' => '+237',
            'contact_firstname' => self::TEST_FIRST_NAME,
            'contact_lastname' => self::TEST_LAST_NAME,
            'email' => self::TEST_EMAIL,
            'address_precision' => 'Compte de test permanent',
            'is_default' => true,
            'neighborhood_id' => $neighborhood->id,
        ]);

        $this->command->info('Default delivery address created for test customer.');
    }
}
