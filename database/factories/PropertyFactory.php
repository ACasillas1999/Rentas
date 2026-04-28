<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        return [
            'name'    => fake()->company() . ' Building',
            'type'    => fake()->randomElement(['commercial', 'residential', 'mixed']),
            'address' => fake()->streetAddress(),
            'city'    => fake()->city(),
            'state'   => fake()->state(),
            'notes'   => null,
        ];
    }
}
