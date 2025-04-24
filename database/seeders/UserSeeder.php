<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'last_name' => config('admin.last_name'),
            'first_name' => config('admin.first_name'),
            'email' => config('admin.email'),
            'phone_number' => config('admin.phone'),
            'email_verified_at' => now(),
            'password' => bcrypt(config('admin.password')),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()
            ->count(100)
            ->create();

        $this->command->info('101 utilisateurs créés avec succès ! (1 admin + 100 utilisateurs)');
    }
}
