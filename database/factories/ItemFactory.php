<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word() . ' ' . fake()->randomElement(['Supply', 'Item', 'Product', 'Material']),
            'category' => fake()->randomElement(['Linen', 'Toiletries', 'Chemicals', 'Food', 'Beverage', 'Glassware', 'Electrical', 'Maintenance', 'Spa Supplies', 'Furniture']),
            'department_id' => Department::factory(),
            'supplier_id' => Supplier::factory(),
            'current_stock' => fake()->numberBetween(1, 100),
            'min_stock' => 10,
            'unit' => fake()->randomElement(['pieces', 'kg', 'liters', 'boxes', 'bottles', 'packs']),
            'purchase_cost' => fake()->randomFloat(2, 10, 500),
            'selling_price' => fake()->randomFloat(2, 20, 800),
            'expiry_date' => fake()->optional(0.3)->dateTimeBetween('+1 month', '+1 year'),
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_stock' => fake()->numberBetween(0, 5),
            'min_stock' => 10,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_stock' => 0,
            'min_stock' => 10,
        ]);
    }

    public function withDepartment(Department $department): static
    {
        return $this->state(fn (array $attributes) => [
            'department_id' => $department->id,
        ]);
    }
}
