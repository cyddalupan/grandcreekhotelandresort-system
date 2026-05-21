<?php

namespace Database\Factories;

use App\Models\Housekeeping;
use App\Models\Room;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class HousekeepingFactory extends Factory
{
    protected $model = Housekeeping::class;

    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'assigned_to' => Employee::factory(),
            'completed_by' => null,
            'task_type' => fake()->randomElement(['cleaning', 'deep_clean', 'turndown', 'maintenance', 'inspection']),
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed']),
            'scheduled_date' => fake()->dateTimeBetween('-1 week', '+1 week'),
            'completed_at' => null,
            'notes' => fake()->optional(0.4)->sentence(),
        ];
    }
}
