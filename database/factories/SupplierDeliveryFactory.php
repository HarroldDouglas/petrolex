<?php

namespace Database\Factories;

use App\Enums\SupplierDeliveryStatus;
use App\Models\DistributionCenter;
use App\Models\SupplierDelivery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupplierDelivery>
 */
class SupplierDeliveryFactory extends Factory
{
    protected $model = SupplierDelivery::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::inRandomOrder()->first();
        $distributionCenter = DistributionCenter::inRandomOrder()->first();

        return [
            'distribution_center_id' => $distributionCenter->id,
            'user_id' => $user->id,
            'delivery_number' => 'DEL-'.fake()->unique()->bothify('######'),
            'title' => fake()->sentence(),
            'supplier_name' => fake()->company(),
            'supply_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'status' => SupplierDeliveryStatus::COMPLETED()->value,
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SupplierDeliveryStatus::IN_PROGRESS()->value,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SupplierDeliveryStatus::COMPLETED()->value,
        ]);
    }
}
