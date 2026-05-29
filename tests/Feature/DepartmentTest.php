<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email_verified_at' => now()]);
    }

    // ─── Authentication ───────────────────────────────────────────

    public function test_unauthenticated_users_are_redirected_to_login()
    {
        $response = $this->get('/departments');
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_users_cannot_create()
    {
        $response = $this->get('/departments/create');
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_users_cannot_store()
    {
        $response = $this->post('/departments', [
            'name' => 'Unauthorized Department',
        ]);
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_users_cannot_edit()
    {
        $department = Department::factory()->create();

        $response = $this->get("/departments/{$department->id}/edit");
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_users_cannot_update()
    {
        $department = Department::factory()->create();

        $response = $this->put("/departments/{$department->id}", [
            'name' => 'Hacked Name',
        ]);
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_users_cannot_delete()
    {
        $department = Department::factory()->create();

        $response = $this->delete("/departments/{$department->id}");
        $response->assertRedirect('/login');
    }

    // ─── Index ────────────────────────────────────────────────────

    public function test_index_displays_all_departments_ordered_by_name()
    {
        $deptB = Department::factory()->create(['name' => 'B Department']);
        $deptA = Department::factory()->create(['name' => 'A Department']);
        $deptC = Department::factory()->create(['name' => 'C Department']);

        $response = $this->actingAs($this->user)->get('/departments');
        $response->assertStatus(200);
        $response->assertViewHas('departments');

        $departments = $response->viewData('departments');
        $this->assertEquals('A Department', $departments[0]->name);
        $this->assertEquals('B Department', $departments[1]->name);
        $this->assertEquals('C Department', $departments[2]->name);
    }

    public function test_index_paginates_departments()
    {
        Department::factory()->count(25)->create();

        $response = $this->actingAs($this->user)->get('/departments');
        $response->assertStatus(200);

        $this->assertCount(20, $response->viewData('departments'));
    }

    public function test_index_shows_empty_state_when_no_departments()
    {
        $response = $this->actingAs($this->user)->get('/departments');
        $response->assertStatus(200);
        $response->assertViewHas('departments');

        $this->assertCount(0, $response->viewData('departments'));
    }

    // ─── Create Form ──────────────────────────────────────────────

    public function test_create_form_loads_successfully()
    {
        $response = $this->actingAs($this->user)->get('/departments/create');
        $response->assertStatus(200);
    }

    // ─── Store ────────────────────────────────────────────────────

    public function test_store_creates_new_department()
    {
        $response = $this->actingAs($this->user)->post('/departments', [
            'name' => 'Front Desk',
            'description' => 'Manages guest check-in and reception',
            'manager' => 'Juan Dela Cruz',
        ]);

        $response->assertRedirect('/departments');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('departments', [
            'name' => 'Front Desk',
            'description' => 'Manages guest check-in and reception',
            'manager' => 'Juan Dela Cruz',
            'active' => true,
        ]);
    }

    public function test_store_creates_inactive_department()
    {
        $response = $this->actingAs($this->user)->post('/departments', [
            'name' => 'Renovation Crew',
            'active' => false,
        ]);

        $response->assertRedirect('/departments');

        $this->assertDatabaseHas('departments', [
            'name' => 'Renovation Crew',
            'active' => false,
        ]);
    }

    public function test_store_requires_name()
    {
        $response = $this->actingAs($this->user)->post('/departments', [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_requires_name_to_be_string()
    {
        $response = $this->actingAs($this->user)->post('/departments', [
            'name' => 12345,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_name_max_length()
    {
        $response = $this->actingAs($this->user)->post('/departments', [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_optional_fields_can_be_empty()
    {
        $response = $this->actingAs($this->user)->post('/departments', [
            'name' => 'Kitchen',
            'description' => null,
            'manager' => null,
        ]);

        $response->assertRedirect('/departments');

        $this->assertDatabaseHas('departments', [
            'name' => 'Kitchen',
            'description' => null,
            'manager' => null,
        ]);
    }

    // ─── Edit Form ────────────────────────────────────────────────

    public function test_edit_form_loads_for_existing_department()
    {
        $department = Department::factory()->create([
            'name' => 'Housekeeping',
            'description' => 'Room cleaning and maintenance',
            'manager' => 'Maria Santos',
        ]);

        $response = $this->actingAs($this->user)->get("/departments/{$department->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('Housekeeping');
        $response->assertSee('Maria Santos');
    }

    public function test_edit_returns_404_for_nonexistent_department()
    {
        $response = $this->actingAs($this->user)->get('/departments/99999/edit');
        $response->assertStatus(404);
    }

    // ─── Update ───────────────────────────────────────────────────

    public function test_update_changes_department_details()
    {
        $department = Department::factory()->create([
            'name' => 'Old Name',
            'manager' => null,
            'active' => true,
        ]);

        $response = $this->actingAs($this->user)->put("/departments/{$department->id}", [
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'manager' => 'New Manager',
            'active' => false,
        ]);

        $response->assertRedirect('/departments');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'manager' => 'New Manager',
            'active' => false,
        ]);
    }

    public function test_update_requires_name()
    {
        $department = Department::factory()->create();

        $response = $this->actingAs($this->user)->put("/departments/{$department->id}", [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_update_returns_404_for_nonexistent_department()
    {
        $response = $this->actingAs($this->user)->put('/departments/99999', [
            'name' => 'Should Fail',
        ]);
        $response->assertStatus(404);
    }

    // ─── Delete ───────────────────────────────────────────────────

    public function test_destroy_deletes_department()
    {
        $department = Department::factory()->create();
        $departmentId = $department->id;

        $response = $this->actingAs($this->user)->delete("/departments/{$department->id}");

        $response->assertRedirect('/departments');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('departments', ['id' => $departmentId]);
    }

    public function test_destroy_returns_404_for_nonexistent_department()
    {
        $response = $this->actingAs($this->user)->delete('/departments/99999');
        $response->assertStatus(404);
    }

    // ─── Route Naming ─────────────────────────────────────────────

    public function test_departments_index_route_named_correctly()
    {
        $response = $this->actingAs($this->user)->get(route('departments.index'));
        $response->assertStatus(200);
    }

    public function test_store_redirects_to_index()
    {
        $response = $this->actingAs($this->user)->post('/departments', [
            'name' => 'Redirect Test',
        ]);

        $response->assertRedirect(route('departments.index'));
    }

    public function test_update_redirects_to_index()
    {
        $department = Department::factory()->create();

        $response = $this->actingAs($this->user)->put("/departments/{$department->id}", [
            'name' => 'Updated Redirect Test',
        ]);

        $response->assertRedirect(route('departments.index'));
    }

    public function test_destroy_redirects_to_index()
    {
        $department = Department::factory()->create();

        $response = $this->actingAs($this->user)->delete("/departments/{$department->id}");

        $response->assertRedirect(route('departments.index'));
    }

    // ─── Data Integrity ───────────────────────────────────────────

    public function test_updating_one_department_does_not_affect_others()
    {
        $deptA = Department::factory()->create(['name' => 'Department A']);
        $deptB = Department::factory()->create(['name' => 'Department B']);

        $this->actingAs($this->user)->put("/departments/{$deptA->id}", [
            'name' => 'Updated A',
        ]);

        $this->assertDatabaseHas('departments', [
            'id' => $deptA->id,
            'name' => 'Updated A',
        ]);
        $this->assertDatabaseHas('departments', [
            'id' => $deptB->id,
            'name' => 'Department B',
        ]);
    }

    public function test_deleting_one_department_does_not_delete_others()
    {
        $deptA = Department::factory()->create();
        $deptB = Department::factory()->create();

        $this->actingAs($this->user)->delete("/departments/{$deptA->id}");

        $this->assertDatabaseMissing('departments', ['id' => $deptA->id]);
        $this->assertDatabaseHas('departments', ['id' => $deptB->id]);
    }
}
