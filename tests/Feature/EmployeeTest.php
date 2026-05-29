<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email_verified_at' => now()]);
        $this->department = Department::factory()->create(['active' => true, 'name' => 'Housekeeping']);
    }

    // ── Auth gates ──

    public function test_unauthenticated_user_is_redirected_to_login_for_index(): void
    {
        $this->get(route('employees.index'))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_store(): void
    {
        $this->post(route('employees.store'), [
            'first_name' => 'Test',
            'last_name' => 'User',
        ])->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_edit(): void
    {
        $employee = Employee::factory()->create();

        $this->get(route('employees.edit', $employee))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_update(): void
    {
        $employee = Employee::factory()->create();

        $this->put(route('employees.update', $employee), [
            'first_name' => 'Test',
        ])->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_for_destroy(): void
    {
        $employee = Employee::factory()->create();

        $this->delete(route('employees.destroy', $employee))->assertRedirect(route('login'));
    }

    // ── Index ──

    public function test_employees_index_lists_employees(): void
    {
        Employee::factory()->count(3)->create([
            'department_id' => $this->department->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('employees.index'));
        $response->assertStatus(200);
        $response->assertSee($this->department->name);
    }

    public function test_employees_index_shows_stats(): void
    {
        Employee::factory()->count(2)->create(['status' => 'active', 'department_id' => $this->department->id]);
        Employee::factory()->count(1)->create(['status' => 'inactive', 'department_id' => $this->department->id]);

        $response = $this->actingAs($this->user)->get(route('employees.index'));
        $response->assertStatus(200);
        $response->assertSee('3'); // total
    }

    public function test_employees_index_shows_empty_state(): void
    {
        $response = $this->actingAs($this->user)->get(route('employees.index'));
        $response->assertStatus(200);
    }

    // ── Create ──

    public function test_employees_create_form_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('employees.create'));
        $response->assertStatus(200);
        $response->assertSee($this->department->name);
        $response->assertSee('EMP-001');
    }

    // ── Store ──

    public function test_employees_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(route('employees.store'), [
            'employee_id' => 'EMP-001',
            'department_id' => $this->department->id,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'position' => 'Front Desk Manager',
            'hire_date' => '2025-01-15',
            'salary' => 35000,
            'email' => 'juan@example.com',
            'phone' => '09171234567',
            'address' => '123 Main St',
            'emergency_contact' => 'Maria Dela Cruz',
            'emergency_phone' => '09179876543',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('employees.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('employees', [
            'employee_id' => 'EMP-001',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.com',
        ]);
    }

    public function test_employees_store_with_minimal_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('employees.store'), [
            'employee_id' => 'EMP-002',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'position' => 'Housekeeper',
            'hire_date' => '2025-03-01',
            'salary' => 20000,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('employees.index'));
        $this->assertDatabaseHas('employees', [
            'employee_id' => 'EMP-002',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
        ]);
    }

    public function test_employees_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('employees.store'), [
            'employee_id' => '',
            'first_name' => '',
            'last_name' => '',
            'position' => '',
            'hire_date' => '',
            'salary' => '',
            'status' => '',
        ]);

        $response->assertSessionHasErrors([
            'employee_id', 'first_name', 'last_name', 'position', 'hire_date', 'salary', 'status',
        ]);
    }

    public function test_employees_store_validates_unique_employee_id(): void
    {
        Employee::factory()->create(['employee_id' => 'EMP-001']);

        $response = $this->actingAs($this->user)->post(route('employees.store'), [
            'employee_id' => 'EMP-001',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'position' => 'Housekeeper',
            'hire_date' => '2025-01-01',
            'salary' => 20000,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('employee_id');
    }

    public function test_employees_store_validates_status_values(): void
    {
        $response = $this->actingAs($this->user)->post(route('employees.store'), [
            'employee_id' => 'EMP-005',
            'first_name' => 'Test',
            'last_name' => 'User',
            'position' => 'Staff',
            'hire_date' => '2025-01-01',
            'salary' => 20000,
            'status' => 'suspended',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_employees_store_rejects_negative_salary(): void
    {
        $response = $this->actingAs($this->user)->post(route('employees.store'), [
            'employee_id' => 'EMP-006',
            'first_name' => 'Test',
            'last_name' => 'User',
            'position' => 'Staff',
            'hire_date' => '2025-01-01',
            'salary' => -1000,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('salary');
    }

    // ── Show ──

    public function test_employees_show_displays_employee(): void
    {
        $employee = Employee::factory()->create([
            'department_id' => $this->department->id,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
        ]);

        $response = $this->actingAs($this->user)->get(route('employees.show', $employee));
        $response->assertStatus(200);
        $response->assertSee('Juan');
        $response->assertSee('Dela Cruz');
    }

    // ── Edit ──

    public function test_employees_edit_form_loads(): void
    {
        $employee = Employee::factory()->create([
            'department_id' => $this->department->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('employees.edit', $employee));
        $response->assertStatus(200);
        $response->assertSee($employee->first_name);
        $response->assertSee($employee->employee_id);
    }

    // ── Update ──

    public function test_employees_can_be_updated(): void
    {
        $employee = Employee::factory()->create([
            'first_name' => 'Old Name',
            'department_id' => $this->department->id,
        ]);

        $response = $this->actingAs($this->user)->put(route('employees.update', $employee), [
            'employee_id' => $employee->employee_id,
            'department_id' => $this->department->id,
            'first_name' => 'Updated Name',
            'last_name' => $employee->last_name,
            'position' => 'Senior Manager',
            'hire_date' => $employee->hire_date->format('Y-m-d'),
            'salary' => 50000,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('employees.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'first_name' => 'Updated Name',
            'position' => 'Senior Manager',
        ]);
    }

    public function test_employees_update_preserves_different_employees_with_same_id(): void
    {
        $emp1 = Employee::factory()->create(['employee_id' => 'EMP-001']);
        $emp2 = Employee::factory()->create(['employee_id' => 'EMP-002']);

        // Updating emp2 should allow emp2's OWN employee_id
        $response = $this->actingAs($this->user)->put(route('employees.update', $emp2), [
            'employee_id' => 'EMP-002',
            'first_name' => $emp2->first_name,
            'last_name' => $emp2->last_name,
            'position' => $emp2->position,
            'hire_date' => $emp2->hire_date->format('Y-m-d'),
            'salary' => $emp2->salary,
            'status' => 'active',
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_employees_update_validates_unique_employee_id_on_change(): void
    {
        $emp1 = Employee::factory()->create(['employee_id' => 'EMP-001']);
        $emp2 = Employee::factory()->create(['employee_id' => 'EMP-002']);

        $response = $this->actingAs($this->user)->put(route('employees.update', $emp2), [
            'employee_id' => 'EMP-001', // already taken by emp1
            'first_name' => $emp2->first_name,
            'last_name' => $emp2->last_name,
            'position' => $emp2->position,
            'hire_date' => $emp2->hire_date->format('Y-m-d'),
            'salary' => $emp2->salary,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('employee_id');
    }

    // ── Destroy ──

    public function test_employees_can_be_deleted(): void
    {
        $employee = Employee::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('employees.destroy', $employee));

        $response->assertRedirect(route('employees.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }
}
