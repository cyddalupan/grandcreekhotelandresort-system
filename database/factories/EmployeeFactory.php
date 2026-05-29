<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    private static int $employeeSeq = 1;

    public function definition(): array
    {
        return [
            'employee_id' => 'EMP-' . str_pad(self::$employeeSeq++, 3, '0', STR_PAD_LEFT),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional(0.8)->phoneNumber(),
            'position' => fake()->jobTitle(),
            'department_id' => Department::factory(),
            'hire_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'salary' => fake()->randomFloat(2, 10000, 100000),
            'status' => fake()->randomElement(['active', 'inactive', 'terminated']),
        ];
    }
}
