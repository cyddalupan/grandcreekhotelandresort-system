<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Housekeeping;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HousekeepingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['email_verified_at' => now()]);
    }

    // ── Auth Guard ──

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('housekeeping.index'))->assertRedirect(route('login'));
        $this->get(route('housekeeping.create'))->assertRedirect(route('login'));
        $this->post(route('housekeeping.store'))->assertRedirect(route('login'));
        $this->get(route('housekeeping.edit', 1))->assertRedirect(route('login'));
        $this->put(route('housekeeping.update', 1))->assertRedirect(route('login'));
        $this->delete(route('housekeeping.destroy', 1))->assertRedirect(route('login'));
    }

    // ── Index ──

    public function test_index_lists_tasks(): void
    {
        $tasks = Housekeeping::factory(3)->create();

        $response = $this->actingAs($this->user)->get(route('housekeeping.index'));

        $response->assertOk();
        $response->assertViewHas('tasks');
        foreach ($tasks as $task) {
            $response->assertSee($task->room->room_number, false);
        }
    }

    public function test_index_filters_by_status(): void
    {
        Housekeeping::factory()->create(['status' => 'pending']);
        Housekeeping::factory()->create(['status' => 'completed']);

        $response = $this->actingAs($this->user)->get(route('housekeeping.index', ['status' => 'pending']));

        $response->assertOk();
        $response->assertViewHas('tasks', function ($tasks) {
            return $tasks->every(fn($t) => $t->status === 'pending');
        });
    }

    // ── Create ──

    public function test_create_page_loads(): void
    {
        Room::factory(2)->create();
        Employee::factory(2)->create();

        $response = $this->actingAs($this->user)->get(route('housekeeping.create'));

        $response->assertOk();
        $response->assertViewHas('rooms');
        $response->assertViewHas('employees');
    }

    // ── Store ──

    public function test_store_creates_task(): void
    {
        $room = Room::factory()->create();
        $employee = Employee::factory()->create();

        $response = $this->actingAs($this->user)->post(route('housekeeping.store'), [
            'room_id'        => $room->id,
            'task_type'      => 'cleaning',
            'priority'       => 'normal',
            'assigned_to'    => $employee->id,
            'notes'          => 'Test notes',
            'scheduled_date' => '2026-06-01',
        ]);

        $response->assertRedirect(route('housekeeping.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('housekeeping', [
            'room_id'   => $room->id,
            'task_type' => 'cleaning',
            'priority'  => 'normal',
            'status'    => 'pending',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('housekeeping.store'), []);

        $response->assertSessionHasErrors(['room_id', 'task_type', 'priority', 'scheduled_date']);
    }

    public function test_store_validates_invalid_task_type(): void
    {
        $room = Room::factory()->create();

        $response = $this->actingAs($this->user)->post(route('housekeeping.store'), [
            'room_id'        => $room->id,
            'task_type'      => 'invalid_type',
            'priority'       => 'normal',
            'scheduled_date' => '2026-06-01',
        ]);

        $response->assertSessionHasErrors(['task_type']);
    }

    public function test_store_validates_invalid_priority(): void
    {
        $room = Room::factory()->create();

        $response = $this->actingAs($this->user)->post(route('housekeeping.store'), [
            'room_id'        => $room->id,
            'task_type'      => 'cleaning',
            'priority'       => 'invalid_priority',
            'scheduled_date' => '2026-06-01',
        ]);

        $response->assertSessionHasErrors(['priority']);
    }

    // ── Show ──

    public function test_show_displays_task(): void
    {
        $task = Housekeeping::factory()->create([
            'notes' => 'This is a test note for show page',
        ]);

        $response = $this->actingAs($this->user)->get(route('housekeeping.show', $task));

        $response->assertOk();
        $response->assertViewHas('housekeeping');
        $response->assertSee($task->room->room_number, false);
        $response->assertSee(ucwords(str_replace('_', ' ', $task->task_type)));
        $response->assertSee('This is a test note for show page');
    }

    public function test_show_displays_employee_name_via_full_name_accessor(): void
    {
        $employee = Employee::factory()->create([
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
        ]);
        $task = Housekeeping::factory()->create([
            'assigned_to' => $employee->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('housekeeping.show', $task));

        $response->assertOk();
        $response->assertSee('Juan Dela Cruz');
    }

    public function test_show_displays_unassigned_when_no_staff(): void
    {
        $task = Housekeeping::factory()->create([
            'assigned_to' => null,
        ]);

        $response = $this->actingAs($this->user)->get(route('housekeeping.show', $task));

        $response->assertOk();
        $response->assertSee('Unassigned');
    }

    // ── Edit ──

    public function test_edit_page_loads_for_pending_task(): void
    {
        $task = Housekeeping::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->user)->get(route('housekeeping.edit', $task));

        $response->assertOk();
        $response->assertViewHas('housekeeping');
        $response->assertViewHas('rooms');
        $response->assertViewHas('employees');
    }

    public function test_edit_redirects_for_completed_task(): void
    {
        $task = Housekeeping::factory()->create([
            'status'       => 'completed',
            'completed_at' => now(),
            'completed_by' => User::factory()->create(['email_verified_at' => now()])->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('housekeeping.edit', $task));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_edit_redirects_for_verified_task(): void
    {
        $task = Housekeeping::factory()->create(['status' => 'verified']);

        $response = $this->actingAs($this->user)->get(route('housekeeping.edit', $task));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // ── Update ──

    public function test_update_modifies_task(): void
    {
        $task = Housekeeping::factory()->create(['status' => 'pending', 'notes' => 'Old notes']);
        $newRoom = Room::factory()->create();

        $response = $this->actingAs($this->user)->put(route('housekeeping.update', $task), [
            'room_id'        => $newRoom->id,
            'task_type'      => 'deep_clean',
            'priority'       => 'high',
            'assigned_to'    => null,
            'notes'          => 'Updated notes',
            'scheduled_date' => '2026-06-15',
        ]);

        $response->assertRedirect(route('housekeeping.show', $task));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('housekeeping', [
            'id'       => $task->id,
            'room_id'  => $newRoom->id,
            'notes'    => 'Updated notes',
            'priority' => 'high',
        ]);
    }

    public function test_update_blocked_for_completed_task(): void
    {
        $task = Housekeeping::factory()->create([
            'status'       => 'completed',
            'completed_at' => now(),
            'completed_by' => User::factory()->create(['email_verified_at' => now()])->id,
        ]);

        $response = $this->actingAs($this->user)->put(route('housekeeping.update', $task), [
            'room_id'        => $task->room_id,
            'task_type'      => 'cleaning',
            'priority'       => 'normal',
            'scheduled_date' => '2026-06-01',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // ── Destroy ──

    public function test_destroy_deletes_pending_task(): void
    {
        $task = Housekeeping::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->user)->delete(route('housekeeping.destroy', $task));

        $response->assertRedirect(route('housekeeping.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('housekeeping', ['id' => $task->id]);
    }

    public function test_destroy_blocked_for_completed_task(): void
    {
        $task = Housekeeping::factory()->create([
            'status'       => 'completed',
            'completed_at' => now(),
            'completed_by' => User::factory()->create(['email_verified_at' => now()])->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('housekeeping.destroy', $task));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('housekeeping', ['id' => $task->id]);
    }

    // ── Workflow: Assign ──

    public function test_assign_staff_to_task(): void
    {
        $employee = Employee::factory()->create();
        $task = Housekeeping::factory()->create(['assigned_to' => null]);

        $response = $this->actingAs($this->user)->post(route('housekeeping.assign', $task), [
            'assigned_to' => $employee->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('housekeeping', [
            'id'          => $task->id,
            'assigned_to' => $employee->id,
        ]);
    }

    // ── Workflow: Start ──

    public function test_start_task_changes_status_to_in_progress(): void
    {
        $task = Housekeeping::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->user)->post(route('housekeeping.start', $task));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('housekeeping', [
            'id'     => $task->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_start_blocked_for_non_pending_tasks(): void
    {
        $task = Housekeeping::factory()->create(['status' => 'in_progress']);

        $response = $this->actingAs($this->user)->post(route('housekeeping.start', $task));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('housekeeping', [
            'id'     => $task->id,
            'status' => 'in_progress',
        ]);
    }

    // ── Workflow: Complete ──

    public function test_complete_task_sets_status_completed_at_and_completed_by(): void
    {
        $task = Housekeeping::factory()->create(['status' => 'in_progress']);

        $response = $this->actingAs($this->user)->post(route('housekeeping.complete', $task));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('housekeeping', [
            'id'           => $task->id,
            'status'       => 'completed',
            'completed_by' => $this->user->id,
        ]);
        $this->assertNotNull($task->fresh()->completed_at);
    }

    public function test_complete_blocked_for_non_in_progress_tasks(): void
    {
        $task = Housekeeping::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->user)->post(route('housekeeping.complete', $task));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('housekeeping', [
            'id'     => $task->id,
            'status' => 'pending',
        ]);
    }
}
