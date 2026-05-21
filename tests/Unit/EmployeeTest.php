<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_full_name_attribute()
    {
        $employee = Employee::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
        ]);

        $this->assertEquals('Juan Dela Cruz', $employee->full_name);
    }

    public function test_employee_belongs_to_department()
    {
        $department = Department::factory()->create();
        $employee = Employee::factory()->create(['department_id' => $department->id]);

        $this->assertInstanceOf(Department::class, $employee->department);
        $this->assertEquals($department->id, $employee->department->id);
    }

    public function test_employee_email_is_set()
    {
        $employee = Employee::factory()->create([
            'email' => 'juan@example.com',
        ]);

        $this->assertEquals('juan@example.com', $employee->email);
    }

    public function test_employee_position_is_set()
    {
        $employee = Employee::factory()->create([
            'position' => 'Front Desk Manager',
        ]);

        $this->assertEquals('Front Desk Manager', $employee->position);
    }

    public function test_employee_phone_can_be_nullable()
    {
        $employee = Employee::factory()->create([
            'phone' => null,
        ]);

        $this->assertNull($employee->phone);
    }

    public function test_employee_hire_date_is_a_date()
    {
        $employee = Employee::factory()->create();

        $this->assertInstanceOf(\Carbon\Carbon::class, $employee->hire_date);
    }

    public function test_employee_status_default()
    {
        $employee = Employee::factory()->create();

        $this->assertContains($employee->status, ['active', 'inactive', 'terminated']);
    }
}
