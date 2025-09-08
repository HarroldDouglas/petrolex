<?php

namespace Database\Factories;

use App\Models\DistributionCenter;
use App\Models\User;
use App\Models\UserDistributionCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserDistributionCenter>
 */
class UserDistributionCenterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserDistributionCenter::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'distribution_center_id' => DistributionCenter::factory(),
        ];
    }
}
