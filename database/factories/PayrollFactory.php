<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollFactory extends Factory
{
    protected $model = Payroll::class;

    public function definition(): array
    {
        $gross = fake()->randomFloat(2, 5000, 80000);
        $deductions = round($gross * 0.10, 2);

        return [
            'employee_id'  => Employee::factory(),
            'period_start' => fake()->dateTimeBetween('-3 months', '-1 month')->format('Y-m-d'),
            'period_end'   => fn (array $a) => date('Y-m-d', strtotime($a['period_start'] . ' +14 days')),
            'work_days'    => fake()->numberBetween(10, 22),
            'gross_pay'    => $gross,
            'deductions'   => $deductions,
            'net_pay'      => $gross - $deductions,
            'status'       => 'draft',
            'paid_at'      => null,
            'notes'        => fake()->optional(0.3)->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending', 'paid_at' => null]);
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status'  => 'paid',
            'paid_at' => now()->subDays(fake()->numberBetween(1, 14)),
        ]);
    }
}
