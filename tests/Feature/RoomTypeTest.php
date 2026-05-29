<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTypeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email_verified_at' => now()]);
    }

    // ── Auth gates ──

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('room-types.index'))->assertRedirect(route('login'));
        $this->get(route('room-types.create'))->assertRedirect(route('login'));
        $this->post(route('room-types.store'), [])->assertRedirect(route('login'));
        $this->get(route('room-types.show', 1))->assertRedirect(route('login'));
        $this->get(route('room-types.edit', 1))->assertRedirect(route('login'));
        $this->put(route('room-types.update', 1), [])->assertRedirect(route('login'));
        $this->delete(route('room-types.destroy', 1))->assertRedirect(route('login'));
    }

    // ── Index ──

    public function test_index_lists_room_types(): void
    {
        RoomType::factory()->create(['name' => 'Deluxe']);
        RoomType::factory()->create(['name' => 'Suite']);

        $response = $this->actingAs($this->user)->get(route('room-types.index'));
        $response->assertStatus(200);
        $response->assertSee('Deluxe');
        $response->assertSee('Suite');
    }

    public function test_index_shows_empty_state(): void
    {
        $response = $this->actingAs($this->user)->get(route('room-types.index'));
        $response->assertStatus(200);
    }

    public function test_index_shows_rooms_count(): void
    {
        $roomType = RoomType::factory()->create(['name' => 'Deluxe']);
        Room::factory()->count(3)->create(['room_type_id' => $roomType->id]);

        $response = $this->actingAs($this->user)->get(route('room-types.index'));
        $response->assertStatus(200);
    }

    // ── Create ──

    public function test_create_form_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('room-types.create'));
        $response->assertStatus(200);
    }

    // ── Store ──

    public function test_room_type_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(route('room-types.store'), [
            'name'            => 'Presidential Suite',
            'description'     => 'Luxury accommodation',
            'capacity'        => 4,
            'price_per_night' => 8999.99,
            'amenities'       => ['WiFi', 'Jacuzzi', 'Ocean View'],
            'icon'            => 'diamond',
            'is_active'       => true,
        ]);

        $response->assertRedirect(route('room-types.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('room_types', [
            'name' => 'Presidential Suite',
        ]);
    }

    public function test_store_creates_with_minimal_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('room-types.store'), [
            'name'            => 'Economy',
            'capacity'        => 2,
            'price_per_night' => 1500,
        ]);

        $response->assertRedirect(route('room-types.index'));

        $this->assertDatabaseHas('room_types', [
            'name'            => 'Economy',
            'capacity'        => 2,
            'price_per_night' => 1500.00,
            'is_active'       => false, // default when not provided
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('room-types.store'), [
            'name'            => '',
            'capacity'        => '',
            'price_per_night' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'capacity', 'price_per_night']);
    }

    public function test_store_validates_capacity_range(): void
    {
        $response = $this->actingAs($this->user)->post(route('room-types.store'), [
            'name'            => 'Test',
            'capacity'        => 0,
            'price_per_night' => 1000,
        ]);

        $response->assertSessionHasErrors('capacity');
    }

    public function test_store_rejects_negative_price(): void
    {
        $response = $this->actingAs($this->user)->post(route('room-types.store'), [
            'name'            => 'Test',
            'capacity'        => 2,
            'price_per_night' => -100,
        ]);

        $response->assertSessionHasErrors('price_per_night');
    }

    public function test_store_creates_with_empty_amenities(): void
    {
        $response = $this->actingAs($this->user)->post(route('room-types.store'), [
            'name'            => 'Basic',
            'capacity'        => 2,
            'price_per_night' => 1000,
            'amenities'       => [],
        ]);

        $response->assertRedirect(route('room-types.index'));

        $this->assertDatabaseHas('room_types', [
            'name' => 'Basic',
        ]);

        $roomType = RoomType::where('name', 'Basic')->first();
        $this->assertIsArray($roomType->amenities);
        $this->assertEmpty($roomType->amenities);
    }

    // ── Show ──

    public function test_show_displays_room_type(): void
    {
        $roomType = RoomType::factory()->create([
            'name'        => 'Penthouse',
            'description' => 'Top floor luxury',
        ]);
        Room::factory()->count(2)->create(['room_type_id' => $roomType->id]);

        $response = $this->actingAs($this->user)->get(route('room-types.show', $roomType));
        $response->assertStatus(200);
        $response->assertSee('Penthouse');
        $response->assertSee('Top floor luxury');
    }

    // ── Edit / Update ──

    public function test_edit_form_loads(): void
    {
        $roomType = RoomType::factory()->create(['name' => 'Deluxe']);

        $response = $this->actingAs($this->user)->get(route('room-types.edit', $roomType));
        $response->assertStatus(200);
        $response->assertSee('Deluxe');
    }

    public function test_room_type_can_be_updated(): void
    {
        $roomType = RoomType::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->user)->put(route('room-types.update', $roomType), [
            'name'            => 'Updated Name',
            'description'     => 'Now with ocean view',
            'capacity'        => 3,
            'price_per_night' => 5500,
            'is_active'       => true,
        ]);

        $response->assertRedirect(route('room-types.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('room_types', [
            'id'   => $roomType->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_update_can_disable_room_type(): void
    {
        $roomType = RoomType::factory()->create(['is_active' => true]);

        $this->actingAs($this->user)->put(route('room-types.update', $roomType), [
            'name'            => $roomType->name,
            'capacity'        => $roomType->capacity,
            'price_per_night' => $roomType->price_per_night,
            'is_active'       => false,
        ]);

        $this->assertDatabaseHas('room_types', [
            'id'        => $roomType->id,
            'is_active' => 0,
        ]);
    }

    // ── Destroy ──

    public function test_room_type_with_rooms_cannot_be_deleted(): void
    {
        $roomType = RoomType::factory()->create();
        Room::factory()->create(['room_type_id' => $roomType->id]);

        $response = $this->actingAs($this->user)->delete(route('room-types.destroy', $roomType));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('room_types', ['id' => $roomType->id]);
    }

    public function test_room_type_without_rooms_can_be_deleted(): void
    {
        $roomType = RoomType::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('room-types.destroy', $roomType));
        $response->assertRedirect(route('room-types.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('room_types', ['id' => $roomType->id]);
    }
}
