<?php

namespace Database\Factories\Geography;

use App\Models\Geography\Neighborhood;
use Illuminate\Database\Eloquent\Factories\Factory;

class NeighborhoodFactory extends Factory
{
    protected $model = Neighborhood::class;

    public function definition(): array
    {
        return [
            'municipality_id' => \Database\Factories\Geography\MunicipalityFactory::new(),
            'name' => $this->faker->streetAddress,
            'latitude' => $this->faker->latitude(4.0, 5.0),
            'longitude' => $this->faker->longitude(9.0, 10.0),
            'is_active' => true,
        ];
    }
}
