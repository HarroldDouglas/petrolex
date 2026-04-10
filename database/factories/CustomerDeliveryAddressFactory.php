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
            'latitude' => fake()->latitude(3.8, 4.1), // Cameroun coords (Yaoundé-Douala region)
            'longitude' => fake()->longitude(9.6, 11.6), // Cameroun coords (Douala-Yaoundé region)
            'location_link' => null,
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
     * Use a location link instead of GPS coordinates.
     */
    public function withLocationLink(): static
    {
        return $this->state([
            'latitude' => null,
            'longitude' => null,
            'location_link' => 'https://maps.google.com/?q=' . fake()->latitude(3.8, 4.1) . ',' . fake()->longitude(9.6, 11.6),
            'neighborhood_id' => null,
        ]);
    }

    /**
     * Mark this address as default.
     */
    public function default(): static
    {
        return $this->state(['is_default' => true]);
    }

    /**
     * Create address in a municipality where distribution centers exist.
     * Ensures compatibility for order creation tests.
     */
    public function inDistributionCenterMunicipality(): static
    {
        return $this->state(function () {
            // Get neighborhoods in municipalities that have distribution centers
            $validNeighborhoods = Neighborhood::whereHas('municipality', function ($query) {
                $query->whereIn('id', [8, 6, 2, 21]); // Douala I, Yaoundé VI, Yaoundé II, Commune Urbaine de Maroua
            })->get();

            $neighborhood = $validNeighborhoods->random();

            return [
                'neighborhood_id' => $neighborhood->id,
            ];
        });
    }
}
