<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Item;
use App\Models\Movement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Department $department;
    private Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email_verified_at' => now()]);
        $this->department = Department::factory()->create(['active' => true]);
        $this->item = Item::factory()->create([
            'department_id' => $this->department->id,
            'current_stock' => 100,
            'min_stock' => 10,
        ]);
    }

    // ── Auth gates ──

    public function test_unauthenticated_users_cannot_view_movements()
    {
        $this->get(route('movements.index'))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_cannot_create()
    {
        $this->get(route('movements.create'))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_cannot_store()
    {
        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'IN',
            'quantity' => 5,
        ])->assertRedirect(route('login'));
    }

    // ── IN movement — auto-increments stock ──

    public function test_in_movement_increases_stock()
    {
        $this->actingAs($this->user);

        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'IN',
            'quantity' => 10,
            'to_department' => $this->department->id,
        ])->assertRedirect(route('movements.index'));

        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'current_stock' => 110,
        ]);
    }

    public function test_in_movement_creates_movement_record()
    {
        $this->actingAs($this->user);

        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'IN',
            'quantity' => 10,
            'to_department' => $this->department->id,
            'notes' => 'Restock order',
        ]);

        $this->assertDatabaseHas('movements', [
            'item_id' => $this->item->id,
            'type' => 'IN',
            'quantity' => 10,
            'notes' => 'Restock order',
        ]);
    }

    public function test_in_movement_increments_department_item_count()
    {
        $this->actingAs($this->user);

        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'IN',
            'quantity' => 5,
            'to_department' => $this->department->id,
        ]);

        $this->assertDatabaseHas('departments', [
            'id' => $this->department->id,
            'item_count' => $this->department->fresh()->item_count,
        ]);
    }

    // ── OUT movement — auto-decrements stock ──

    public function test_out_movement_decreases_stock()
    {
        $this->actingAs($this->user);

        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'OUT',
            'quantity' => 30,
            'from_department' => $this->department->id,
        ])->assertRedirect(route('movements.index'));

        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'current_stock' => 70,
        ]);
    }

    public function test_out_movement_rejects_insufficient_stock()
    {
        $this->actingAs($this->user);

        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'OUT',
            'quantity' => 999,
            'from_department' => $this->department->id,
        ])->assertSessionHasErrors('quantity');

        // Stock unchanged
        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'current_stock' => 100,
        ]);
    }

    public function test_out_movement_creates_movement_record()
    {
        $this->actingAs($this->user);

        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'OUT',
            'quantity' => 5,
            'from_department' => $this->department->id,
            'reason' => 'Room use',
        ]);

        $this->assertDatabaseHas('movements', [
            'item_id' => $this->item->id,
            'type' => 'OUT',
            'quantity' => 5,
            'reason' => 'Room use',
        ]);
    }

    public function test_out_movement_exact_stock_goes_to_zero()
    {
        $this->actingAs($this->user);

        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'OUT',
            'quantity' => 100,
            'from_department' => $this->department->id,
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'current_stock' => 0,
        ]);
    }

    // ── TRANSFER movement ──

    public function test_transfer_movement_decreases_source_stock()
    {
        $this->actingAs($this->user);

        $destDept = Department::factory()->create(['active' => true]);

        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'TRANSFER',
            'quantity' => 20,
            'from_department' => $this->department->id,
            'to_department' => $destDept->id,
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'current_stock' => 80,
        ]);
    }

    public function test_transfer_movement_rejects_insufficient_stock()
    {
        $this->actingAs($this->user);

        $destDept = Department::factory()->create(['active' => true]);

        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'TRANSFER',
            'quantity' => 999,
            'from_department' => $this->department->id,
            'to_department' => $destDept->id,
        ])->assertSessionHasErrors('quantity');

        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'current_stock' => 100,
        ]);
    }

    // ── Index ──

    public function test_index_displays_movements()
    {
        $this->actingAs($this->user);

        Movement::factory()->count(5)->create(['item_id' => $this->item->id]);

        $response = $this->get(route('movements.index'));

        $response->assertOk();
        $response->assertViewHas('movements');
        $this->assertCount(5, $response->viewData('movements'));
    }

    public function test_index_paginates_movements()
    {
        $this->actingAs($this->user);

        Movement::factory()->count(25)->create(['item_id' => $this->item->id]);

        $response = $this->get(route('movements.index'));

        $this->assertCount(20, $response->viewData('movements'));
    }

    public function test_index_filters_by_type()
    {
        $this->actingAs($this->user);

        Movement::factory()->create(['item_id' => $this->item->id, 'type' => 'IN']);
        Movement::factory()->create(['item_id' => $this->item->id, 'type' => 'OUT']);

        $response = $this->get(route('movements.index', ['type' => 'IN']));

        $types = $response->viewData('movements')->pluck('type')->toArray();
        $this->assertCount(1, $types);
        $this->assertEquals(['IN'], $types);
    }

    // ── Create form ──

    public function test_create_form_loads_with_items_and_departments()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('movements.create'));

        $response->assertOk();
        $response->assertViewHas('items');
        $response->assertViewHas('departments');
    }

    // ── Validation ──

    public function test_store_requires_item_id()
    {
        $this->actingAs($this->user);

        $this->post(route('movements.store'), [
            'type' => 'IN',
            'quantity' => 5,
        ])->assertSessionHasErrors('item_id');
    }

    public function test_store_requires_type()
    {
        $this->actingAs($this->user);

        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'quantity' => 5,
        ])->assertSessionHasErrors('type');
    }

    public function test_store_rejects_invalid_type()
    {
        $this->actingAs($this->user);

        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'STOLEN',
            'quantity' => 5,
        ])->assertSessionHasErrors('type');
    }

    public function test_store_requires_quantity()
    {
        $this->actingAs($this->user);

        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'IN',
        ])->assertSessionHasErrors('quantity');
    }

    public function test_store_rejects_zero_quantity()
    {
        $this->actingAs($this->user);

        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'IN',
            'quantity' => 0,
        ])->assertSessionHasErrors('quantity');
    }

    public function test_store_rejects_negative_quantity()
    {
        $this->actingAs($this->user);

        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'IN',
            'quantity' => -5,
        ])->assertSessionHasErrors('quantity');
    }

    // ── Edge cases ──

    public function test_successive_in_movements_accumulate()
    {
        $this->actingAs($this->user);

        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'IN',
            'quantity' => 10,
            'to_department' => $this->department->id,
        ]);

        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'IN',
            'quantity' => 20,
            'to_department' => $this->department->id,
        ]);

        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'IN',
            'quantity' => 30,
            'to_department' => $this->department->id,
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'current_stock' => 160,
        ]);
    }

    public function test_full_cycle_in_out_transfer_does_not_overshoot()
    {
        $this->actingAs($this->user);

        $destDept = Department::factory()->create(['active' => true]);

        // IN: +20
        // OUT: -30 (should fail, only 20 available)
        // TRANSFER: -10
        // OUT: -10

        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'IN', 'quantity' => 20,
            'to_department' => $this->department->id,
        ]);

        // Try OUT 30 with only 120 available — should pass
        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'OUT', 'quantity' => 30,
            'from_department' => $this->department->id,
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'current_stock' => 90,
        ]);

        // Try TRANSFER 999 — should fail
        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'TRANSFER', 'quantity' => 999,
            'from_department' => $this->department->id,
            'to_department' => $destDept->id,
        ])->assertSessionHasErrors('quantity');

        // Stock unchanged at 90
        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'current_stock' => 90,
        ]);

        // TRANSFER 10 should pass
        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'TRANSFER', 'quantity' => 10,
            'from_department' => $this->department->id,
            'to_department' => $destDept->id,
        ]);

        // OUT 80 should pass (90 - 10 = 80)
        $this->post(route('movements.store'), [
            'item_id' => $this->item->id,
            'type' => 'OUT', 'quantity' => 80,
            'from_department' => $this->department->id,
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'current_stock' => 0,
        ]);
    }
}
