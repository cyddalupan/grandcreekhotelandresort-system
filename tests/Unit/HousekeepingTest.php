<?php

namespace Tests\Unit;

use App\Models\Housekeeping;
use App\Models\Room;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HousekeepingTest extends TestCase
{
    use RefreshDatabase;

    public function test_housekeeping_task_belongs_to_room()
    {
        $room = Room::factory()->create();
        $task = Housekeeping::factory()->create(['room_id' => $room->id]);

        $this->assertInstanceOf(Room::class, $task->room);
        $this->assertEquals($room->id, $task->room->id);
    }

    public function test_housekeeping_task_has_assigned_staff()
    {
        $staff = Employee::factory()->create();
        $task = Housekeeping::factory()->create(['assigned_to' => $staff->id]);

        $this->assertInstanceOf(Employee::class, $task->assignedStaff);
        $this->assertEquals($staff->id, $task->assignedStaff->id);
    }

    public function test_housekeeping_task_has_completed_by()
    {
        $user = User::factory()->create();
        $task = Housekeeping::factory()->create(['completed_by' => $user->id]);

        $this->assertInstanceOf(User::class, $task->completedBy);
        $this->assertEquals($user->id, $task->completedBy->id);
    }

    public function test_housekeeping_task_has_status()
    {
        $task = Housekeeping::factory()->create(['status' => 'in_progress']);

        $this->assertEquals('in_progress', $task->status);
    }

    public function test_housekeeping_task_has_task_type()
    {
        $task = Housekeeping::factory()->create(['task_type' => 'deep_clean']);

        $this->assertEquals('deep_clean', $task->task_type);
    }
}
