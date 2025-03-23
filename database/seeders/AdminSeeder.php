<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => config('admin.name'),
            'email' => config('admin.email'),
            'email_verified_at' => now(),
            'password' => bcrypt(config('admin.password')),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);
    }
}
