<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Simple Order factory for testing - doesn't depend on Customer/DistributionCenter
 */
class SimpleOrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 1000, 5000);
        $deliveryFee = $this->faker->randomFloat(2, 100, 500);

        return [
            'customer_id' => 1, // Simple fixed value for tests
            'distribution_center_id' => 1,
            'delivery_address_id' => 1,
            'delivery_person_id' => null,
            'order_number' => 'ORD-'.$this->faker->unique()->bothify('######'),
            'delivery_type' => 'normal',
            'status' => OrderStatus::PAID(),
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total_amount' => $subtotal + $deliveryFee,
            'order_date' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::PAID(),
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::PROCESSING(),
        ]);
    }
}
