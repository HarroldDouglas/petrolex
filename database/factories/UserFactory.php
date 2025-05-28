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
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower($firstName.'.'.$lastName.'@example.com'),
            'phone_number' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Default password for test users
            'remember_token' => Str::random(10),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): self
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a manager user.
     */
    public function manager(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => 'manager_'.Str::random(5).'@example.com',
            ];
        });
    }

    /**
     * Create a accountant user.
     */
    public function accountant(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => 'accountant_'.Str::random(5).'@example.com',
            ];
        });
    }

    /**
     * Create a center manager user.
     */
    public function centerManager(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => 'center_'.Str::random(5).'@example.com',
            ];
        });
    }

    /**
     * Create a warehouse manager user.
     */
    public function warehouseManager(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => 'warehouse_'.Str::random(5).'@example.com',
            ];
        });
    }

    /**
     * Create a delivery person user.
     */
    public function deliveryPerson(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => 'delivery_'.Str::random(5).'@example.com',
            ];
        });
    }

    /**
     * Create a customer user.
     */
    public function customer(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => 'customer_'.Str::random(5).'@example.com',
            ];
        });
    }
}
