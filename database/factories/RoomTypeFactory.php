<?php

namespace Database\Factories;

use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomTypeFactory extends Factory
{
    protected $model = RoomType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Standard', 'Deluxe', 'Suite', 'Executive', 'Presidential']),
            'description' => fake()->sentence(),
            'capacity' => fake()->numberBetween(1, 6),
            'price_per_night' => fake()->randomFloat(2, 1000, 10000),
            'amenities' => fake()->randomElements(['WiFi', 'TV', 'Air Conditioning', 'Mini Bar', 'Bathtub', 'Ocean View'], fake()->numberBetween(1, 5)),
            'icon' => fake()->optional()->randomElement(['bed', 'star', 'diamond']),
            'is_active' => true,
        ];
    }
}
