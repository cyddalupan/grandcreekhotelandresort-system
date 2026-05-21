<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'room_number' => fake()->unique()->numerify('###'),
            'room_type_id' => RoomType::factory(),
            'floor' => fake()->numberBetween(1, 5),
            'status' => fake()->randomElement(['available', 'occupied', 'maintenance']),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }
}
