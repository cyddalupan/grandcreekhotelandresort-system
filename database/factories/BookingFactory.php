<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Room;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('now', '+1 month');
        $checkOut = (clone $checkIn)->modify('+' . fake()->numberBetween(1, 7) . ' days');

        return [
            'room_id' => Room::factory(),
            'guest_name' => fake()->name(),
            'guest_email' => fake()->email(),
            'guest_phone' => fake()->phoneNumber(),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'adults' => fake()->numberBetween(1, 4),
            'children' => fake()->numberBetween(0, 3),
            'status' => fake()->randomElement(['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled']),
            'total_amount' => fake()->randomFloat(2, 2000, 50000),
            'paid_amount' => 0,
            
        ];
    }
}
