<?php

namespace Database\Factories;

use App\Enums\DeliveryTrackingStatus;
use App\Models\DeliveryTracking;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryTracking>
 */
class DeliveryTrackingFactory extends Factory
{
    protected $model = DeliveryTracking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'status' => DeliveryTrackingStatus::PENDING(),
            'driver_lat' => $this->faker->latitude(48.8, 48.9), // Paris area
            'driver_lng' => $this->faker->longitude(2.2, 2.4),
            'current_speed' => $this->faker->randomFloat(1, 0, 60),
            'total_distance' => $this->faker->randomFloat(2, 1, 50),
            'distance_remaining' => $this->faker->randomFloat(2, 0, 25),
            'progress_percentage' => $this->faker->randomFloat(1, 0, 100),
            'estimated_duration' => $this->faker->numberBetween(5, 120),
            'route_geometry' => [
                'type' => 'LineString',
                'coordinates' => [
                    [$this->faker->longitude(2.2, 2.4), $this->faker->latitude(48.8, 48.9)],
                    [$this->faker->longitude(2.2, 2.4), $this->faker->latitude(48.8, 48.9)],
                ],
            ],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DeliveryTrackingStatus::PENDING(),
            'started_at' => null,
            'delivered_at' => null,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DeliveryTrackingStatus::IN_PROGRESS(),
            'started_at' => now()->subMinutes(30),
            'delivered_at' => null,
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DeliveryTrackingStatus::DELIVERED(),
            'started_at' => now()->subHours(2),
            'delivered_at' => now()->subMinutes(15),
            'distance_remaining' => 0,
            'progress_percentage' => 100,
        ]);
    }
}
