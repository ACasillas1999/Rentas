<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'code'        => strtoupper(fake()->bothify('L-###')),
            'floor'       => fake()->randomElement(['PB', '1', '2', '3']),
            'area_m2'     => fake()->randomFloat(2, 20, 300),
            'status'      => 'available',
            'notes'       => null,
        ];
    }

    public function rented(): static
    {
        return $this->state(['status' => 'rented']);
    }

    public function available(): static
    {
        return $this->state(['status' => 'available']);
    }
}
