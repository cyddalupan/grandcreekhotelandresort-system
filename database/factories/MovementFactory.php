<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Item;
use App\Models\Movement;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovementFactory extends Factory
{
    protected $model = Movement::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['IN', 'OUT', 'TRANSFER']);

        return [
            'item_id' => Item::factory(),
            'type' => $type,
            'quantity' => fake()->numberBetween(1, 50),
            'from_department' => $type === 'TRANSFER' ? Department::factory() : null,
            'to_department' => $type === 'TRANSFER' || $type === 'IN' ? Department::factory() : null,
            'reason' => fake()->optional(0.6)->sentence(),
            'user' => fake()->name(),
            'notes' => fake()->optional(0.4)->sentence(),
            'date' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function stockIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'IN',
            'to_department' => Department::factory(),
            'from_department' => null,
        ]);
    }

    public function stockOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'OUT',
            'from_department' => Department::factory(),
            'to_department' => null,
        ]);
    }

    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'TRANSFER',
            'from_department' => Department::factory(),
            'to_department' => Department::factory(),
        ]);
    }
}
