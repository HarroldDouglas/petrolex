<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Geography\Country;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createSuperAdmin();
        $this->createGasManager();
        $this->createCenterManager();
    }

    /**
     * Create the Super Admin user
     */
    private function createSuperAdmin(): void
    {
        $cameroon = Country::where('code', 'CM')->first();

        $superAdmin = User::factory()->create([
            'first_name' => config('super-admin.first_name', 'Super'),
            'last_name' => config('super-admin.last_name', 'Admin'),
            'email' => config('super-admin.email', 'admin@petrolex.com'),
            'phone_number' => config('super-admin.phone', '670000001'),
            'country_id' => $cameroon?->id,
            'password' => Hash::make(config('super-admin.password', 'password')),
        ]);

        $superAdmin->assignRole(UserRole::SUPER_ADMIN()->value);
    }

    /**
     * Create the Gas Manager user
     */
    private function createGasManager(): void
    {
        $cameroon = Country::where('code', 'CM')->first();

        $gasManager = User::factory()->create([
            'first_name' => 'Responsable',
            'last_name' => 'Gaz',
            'email' => 'responsablegaz@petrolex.com',
            'phone_number' => '670000002',
            'country_id' => $cameroon?->id,
            'password' => Hash::make('password'),
        ]);

        $gasManager->assignRole(UserRole::GAS_MANAGER()->value);
    }

    /**
     * Create the Center Manager user
     */
    private function createCenterManager(): void
    {
        $cameroon = Country::where('code', 'CM')->first();

        $centerManager = User::factory()->create([
            'first_name' => 'Responsable',
            'last_name' => 'Centre',
            'email' => 'responsablecentre@petrolex.com',
            'phone_number' => '670000003',
            'country_id' => $cameroon?->id,
            'password' => Hash::make('password'),
        ]);

        $centerManager->assignRole(UserRole::CENTER_MANAGER()->value);
    }
}
