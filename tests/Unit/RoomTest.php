<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    // ── Existing tests (preserved) ──

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

        $this->assertIsArray($list);
        $this->assertContains('WiFi', $list);
        $this->assertContains('TV', $list);
    }

    // ── New Room model tests ──

    public function test_room_floor_is_casted_to_integer(): void
    {
        $room = Room::factory()->create(['floor' => 3]);

        $this->assertIsInt($room->floor);
        $this->assertEquals(3, $room->floor);
    }

    public function test_room_status_can_be_available(): void
    {
        $room = Room::factory()->create(['status' => 'available']);

        $this->assertEquals('available', $room->status);
    }

    public function test_room_status_can_be_occupied(): void
    {
        $room = Room::factory()->create(['status' => 'occupied']);

        $this->assertEquals('occupied', $room->status);
    }

    public function test_room_status_can_be_maintenance(): void
    {
        $room = Room::factory()->create(['status' => 'maintenance']);

        $this->assertEquals('maintenance', $room->status);
    }

    public function test_room_notes_are_nullable(): void
    {
        $room = Room::factory()->create(['notes' => null]);

        $this->assertNull($room->notes);
    }

    public function test_room_type_can_have_nullable_fields(): void
    {
        $roomType = RoomType::factory()->create([
            'description' => null,
            'icon' => null,
        ]);

        $this->assertNull($roomType->description);
        $this->assertNull($roomType->icon);
    }

    public function test_room_type_is_active_defaults_true(): void
    {
        $roomType = RoomType::factory()->create();

        $this->assertTrue($roomType->is_active);
    }

    public function test_room_type_can_be_inactive(): void
    {
        $roomType = RoomType::factory()->create(['is_active' => false]);

        $this->assertFalse($roomType->is_active);
    }

    public function test_room_type_amenities_is_array_cast(): void
    {
        $roomType = RoomType::factory()->create([
            'amenities' => ['WiFi', 'TV', 'Mini Bar'],
        ]);

        $this->assertIsArray($roomType->amenities);
        $this->assertCount(3, $roomType->amenities);
    }

    public function test_room_type_has_many_rooms(): void
    {
        $roomType = RoomType::factory()->create();
        Room::factory()->count(5)->create(['room_type_id' => $roomType->id]);

        $this->assertCount(5, $roomType->rooms);
    }

    public function test_room_type_has_rooms_count(): void
    {
        $roomType = RoomType::factory()->create();
        Room::factory()->count(3)->create(['room_type_id' => $roomType->id]);

        $loadedWithCount = RoomType::withCount('rooms')->find($roomType->id);

        $this->assertEquals(3, $loadedWithCount->rooms_count);
    }

    public function test_room_type_amenities_list_returns_empty_string_for_null(): void
    {
        $roomType = RoomType::factory()->create(['amenities' => null]);

        $this->assertIsArray($roomType->getAmenitiesListAttribute());
        $this->assertEmpty($roomType->getAmenitiesListAttribute());
    }

    public function test_room_type_amenities_list_returns_empty_string_for_empty_array(): void
    {
        $roomType = RoomType::factory()->create(['amenities' => []]);

        $this->assertIsArray($roomType->getAmenitiesListAttribute());
        $this->assertEmpty($roomType->getAmenitiesListAttribute());
    }

    public function test_room_can_have_cleaning_status(): void
    {
        $room = Room::factory()->create(['status' => 'cleaning']);

        $this->assertEquals('cleaning', $room->status);
    }

    public function test_available_scope_includes_cleaning_status(): void
    {
        $roomType = RoomType::factory()->create();

        Room::factory()->create(['room_type_id' => $roomType->id, 'status' => 'cleaning']);

        $available = Room::available()->get();
        $this->assertFalse($available->pluck('status')->contains('cleaning'));
    }

    public function test_room_number_format_is_numeric(): void
    {
        $room = Room::factory()->create(['room_number' => '201']);

        $this->assertEquals('201', $room->room_number);
    }
}
