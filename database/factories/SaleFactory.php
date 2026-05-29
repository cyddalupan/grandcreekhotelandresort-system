<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 10000);
        $taxPercent = 12;
        $taxAmount = round($subtotal * $taxPercent / 100, 2);
        $discount = fake()->optional(0.3)->randomFloat(2, 0, $subtotal * 0.2) ?? 0;
        $total = round($subtotal + $taxAmount - $discount, 2);
        $tendered = $total + fake()->optional(0.5)->randomFloat(2, 0, 500) ?? $total;

        return [
            'items' => [
                [
                    'item_id' => 1,
                    'name' => 'Sample Item',
                    'quantity' => 1,
                    'price' => $subtotal,
                    'total' => $subtotal,
                ],
            ],
            'subtotal' => $subtotal,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'discount' => $discount,
            'total' => $total,
            'payment_method' => fake()->randomElement(['cash', 'gcash', 'card', 'bank_transfer']),
            'tendered_amount' => $tendered,
            'change' => round(max(0, $tendered - $total), 2),
            'user_id' => User::factory(),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    public function cashPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'cash',
        ]);
    }

    public function gcashPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'gcash',
        ]);
    }

    public function withoutNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => null,
        ]);
    }
}
