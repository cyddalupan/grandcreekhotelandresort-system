<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional(0.8)->phoneNumber(),
            'position' => fake()->jobTitle(),
            'department_id' => Department::factory(),
            'hire_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'salary' => fake()->randomFloat(2, 10000, 100000),
            'status' => fake()->randomElement(['active', 'inactive', 'suspended']),
        ];
    }
}
