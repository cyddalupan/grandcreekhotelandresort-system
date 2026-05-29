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

    /*
     * Scope: recent movements by date
     */

    public function test_movement_recent_scope_returns_ordered_movements()
    {
        $old = Movement::factory()->create(['date' => now()->subDays(10)]);
        $recent = Movement::factory()->create(['date' => now()->subDay()]);
        $latest = Movement::factory()->create(['date' => now()]);

        $recentMovements = Movement::recent()->get();

        $this->assertEquals($latest->id, $recentMovements->first()->id);
        $this->assertEquals($old->id, $recentMovements->last()->id);
    }

    public function test_movement_recent_scope_returns_all_movements_ordered()
    {
        Movement::factory()->count(10)->create();

        $recentMovements = Movement::recent()->get();

        $this->assertCount(10, $recentMovements);
    }

    /*
     * Business logic: stock movement types
     */

    public function test_movement_type_is_valid()
    {
        $in = Movement::factory()->create(['type' => 'IN']);
        $out = Movement::factory()->create(['type' => 'OUT']);
        $transfer = Movement::factory()->create(['type' => 'TRANSFER']);

        $this->assertEquals('IN', $in->type);
        $this->assertEquals('OUT', $out->type);
        $this->assertEquals('TRANSFER', $transfer->type);
    }

    public function test_movement_stock_in_has_destination_department()
    {
        $department = Department::factory()->create();
        $movement = Movement::factory()->stockIn()->create([
            'to_department' => $department->id,
        ]);

        $this->assertEquals('IN', $movement->type);
        $this->assertNotNull($movement->to_department);
        $this->assertNull($movement->from_department);
    }

    public function test_movement_stock_out_has_source_department()
    {
        $department = Department::factory()->create();
        $movement = Movement::factory()->stockOut()->create([
            'from_department' => $department->id,
        ]);

        $this->assertEquals('OUT', $movement->type);
        $this->assertNotNull($movement->from_department);
        $this->assertNull($movement->to_department);
    }

    public function test_movement_transfer_has_both_departments()
    {
        $from = Department::factory()->create();
        $to = Department::factory()->create();
        $movement = Movement::factory()->transfer()->create([
            'from_department' => $from->id,
            'to_department' => $to->id,
        ]);

        $this->assertEquals('TRANSFER', $movement->type);
        $this->assertNotNull($movement->from_department);
        $this->assertNotNull($movement->to_department);
    }

    /*
     * Business logic: quantity must be positive
     */

    public function test_movement_quantity_is_positive()
    {
        $movement = Movement::factory()->create(['quantity' => 5]);

        $this->assertGreaterThan(0, $movement->quantity);
    }

    public function test_movement_quantity_tracks_inventory_change_magnitude()
    {
        $items = Movement::factory()->stockIn()->create(['quantity' => 25]);

        $this->assertEquals(25, $items->quantity);
    }

    /*
     * Business logic: audit trail
     */

    public function test_movement_records_who_performed_it()
    {
        $movement = Movement::factory()->create(['user' => 'Juan Dela Cruz']);

        $this->assertEquals('Juan Dela Cruz', $movement->user);
        $this->assertNotNull($movement->user);
    }

    public function test_movement_date_is_tracked_as_datetime()
    {
        $movement = Movement::factory()->create(['date' => now()]);

        $this->assertInstanceOf(Carbon::class, $movement->date);
    }

    public function test_movement_can_have_reason_and_notes()
    {
        $movement = Movement::factory()->create([
            'reason' => 'Monthly restock',
            'notes' => 'Requested by kitchen',
        ]);

        $this->assertEquals('Monthly restock', $movement->reason);
        $this->assertEquals('Requested by kitchen', $movement->notes);
    }

    /*
     * Business logic: item relationship
     */

    public function test_movement_tracks_which_item_was_moved()
    {
        $item = Item::factory()->create(['name' => 'Rice 50kg']);
        $movement = Movement::factory()->create(['item_id' => $item->id]);

        $this->assertInstanceOf(Item::class, $movement->item);
        $this->assertEquals('Rice 50kg', $movement->item->name);
    }
}
