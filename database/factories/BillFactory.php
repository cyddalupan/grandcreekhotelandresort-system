<?php

namespace Database\Factories;

use App\Models\Bill;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillFactory extends Factory
{
    protected $model = Bill::class;

    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['Electricity', 'Water', 'Internet', 'Telephone', 'Gas', 'Maintenance', 'Insurance', 'Rent']),
            'provider' => fake()->company(),
            'account_number' => fake()->optional(0.7)->numerify('##########'),
            'amount' => fake()->randomFloat(2, 500, 50000),
            'due_date' => fake()->dateTimeBetween('-2 months', '+1 month'),
            'status' => 'Pending',
            'billing_period' => fake()->optional(0.8)->monthName() . ' ' . fake()->year(),
            'payment_date' => null,
            'payment_method' => null,
            'payment_reference' => null,
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Paid',
            'payment_date' => fake()->dateTimeBetween('-2 months', 'now'),
            'payment_method' => fake()->randomElement(['Bank Transfer', 'Cash', 'Check', 'GCash']),
            'payment_reference' => fake()->optional(0.8)->bothify('REF-####-????'),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Overdue',
            'due_date' => fake()->dateTimeBetween('-3 months', '-1 day'),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Pending',
            'due_date' => fake()->dateTimeBetween('-1 month', '+1 month'),
        ]);
    }
}
