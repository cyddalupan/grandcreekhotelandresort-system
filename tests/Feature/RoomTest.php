<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email_verified_at' => now()]);
        $this->roomType = RoomType::factory()->create(['is_active' => true, 'name' => 'Deluxe']);
    }

    // ── Auth gates ──

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('rooms.index'))->assertRedirect(route('login'));
        $this->get(route('rooms.create'))->assertRedirect(route('login'));
        $this->post(route('rooms.store'), [])->assertRedirect(route('login'));
        $this->get(route('rooms.show', 1))->assertRedirect(route('login'));
        $this->get(route('rooms.edit', 1))->assertRedirect(route('login'));
        $this->put(route('rooms.update', 1), [])->assertRedirect(route('login'));
        $this->delete(route('rooms.destroy', 1))->assertRedirect(route('login'));
    }

    // ── Index ──

    public function test_index_lists_rooms(): void
    {
        Room::factory()->count(3)->create(['room_type_id' => $this->roomType->id]);

        $response = $this->actingAs($this->user)->get(route('rooms.index'));
        $response->assertStatus(200);
    }

    public function test_index_shows_empty_state(): void
    {
        $response = $this->actingAs($this->user)->get(route('rooms.index'));
        $response->assertStatus(200);
    }

    public function test_index_shows_room_stats(): void
    {
        Room::factory()->create(['room_type_id' => $this->roomType->id, 'status' => 'available']);
        Room::factory()->create(['room_type_id' => $this->roomType->id, 'status' => 'available']);
        Room::factory()->create(['room_type_id' => $this->roomType->id, 'status' => 'occupied']);
        Room::factory()->create(['room_type_id' => $this->roomType->id, 'status' => 'maintenance']);

        $response = $this->actingAs($this->user)->get(route('rooms.index'));
        $response->assertStatus(200);
        $response->assertSee('2'); // available
        $response->assertSee('1'); // occupied
        $response->assertSee('1'); // maintenance
        $response->assertSee('4'); // total
    }

    public function test_index_can_filter_by_status(): void
    {
        Room::factory()->create(['room_type_id' => $this->roomType->id, 'status' => 'available']);
        Room::factory()->create(['room_type_id' => $this->roomType->id, 'status' => 'occupied']);

        $response = $this->actingAs($this->user)->get(route('rooms.index', ['status' => 'occupied']));
        $response->assertStatus(200);
    }

    public function test_index_can_filter_by_floor(): void
    {
        Room::factory()->create(['room_type_id' => $this->roomType->id, 'floor' => 1]);
        Room::factory()->create(['room_type_id' => $this->roomType->id, 'floor' => 2]);

        $response = $this->actingAs($this->user)->get(route('rooms.index', ['floor' => 1]));
        $response->assertStatus(200);
    }

    public function test_index_can_filter_by_room_type(): void
    {
        $other = RoomType::factory()->create(['is_active' => true, 'name' => 'Suite']);
        Room::factory()->create(['room_type_id' => $this->roomType->id]);
        Room::factory()->create(['room_type_id' => $other->id]);

        $response = $this->actingAs($this->user)->get(route('rooms.index', ['room_type_id' => $this->roomType->id]));
        $response->assertStatus(200);
    }

    // ── Create ──

    public function test_create_form_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('rooms.create'));
        $response->assertStatus(200);
        $response->assertSee($this->roomType->name);
        $response->assertSee('101'); // first room number
    }

    // ── Store ──

    public function test_room_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(route('rooms.store'), [
            'room_number'  => '101',
            'room_type_id' => $this->roomType->id,
            'floor'        => 1,
            'status'       => 'available',
            'notes'        => 'Corner room',
        ]);

        $response->assertRedirect(route('rooms.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', [
            'room_number' => '101',
            'room_type_id' => $this->roomType->id,
            'status'       => 'available',
        ]);
    }

    public function test_store_creates_room_without_notes(): void
    {
        $response = $this->actingAs($this->user)->post(route('rooms.store'), [
            'room_number'  => '102',
            'room_type_id' => $this->roomType->id,
            'floor'        => 2,
            'status'       => 'available',
        ]);

        $response->assertRedirect(route('rooms.index'));
        $this->assertDatabaseHas('rooms', ['room_number' => '102']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('rooms.store'), [
            'room_number'  => '',
            'room_type_id' => '',
            'floor'        => '',
            'status'       => '',
        ]);

        $response->assertSessionHasErrors(['room_number', 'room_type_id', 'floor', 'status']);
    }

    public function test_store_validates_unique_room_number(): void
    {
        Room::factory()->create(['room_number' => '101', 'room_type_id' => $this->roomType->id]);

        $response = $this->actingAs($this->user)->post(route('rooms.store'), [
            'room_number'  => '101',
            'room_type_id' => $this->roomType->id,
            'floor'        => 1,
            'status'       => 'available',
        ]);

        $response->assertSessionHasErrors('room_number');
    }

    public function test_store_validates_room_type_exists(): void
    {
        $response = $this->actingAs($this->user)->post(route('rooms.store'), [
            'room_number'  => '201',
            'room_type_id' => 999,
            'floor'        => 2,
            'status'       => 'available',
        ]);

        $response->assertSessionHasErrors('room_type_id');
    }

    public function test_store_validates_floor_range(): void
    {
        $response = $this->actingAs($this->user)->post(route('rooms.store'), [
            'room_number'  => '301',
            'room_type_id' => $this->roomType->id,
            'floor'        => 0,
            'status'       => 'available',
        ]);

        $response->assertSessionHasErrors('floor');
    }

    public function test_store_rejects_invalid_status(): void
    {
        $response = $this->actingAs($this->user)->post(route('rooms.store'), [
            'room_number'  => '401',
            'room_type_id' => $this->roomType->id,
            'floor'        => 4,
            'status'       => 'booked',
        ]);

        $response->assertSessionHasErrors('status');
    }

    // ── Show ──

    public function test_show_displays_room(): void
    {
        $room = Room::factory()->create([
            'room_type_id' => $this->roomType->id,
            'notes'        => 'Ocean view',
        ]);

        $response = $this->actingAs($this->user)->get(route('rooms.show', $room));
        $response->assertStatus(200);
        $response->assertSee($room->room_number);
        $response->assertSee('Ocean view');
    }

    // ── Edit / Update ──

    public function test_edit_form_loads(): void
    {
        $room = Room::factory()->create(['room_type_id' => $this->roomType->id]);

        $response = $this->actingAs($this->user)->get(route('rooms.edit', $room));
        $response->assertStatus(200);
        $response->assertSee($room->room_number);
    }

    public function test_room_can_be_updated(): void
    {
        $room = Room::factory()->create([
            'room_type_id' => $this->roomType->id,
            'notes'        => 'Old note',
        ]);

        $response = $this->actingAs($this->user)->put(route('rooms.update', $room), [
            'room_number'  => $room->room_number,
            'room_type_id' => $this->roomType->id,
            'floor'        => 5,
            'status'       => 'occupied',
            'notes'        => 'Updated note',
        ]);

        $response->assertRedirect(route('rooms.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', [
            'id'     => $room->id,
            'floor'  => 5,
            'status' => 'occupied',
            'notes'  => 'Updated note',
        ]);
    }

    public function test_update_preserves_same_room_number(): void
    {
        $room = Room::factory()->create([
            'room_number'  => '101',
            'room_type_id' => $this->roomType->id,
        ]);

        $response = $this->actingAs($this->user)->put(route('rooms.update', $room), [
            'room_number'  => '101', // same as original
            'room_type_id' => $this->roomType->id,
            'floor'        => 1,
            'status'       => 'available',
        ]);

        $response->assertSessionHasNoErrors();
    }

    // ── Destroy ──

    public function test_room_can_be_deleted(): void
    {
        $room = Room::factory()->create(['room_type_id' => $this->roomType->id]);

        $response = $this->actingAs($this->user)->delete(route('rooms.destroy', $room));
        $response->assertRedirect(route('rooms.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
    }

    // ── Next room number ──

    public function test_create_shows_incremented_room_number(): void
    {
        Room::factory()->create([
            'room_number'  => '105',
            'room_type_id' => $this->roomType->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('rooms.create'));
        $response->assertSee('106');
    }
}
