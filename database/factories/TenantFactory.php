<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'full_name'   => fake()->name(),
            'document_id' => fake()->numerify('RFC-########'),
            'phone'       => fake()->phoneNumber(),
            'email'       => fake()->unique()->safeEmail(),
            'address'     => fake()->address(),
            'notes'       => null,
        ];
    }
}
