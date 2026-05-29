<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    // ── Existing tests (preserved) ──

    public function test_employee_full_name_attribute(): void
    {
        $employee = Employee::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
        ]);

        $this->assertEquals('Juan Dela Cruz', $employee->full_name);
    }

    public function test_employee_belongs_to_department(): void
    {
        $department = Department::factory()->create();
        $employee = Employee::factory()->create(['department_id' => $department->id]);

        $this->assertInstanceOf(Department::class, $employee->department);
        $this->assertEquals($department->id, $employee->department->id);
    }

    public function test_employee_email_is_set(): void
    {
        $employee = Employee::factory()->create([
            'email' => 'juan@example.com',
        ]);

        $this->assertEquals('juan@example.com', $employee->email);
    }

    public function test_employee_position_is_set(): void
    {
        $employee = Employee::factory()->create([
            'position' => 'Front Desk Manager',
        ]);

        $this->assertEquals('Front Desk Manager', $employee->position);
    }

    public function test_employee_phone_can_be_nullable(): void
    {
        $employee = Employee::factory()->create(['phone' => null]);

        $this->assertNull($employee->phone);
    }

    public function test_employee_hire_date_is_a_date(): void
    {
        $employee = Employee::factory()->create();

        $this->assertInstanceOf(\Carbon\Carbon::class, $employee->hire_date);
    }

    public function test_employee_status_default(): void
    {
        $employee = Employee::factory()->create();

        $this->assertContains($employee->status, ['active', 'inactive', 'terminated']);
        $this->assertNotNull($employee->employee_id);
        $this->assertStringStartsWith('EMP-', $employee->employee_id);
    }

    // ── New business-logic tests ──

    public function test_employee_id_is_sequential(): void
    {
        $emp1 = Employee::factory()->create();
        $emp2 = Employee::factory()->create();
        $emp3 = Employee::factory()->create();

        $id1 = (int) substr($emp1->employee_id, 4);
        $id2 = (int) substr($emp2->employee_id, 4);
        $id3 = (int) substr($emp3->employee_id, 4);

        $this->assertEquals($id1 + 1, $id2);
        $this->assertEquals($id2 + 1, $id3);
    }

    public function test_employee_id_format_is_three_digit_padded(): void
    {
        $employee = Employee::factory()->create([
            'employee_id' => 'EMP-042',
        ]);

        $this->assertEquals('EMP-042', $employee->employee_id);
        $this->assertMatchesRegularExpression('/^EMP-\d{3}$/', $employee->employee_id);
    }

    public function test_employee_full_name_with_middle_initial_is_not_included(): void
    {
        $employee = Employee::factory()->create([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
        ]);

        $this->assertEquals('Maria Santos', $employee->full_name);
        $this->assertStringNotContainsString('R.', $employee->full_name);
    }

    public function test_employee_salary_is_decimal_with_two_places(): void
    {
        $employee = Employee::factory()->create(['salary' => 50000]);

        $this->assertEquals(50000.00, (float) $employee->salary);
    }

    public function test_employee_salary_can_be_zero(): void
    {
        $employee = Employee::factory()->create(['salary' => 0]);

        $this->assertEquals(0, (float) $employee->salary);
    }

    public function test_employee_can_have_null_department(): void
    {
        $employee = Employee::factory()->create(['department_id' => null]);

        $this->assertNull($employee->department_id);
        $this->assertNull($employee->department);
    }

    public function test_employee_address_is_nullable(): void
    {
        $employee = Employee::factory()->create(['address' => null]);

        $this->assertNull($employee->address);
    }

    public function test_employee_emergency_contact_and_phone_are_nullable(): void
    {
        $employee = Employee::factory()->create([
            'emergency_contact' => null,
            'emergency_phone' => null,
        ]);

        $this->assertNull($employee->emergency_contact);
        $this->assertNull($employee->emergency_phone);
    }

    public function test_employee_status_can_be_active(): void
    {
        $employee = Employee::factory()->create(['status' => 'active']);

        $this->assertEquals('active', $employee->status);
    }

    public function test_employee_status_can_be_inactive(): void
    {
        $employee = Employee::factory()->create(['status' => 'inactive']);

        $this->assertEquals('inactive', $employee->status);
    }

    public function test_employee_status_can_be_terminated(): void
    {
        $employee = Employee::factory()->create(['status' => 'terminated']);

        $this->assertEquals('terminated', $employee->status);
    }

    public function test_employee_hire_date_is_formatted_as_date(): void
    {
        $employee = Employee::factory()->create([
            'hire_date' => '2025-06-15',
        ]);

        $this->assertEquals('2025-06-15', $employee->hire_date->format('Y-m-d'));
    }

    public function test_employee_can_have_email_nullable(): void
    {
        $employee = Employee::factory()->create(['email' => null]);

        $this->assertNull($employee->email);
    }

    public function test_employee_position_accepts_long_titles(): void
    {
        $employee = Employee::factory()->create([
            'position' => 'Senior Front Desk Manager & Guest Relations Officer',
        ]);

        $this->assertEquals('Senior Front Desk Manager & Guest Relations Officer', $employee->position);
    }

    public function test_employee_can_be_queried_by_status(): void
    {
        Employee::factory()->count(3)->create(['status' => 'active']);
        Employee::factory()->count(2)->create(['status' => 'inactive']);

        $this->assertCount(3, Employee::where('status', 'active')->get());
        $this->assertCount(2, Employee::where('status', 'inactive')->get());
        $this->assertCount(5, Employee::all());
    }

    public function test_employee_employee_id_must_be_unique(): void
    {
        Employee::factory()->create(['employee_id' => 'EMP-001']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Employee::factory()->create(['employee_id' => 'EMP-001']);
    }

    public function test_employee_can_be_created_without_optional_fields(): void
    {
        $employee = Employee::factory()->create([
            'email' => null,
            'phone' => null,
            'address' => null,
            'emergency_contact' => null,
            'emergency_phone' => null,
        ]);

        $this->assertNotNull($employee->id);
        $this->assertNull($employee->email);
        $this->assertNull($employee->phone);
    }
}
