<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Item;
use App\Models\Movement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    // ─── Model Attributes ─────────────────────────────────────────

    public function test_department_has_name()
    {
        $department = Department::factory()->create([
            'name' => 'Housekeeping',
        ]);

        $this->assertEquals('Housekeeping', $department->name);
    }

    public function test_department_has_optional_description()
    {
        $department = Department::factory()->create([
            'description' => 'Handles room maintenance and cleaning',
        ]);

        $this->assertEquals('Handles room maintenance and cleaning', $department->description);
    }

    public function test_department_description_can_be_null()
    {
        $department = Department::factory()->create([
            'description' => null,
        ]);

        $this->assertNull($department->description);
    }

    public function test_department_has_optional_manager()
    {
        $department = Department::factory()->create([
            'manager' => 'Juan Dela Cruz',
        ]);

        $this->assertEquals('Juan Dela Cruz', $department->manager);
    }

    public function test_department_manager_can_be_null()
    {
        $department = Department::factory()->create([
            'manager' => null,
        ]);

        $this->assertNull($department->manager);
    }

    public function test_department_is_active_by_default()
    {
        $department = Department::factory()->create();

        $this->assertTrue($department->active);
    }

    public function test_active_is_cast_to_boolean()
    {
        $department = Department::factory()->create(['active' => 1]);

        $this->assertIsBool($department->active);
        $this->assertTrue($department->active);
    }

    public function test_department_can_be_inactive()
    {
        $department = Department::factory()->inactive()->create();

        $this->assertFalse($department->active);
    }

    public function test_item_count_defaults_to_zero()
    {
        $department = Department::factory()->create();

        $this->assertEquals(0, $department->item_count);
    }

    // ─── Relationships ────────────────────────────────────────────

    public function test_department_has_many_items()
    {
        $department = Department::factory()->create();
        $items = Item::factory()->count(3)->create([
            'department_id' => $department->id,
        ]);

        $this->assertCount(3, $department->items);
        $this->assertInstanceOf(Item::class, $department->items->first());
        $this->assertEquals($items->pluck('id')->sort()->values(), $department->items->pluck('id')->sort()->values());
    }

    public function test_department_has_many_movements_from()
    {
        $department = Department::factory()->create();
        $movements = Movement::factory()->count(2)->create([
            'from_department' => $department->id,
        ]);

        $this->assertCount(2, $department->movementsFrom);
        $this->assertInstanceOf(Movement::class, $department->movementsFrom->first());
    }

    public function test_department_has_many_movements_to()
    {
        $department = Department::factory()->create();
        $movements = Movement::factory()->count(2)->create([
            'to_department' => $department->id,
        ]);

        $this->assertCount(2, $department->movementsTo);
        $this->assertInstanceOf(Movement::class, $department->movementsTo->first());
    }

    public function test_department_does_not_include_other_departments_items()
    {
        $deptA = Department::factory()->create();
        $deptB = Department::factory()->create();

        Item::factory()->count(2)->create(['department_id' => $deptA->id]);
        Item::factory()->count(3)->create(['department_id' => $deptB->id]);

        $this->assertCount(2, $deptA->items);
        $this->assertCount(3, $deptB->items);
    }

    // ─── Soft Deletion Behavior ───────────────────────────────────

    public function test_department_can_be_deleted()
    {
        $department = Department::factory()->create();
        $departmentId = $department->id;

        $department->delete();

        $this->assertDatabaseMissing('departments', ['id' => $departmentId]);
    }

    public function test_deleting_department_does_not_affect_other_departments()
    {
        $deptA = Department::factory()->create();
        $deptB = Department::factory()->create();

        $deptA->delete();

        $this->assertDatabaseHas('departments', ['id' => $deptB->id]);
    }

    // ─── Fillable / Mass Assignment ───────────────────────────────

    public function test_department_can_be_created_via_mass_assignment()
    {
        $data = [
            'name' => 'Front Desk',
            'description' => 'Guest reception and check-in services',
            'manager' => 'Maria Santos',
            'active' => true,
        ];

        $department = Department::create($data);

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'name' => 'Front Desk',
        ]);
    }

    public function test_department_can_be_updated_via_mass_assignment()
    {
        $department = Department::factory()->create([
            'name' => 'Old Name',
            'manager' => null,
        ]);

        $department->update([
            'name' => 'Updated Name',
            'manager' => 'New Manager',
        ]);

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'name' => 'Updated Name',
            'manager' => 'New Manager',
        ]);
    }
}
