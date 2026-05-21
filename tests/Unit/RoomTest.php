<?php

namespace Tests\Unit;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_belongs_to_room_type()
    {
        $roomType = RoomType::factory()->create();
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        $this->assertInstanceOf(RoomType::class, $room->roomType);
        $this->assertEquals($roomType->id, $room->roomType->id);
    }

    public function test_available_scope_excludes_occupied_rooms()
    {
        $roomType = RoomType::factory()->create();

        $availableRoom = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        $occupiedRoom = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'occupied',
        ]);

        $maintenanceRoom = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'maintenance',
        ]);

        $availableRooms = Room::available()->get();

        $this->assertTrue($availableRooms->contains('id', $availableRoom->id));
        $this->assertFalse($availableRooms->contains('id', $occupiedRoom->id));
        $this->assertFalse($availableRooms->contains('id', $maintenanceRoom->id));
    }

    public function test_by_floor_scope_filters_correctly()
    {
        Room::factory()->create(['room_number' => '101', 'floor' => 1]);
        Room::factory()->create(['room_number' => '102', 'floor' => 1]);
        Room::factory()->create(['room_number' => '201', 'floor' => 2]);

        $floor1Rooms = Room::byFloor(1)->get();

        $this->assertCount(2, $floor1Rooms);
        $floor1Rooms->each(fn($r) => $this->assertEquals(1, $r->floor));

        $floor2Rooms = Room::byFloor(2)->get();
        $this->assertCount(1, $floor2Rooms);
    }

    public function test_room_has_unique_room_number()
    {
        Room::factory()->create(['room_number' => '101']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Room::factory()->create(['room_number' => '101']);
    }

    public function test_room_type_capacity_is_positive()
    {
        $roomType = RoomType::factory()->create(['capacity' => 2]);

        $this->assertGreaterThan(0, $roomType->capacity);
    }

    public function test_room_type_has_price_per_night()
    {
        $roomType = RoomType::factory()->create(['price_per_night' => 1500]);

        $this->assertGreaterThan(0, $roomType->price_per_night);
    }

    public function test_room_type_has_available_rooms_relationship()
    {
        $roomType = RoomType::factory()->create();
        Room::factory()->count(3)->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);
        Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'occupied',
        ]);

        $this->assertCount(3, $roomType->availableRooms);
    }

    public function test_room_type_get_amenities_list()
    {
        $roomType = RoomType::factory()->create([
            'amenities' => ['WiFi', 'TV', 'Air Conditioning'],
        ]);

        $list = $roomType->getAmenitiesListAttribute();

        $this->assertIsString($list);
        $this->assertStringContainsString('WiFi', $list);
        $this->assertStringContainsString('TV', $list);
    }
}
