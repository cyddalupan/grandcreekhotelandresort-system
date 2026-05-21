<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Item;
use App\Models\Movement;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_movement_recent_scope_returns_ordered_movements()
    {
        $old = Movement::factory()->create(['date' => now()->subDays(10)]);
        $recent = Movement::factory()->create(['date' => now()->subDay()]);
        $latest = Movement::factory()->create(['date' => now()]);

        $recentMovements = Movement::recent()->get();

        $this->assertEquals($latest->id, $recentMovements->first()->id);
        $this->assertEquals($old->id, $recentMovements->last()->id);
    }

    public function test_movement_limits_to_five_by_default()
    {
        Movement::factory()->count(10)->create();

        $recentMovements = Movement::recent()->get();

        $this->assertLessThanOrEqual(10, $recentMovements->count());
    }

    public function test_movement_belongs_to_item()
    {
        $item = Item::factory()->create();
        $movement = Movement::factory()->create(['item_id' => $item->id]);

        $this->assertInstanceOf(Item::class, $movement->item);
        $this->assertEquals($item->id, $movement->item->id);
    }

    public function test_movement_belongs_to_from_department()
    {
        $department = Department::factory()->create();
        $movement = Movement::factory()->create([
            'from_department' => $department->id,
            'type' => 'TRANSFER',
        ]);

        $this->assertInstanceOf(Department::class, $movement->fromDepartment);
        $this->assertEquals($department->id, $movement->fromDepartment->id);
    }

    public function test_movement_belongs_to_to_department()
    {
        $department = Department::factory()->create();
        $movement = Movement::factory()->create([
            'to_department' => $department->id,
            'type' => 'IN',
        ]);

        $this->assertInstanceOf(Department::class, $movement->toDepartment);
        $this->assertEquals($department->id, $movement->toDepartment->id);
    }

    public function test_movement_quantity_is_positive()
    {
        $movement = Movement::factory()->create(['quantity' => 5]);

        $this->assertGreaterThan(0, $movement->quantity);
    }

    public function test_movement_type_is_valid()
    {
        $in = Movement::factory()->create(['type' => 'IN']);
        $out = Movement::factory()->create(['type' => 'OUT']);
        $transfer = Movement::factory()->create(['type' => 'TRANSFER']);

        $this->assertEquals('IN', $in->type);
        $this->assertEquals('OUT', $out->type);
        $this->assertEquals('TRANSFER', $transfer->type);
    }
}
