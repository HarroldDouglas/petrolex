<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'last_name' => fake()->lastName(),
            'first_name' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->unique()->e164PhoneNumber(),
            'address' => fake()->address(),
            'email_verified_at' => fake()->boolean(80) ? now() : null,
            'phone_verified_at' => fake()->boolean(80) ? now() : null,
            'password' => static::$password ??= Hash::make('password'),
            'is_active' => fake()->boolean(90),
            'last_login_at' => fake()->dateTimeBetween('-1 month'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'phone_verified_at' => null,
        ]);
    }
}
