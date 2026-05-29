<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'po_number' => 'PO-' . now()->format('ymd') . '-' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'supplier_id' => Supplier::factory(),
            'items' => [
                [
                    'name' => fake()->word(),
                    'qty' => fake()->numberBetween(1, 50),
                    'unit' => fake()->randomElement(['pcs', 'kg', 'liters', 'boxes']),
                    'unit_price' => fake()->randomFloat(2, 10, 5000),
                    'total' => fake()->randomFloat(2, 100, 50000),
                ],
            ],
            'total_amount' => fake()->randomFloat(2, 1000, 100000),
            'status' => 'draft',
            'created_by' => User::factory(),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }
}
