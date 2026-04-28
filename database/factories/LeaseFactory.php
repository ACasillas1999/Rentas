<?php

namespace Database\Factories;

use App\Models\Lease;
use App\Models\Tenant;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaseFactory extends Factory
{
    protected $model = Lease::class;

    public function definition(): array
    {
        $start = Carbon::now()->subMonths(fake()->numberBetween(1, 6));
        $end   = $start->copy()->addYear();

        return [
            'unit_id'            => Unit::factory(),
            'tenant_id'          => Tenant::factory(),
            'contract_number'    => 'CONT-' . fake()->unique()->numerify('####'),
            'start_date'         => $start->toDateString(),
            'end_date'           => $end->toDateString(),
            'first_period_start' => $start->toDateString(),
            'monthly_amount'     => fake()->randomFloat(2, 5000, 25000),
            'maintenance_amount' => fake()->randomFloat(2, 500, 3000),
            'deposit_amount'     => null,
            'status'             => 'active',
            'notes'              => null,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function finished(): static
    {
        return $this->state(['status' => 'finished']);
    }
}
