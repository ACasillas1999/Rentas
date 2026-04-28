<?php

namespace Database\Factories;

use App\Models\Lease;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 5000, 30000);

        return [
            'lease_id'      => Lease::factory(),
            'type'          => 'rent',
            'period_label'  => '1 Ene – 31 Ene 2026 (1/12)',
            'period_start'  => now()->startOfMonth()->toDateString(),
            'period_end'    => now()->endOfMonth()->toDateString(),
            'period_number' => 1,
            'total_periods' => 12,
            'due_date'      => now()->endOfMonth()->toDateString(),
            'amount'        => $amount,
            'subtotal'      => $amount / 1.16,
            'tax_amount'    => $amount - ($amount / 1.16),
            'status'        => 'pending',
            'paid_at'       => null,
            'paid_amount'   => null,
        ];
    }

    public function paid(): static
    {
        $amount = fake()->randomFloat(2, 5000, 30000);
        return $this->state([
            'status'      => 'paid',
            'paid_at'     => now()->toDateString(),
            'paid_amount' => $amount,
            'amount'      => $amount,
        ]);
    }

    public function overdue(): static
    {
        return $this->state([
            'status'   => 'overdue',
            'due_date' => now()->subDays(5)->toDateString(),
        ]);
    }
}
