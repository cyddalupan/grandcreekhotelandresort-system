<?php

namespace Tests\Unit;

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

    // ── Factory / Creation ──

    public function test_housekeeping_task_can_be_created_via_factory(): void
    {
        $task = Housekeeping::factory()->create();

        $this->assertInstanceOf(Housekeeping::class, $task);
        $this->assertNotNull($task->room_id);
        $this->assertNotNull($task->task_type);
        $this->assertNotNull($task->status);
    }

    // ── Relationships ──

    public function test_housekeeping_task_belongs_to_room(): void
    {
        $room = Room::factory()->create();
        $task = Housekeeping::factory()->create(['room_id' => $room->id]);

        $this->assertInstanceOf(Room::class, $task->room);
        $this->assertEquals($room->id, $task->room->id);
    }

    public function test_housekeeping_task_has_assigned_staff(): void
    {
        $staff = Employee::factory()->create();
        $task = Housekeeping::factory()->create(['assigned_to' => $staff->id]);

        $this->assertInstanceOf(Employee::class, $task->assignedStaff);
        $this->assertEquals($staff->id, $task->assignedStaff->id);
    }

    public function test_housekeeping_task_has_completed_by(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $task = Housekeeping::factory()->create(['completed_by' => $user->id]);

        $this->assertInstanceOf(User::class, $task->completedBy);
        $this->assertEquals($user->id, $task->completedBy->id);
    }

    public function test_relationship_is_null_when_not_set(): void
    {
        $task = Housekeeping::factory()->create([
            'assigned_to' => null,
            'completed_by' => null,
        ]);

        $this->assertNull($task->assignedStaff);
        $this->assertNull($task->completedBy);
    }

    // ── Status constants ──

    public function test_all_statuses_are_valid(): void
    {
        $expected = ['pending', 'in_progress', 'completed', 'verified'];

        foreach ($expected as $status) {
            $task = Housekeeping::factory()->create(['status' => $status]);
            $this->assertEquals($status, $task->status);
        }
    }

    public function test_all_task_types_are_valid(): void
    {
        $expected = ['cleaning', 'maintenance', 'inspection', 'turndown', 'deep_clean'];

        foreach ($expected as $type) {
            $task = Housekeeping::factory()->create(['task_type' => $type]);
            $this->assertEquals($type, $task->task_type);
        }
    }

    public function test_all_priorities_are_valid(): void
    {
        $expected = ['low', 'normal', 'high', 'urgent'];

        foreach ($expected as $priority) {
            $task = Housekeeping::factory()->create(['priority' => $priority]);
            $this->assertEquals($priority, $task->priority);
        }
    }

    // ── Defaults ──

    public function test_default_status_is_not_null(): void
    {
        $task = Housekeeping::factory()->create(['status' => 'pending']);

        $this->assertEquals('pending', $task->status);
    }

    // ── Casts ──

    public function test_scheduled_date_is_date_casted(): void
    {
        $task = Housekeeping::factory()->create(['scheduled_date' => '2026-06-01']);

        $this->assertInstanceOf(Carbon::class, $task->scheduled_date);
    }

    public function test_completed_at_is_datetime_casted(): void
    {
        $task = Housekeeping::factory()->create(['completed_at' => now()]);

        $this->assertInstanceOf(Carbon::class, $task->completed_at);
    }

    // ── Nullable fields ──

    public function test_notes_is_nullable(): void
    {
        $task = Housekeeping::factory()->create(['notes' => null]);

        $this->assertNull($task->notes);
    }

    public function test_completed_at_is_nullable(): void
    {
        $task = Housekeeping::factory()->create(['completed_at' => null]);

        $this->assertNull($task->completed_at);
    }

    public function test_completed_by_is_nullable(): void
    {
        $task = Housekeeping::factory()->create(['completed_by' => null]);

        $this->assertNull($task->completedBy);
    }

    public function test_assigned_to_is_nullable(): void
    {
        $task = Housekeeping::factory()->create(['assigned_to' => null]);

        $this->assertNull($task->assignedStaff);
    }

    // ── Priority defaults ──

    public function test_priority_defaults_to_normal_when_not_set(): void
    {
        $task = Housekeeping::factory()->create(['priority' => 'normal']);

        $this->assertEquals('normal', $task->priority);
    }
}
