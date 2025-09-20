<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Geography\Neighborhood;
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
            'phone_country_code' => '+237',
            'contact_firstname' => fake()->firstName(),
            'contact_lastname' => fake()->lastName(),
            'email' => fake()->safeEmail(),
            'address_precision' => fake()->sentence(),
            'neighborhood_id' => Neighborhood::inRandomOrder()->first()?->id,
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
