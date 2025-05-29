<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerDeliveryAddress>
 */
class CustomerDeliveryAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'label' => fake()->randomElement(['Domicile', 'Bureau', 'Entrepôt', 'Magasin']),
            'address' => fake()->address(),
            'latitude' => fake()->latitude(-4.3, -4.2), // Abidjan coords
            'longitude' => fake()->longitude(-4.1, -3.9),
            'phone' => fake()->phoneNumber(),
            'contact_name' => fake()->name(),
            'is_default' => false,
        ];
    }

    /**
     * Mark this address as default.
     */
    public function default(): static
    {
        return $this->state(['is_default' => true]);
    }
}
