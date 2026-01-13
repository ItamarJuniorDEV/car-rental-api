<?php

namespace Database\Factories;

use App\Models\Line;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarFactory extends Factory
{
    public function definition()
    {
        return [
            'line_id' => Line::factory(),
            'plate' => $this->faker->regexify('[A-Z]{3}-[0-9][A-Z][0-9]{2}'),
            'available' => true,
            'km' => $this->faker->numberBetween(5000, 120000),
        ];
    }
}
