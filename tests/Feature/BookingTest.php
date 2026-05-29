<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private RoomType $roomType;
    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email_verified_at' => now()]);
        $this->roomType = RoomType::factory()->create([
            'is_active'       => true,
            'price_per_night' => 2500,
            'capacity'        => 2,
        ]);
        $this->room = Room::factory()->create([
            'room_type_id' => $this->roomType->id,
            'status'       => 'available',
        ]);
    }

    private function validBookingData(array $overrides = []): array
    {
        return array_merge([
            'guest_name'    => 'Juan Dela Cruz',
            'guest_email'   => 'juan@example.com',
            'guest_phone'   => '09171234567',
            'room_id'       => $this->room->id,
            'check_in'      => Carbon::tomorrow()->toDateString(),
            'check_out'     => Carbon::tomorrow()->addDays(3)->toDateString(),
            'adults'        => 2,
            'children'      => 1,
            'total_amount'  => 7500,
            'paid_amount'   => 0,
            'payment_method'=> 'cash',
            'notes'         => 'Requesting extra towel',
        ], $overrides);
    }

    // ── Auth gates ──

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('bookings.index'))->assertRedirect(route('login'));
        $this->get(route('bookings.create'))->assertRedirect(route('login'));
        $this->post(route('bookings.store'), [])->assertRedirect(route('login'));
        $this->get(route('bookings.show', 1))->assertRedirect(route('login'));
        $this->get(route('bookings.edit', 1))->assertRedirect(route('login'));
        $this->put(route('bookings.update', 1), [])->assertRedirect(route('login'));
        $this->delete(route('bookings.destroy', 1))->assertRedirect(route('login'));
        $this->post(route('bookings.confirm', 1))->assertRedirect(route('login'));
        $this->post(route('bookings.check-in', 1))->assertRedirect(route('login'));
        $this->post(route('bookings.check-out', 1))->assertRedirect(route('login'));
        $this->post(route('bookings.cancel', 1))->assertRedirect(route('login'));
    }

    // ── Index ──

    public function test_index_lists_bookings(): void
    {
        Booking::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('bookings.index'));
        $response->assertStatus(200);
    }

    public function test_index_shows_empty_state(): void
    {
        $response = $this->actingAs($this->user)->get(route('bookings.index'));
        $response->assertStatus(200);
    }

    public function test_index_shows_booking_stats(): void
    {
        $rooms = Room::factory()->count(5)->create(['room_type_id' => $this->roomType->id]);
        Booking::factory()->create(['status' => 'pending', 'room_id' => $rooms[0]->id]);
        Booking::factory()->create(['status' => 'confirmed', 'room_id' => $rooms[1]->id]);
        Booking::factory()->create(['status' => 'checked_in', 'room_id' => $rooms[2]->id]);
        Booking::factory()->create(['status' => 'checked_out', 'room_id' => $rooms[3]->id]);
        Booking::factory()->create(['status' => 'checked_out', 'room_id' => $rooms[4]->id]);

        $response = $this->actingAs($this->user)->get(route('bookings.index'));
        $response->assertStatus(200);
    }

    public function test_index_can_filter_by_status(): void
    {
        Booking::factory()->create(['status' => 'pending']);
        Booking::factory()->create(['status' => 'confirmed']);
        Booking::factory()->create(['status' => 'cancelled']);

        $response = $this->actingAs($this->user)->get(route('bookings.index', ['status' => 'pending']));
        $response->assertStatus(200);
    }

    public function test_index_can_search_by_guest_name(): void
    {
        Booking::factory()->create(['guest_name' => 'Juan Dela Cruz']);
        Booking::factory()->create(['guest_name' => 'Maria Santos']);

        $response = $this->actingAs($this->user)->get(route('bookings.index', ['guest' => 'Juan']));
        $response->assertStatus(200);
    }

    public function test_index_can_filter_by_date_range(): void
    {
        Booking::factory()->create([
            'check_in'  => Carbon::parse('+1 week'),
            'check_out' => Carbon::parse('+1 week')->addDays(2),
        ]);
        Booking::factory()->create([
            'check_in'  => Carbon::parse('+2 weeks'),
            'check_out' => Carbon::parse('+2 weeks')->addDays(2),
        ]);

        $response = $this->actingAs($this->user)->get(route('bookings.index', [
            'date_from' => Carbon::parse('+1 week')->toDateString(),
            'date_to'   => Carbon::parse('+1 week')->addDays(2)->toDateString(),
        ]));
        $response->assertStatus(200);
    }

    // ── Create ──

    public function test_create_form_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('bookings.create'));
        $response->assertStatus(200);
    }

    // ── Store ──

    public function test_booking_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(route('bookings.store'), $this->validBookingData());

        $response->assertRedirect(route('bookings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'guest_name' => 'Juan Dela Cruz',
        ]);
    }

    public function test_store_creates_without_optional_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('bookings.store'), $this->validBookingData([
            'guest_email'   => null,
            'guest_phone'   => null,
            'payment_method'=> null,
            'notes'         => null,
        ]));

        $response->assertRedirect(route('bookings.index'));
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('bookings.store'), [
            'guest_name'  => '',
            'room_id'     => '',
            'check_in'    => '',
            'check_out'   => '',
            'adults'      => '',
            'children'    => '',
            'total_amount'=> '',
            'paid_amount' => '',
        ]);

        $response->assertSessionHasErrors(['guest_name', 'room_id', 'check_in', 'check_out', 'adults', 'children', 'total_amount', 'paid_amount']);
    }

    public function test_store_rejects_invalid_email(): void
    {
        $response = $this->actingAs($this->user)->post(route('bookings.store'), $this->validBookingData([
            'guest_email' => 'not-an-email',
        ]));

        $response->assertSessionHasErrors('guest_email');
    }

    public function test_store_rejects_check_out_before_check_in(): void
    {
        $response = $this->actingAs($this->user)->post(route('bookings.store'), $this->validBookingData([
            'check_in'  => Carbon::tomorrow()->addDays(5)->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(3)->toDateString(),
        ]));

        $response->assertSessionHasErrors('check_out');
    }

    public function test_store_rejects_adults_over_maximum(): void
    {
        $response = $this->actingAs($this->user)->post(route('bookings.store'), $this->validBookingData([
            'adults' => 21,
        ]));

        $response->assertSessionHasErrors('adults');
    }

    public function test_store_rejects_negative_total_amount(): void
    {
        $response = $this->actingAs($this->user)->post(route('bookings.store'), $this->validBookingData([
            'total_amount' => -100,
        ]));

        $response->assertSessionHasErrors('total_amount');
    }

    public function test_store_prevents_double_booking_same_room(): void
    {
        $this->actingAs($this->user)->post(route('bookings.store'), $this->validBookingData());

        $response2 = $this->actingAs($this->user)->post(route('bookings.store'), $this->validBookingData([
            'guest_name' => 'Another Guest',
        ]));

        $response2->assertSessionHasErrors('room_id');
        $this->assertDatabaseCount('bookings', 1);
    }

    // ── Show ──

    public function test_show_displays_booking(): void
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'notes'   => 'Early check-in requested',
        ]);

        $response = $this->actingAs($this->user)->get(route('bookings.show', $booking));
        $response->assertStatus(200);
        $response->assertSee($booking->guest_name);
        $response->assertSee('Early check-in requested');
    }

    // ── Edit ──

    public function test_edit_form_loads(): void
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'status'  => 'pending',
        ]);

        $response = $this->actingAs($this->user)->get(route('bookings.edit', $booking));
        $response->assertStatus(200);
        $response->assertSee($booking->guest_name);
    }

    public function test_edit_redirects_for_checked_out_booking(): void
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'status'  => 'checked_out',
        ]);

        $response = $this->actingAs($this->user)->get(route('bookings.edit', $booking));
        $response->assertRedirect(route('bookings.index'));
        $response->assertSessionHas('error');
    }

    public function test_edit_redirects_for_cancelled_booking(): void
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'status'  => 'cancelled',
        ]);

        $response = $this->actingAs($this->user)->get(route('bookings.edit', $booking));
        $response->assertRedirect(route('bookings.index'));
        $response->assertSessionHas('error');
    }

    // ── Update ──

    public function test_booking_can_be_updated(): void
    {
        $booking = Booking::factory()->create([
            'room_id'    => $this->room->id,
            'status'     => 'pending',
            'guest_name' => 'Old Name',
        ]);

        $response = $this->actingAs($this->user)->put(route('bookings.update', $booking), [
            'guest_name'    => 'New Name',
            'guest_email'   => 'new@example.com',
            'guest_phone'   => '09221111111',
            'room_id'       => $this->room->id,
            'check_in'      => Carbon::tomorrow()->addDays(10)->toDateString(),
            'check_out'     => Carbon::tomorrow()->addDays(13)->toDateString(),
            'adults'        => 3,
            'children'      => 2,
            'total_amount'  => 9000,
            'paid_amount'   => 2000,
            'payment_method'=> 'gcash',
            'notes'         => 'Updated notes',
        ]);

        $response->assertRedirect(route('bookings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'id'         => $booking->id,
            'guest_name' => 'New Name',
        ]);
    }

    public function test_update_rejects_for_checked_out_booking(): void
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'status'  => 'checked_out',
        ]);

        $response = $this->actingAs($this->user)->put(route('bookings.update', $booking), $this->validBookingData());
        $response->assertSessionHas('error');
    }

    // ── Destroy ──

    public function test_pending_booking_can_be_deleted(): void
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'status'  => 'pending',
        ]);

        $response = $this->actingAs($this->user)->delete(route('bookings.destroy', $booking));
        $response->assertRedirect(route('bookings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }

    public function test_checked_in_booking_cannot_be_deleted(): void
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'status'  => 'checked_in',
        ]);

        $response = $this->actingAs($this->user)->delete(route('bookings.destroy', $booking));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    // ── Status transitions: Confirm ──

    public function test_confirm_pending_booking(): void
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'status'  => 'pending',
        ]);

        $response = $this->actingAs($this->user)->post(route('bookings.confirm', $booking));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_confirm_rejects_non_pending_booking(): void
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'status'  => 'confirmed',
        ]);

        $response = $this->actingAs($this->user)->post(route('bookings.confirm', $booking));
        $response->assertSessionHas('error');
    }

    // ── Status transitions: Check In ──

    public function test_check_in_from_pending(): void
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'status'  => 'pending',
        ]);

        $response = $this->actingAs($this->user)->post(route('bookings.check-in', $booking));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => 'checked_in',
        ]);
        $this->assertDatabaseHas('rooms', [
            'id'     => $this->room->id,
            'status' => 'occupied',
        ]);
    }

    public function test_check_in_from_confirmed(): void
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'status'  => 'confirmed',
        ]);

        $this->actingAs($this->user)->post(route('bookings.check-in', $booking));

        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => 'checked_in',
        ]);
    }

    public function test_check_in_rejects_checked_in_status(): void
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'status'  => 'checked_in',
        ]);

        $response = $this->actingAs($this->user)->post(route('bookings.check-in', $booking));
        $response->assertSessionHas('error');
    }

    // ── Status transitions: Check Out ──

    public function test_check_out_releases_room_for_cleaning(): void
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'status'  => 'checked_in',
        ]);

        $this->room->update(['status' => 'occupied']);

        $response = $this->actingAs($this->user)->post(route('bookings.check-out', $booking));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => 'checked_out',
        ]);
        $this->assertDatabaseHas('rooms', [
            'id'     => $this->room->id,
            'status' => 'cleaning',
        ]);
    }

    public function test_check_out_rejects_non_checked_in(): void
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'status'  => 'pending',
        ]);

        $response = $this->actingAs($this->user)->post(route('bookings.check-out', $booking));
        $response->assertSessionHas('error');
    }

    // ── Status transitions: Cancel ──

    public function test_cancel_pending_booking(): void
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'status'  => 'pending',
        ]);

        $response = $this->actingAs($this->user)->post(route('bookings.cancel', $booking));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_cancel_frees_room_when_was_checked_in(): void
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'status'  => 'checked_in',
        ]);
        $this->room->update(['status' => 'occupied']);

        $this->actingAs($this->user)->post(route('bookings.cancel', $booking));

        $this->assertDatabaseHas('rooms', [
            'id'     => $this->room->id,
            'status' => 'available',
        ]);
    }

    public function test_cancel_rejects_completed_booking(): void
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'status'  => 'checked_out',
        ]);

        $response = $this->actingAs($this->user)->post(route('bookings.cancel', $booking));
        $response->assertSessionHas('error');
    }

    public function test_cancel_rejects_already_cancelled(): void
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'status'  => 'cancelled',
        ]);

        $response = $this->actingAs($this->user)->post(route('bookings.cancel', $booking));
        $response->assertSessionHas('error');
    }

    // ── Available Rooms AJAX ──

    public function test_available_rooms_returns_json(): void
    {
        $response = $this->actingAs($this->user)->get(route('bookings.available-rooms', [
            'check_in'  => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'number', 'type', 'floor', 'price', 'capacity'],
        ]);
    }

    public function test_available_rooms_excludes_booked_rooms(): void
    {
        $bookedRoom = Room::factory()->create([
            'room_type_id' => $this->roomType->id,
            'status'       => 'available',
        ]);
        Booking::factory()->create([
            'room_id'  => $bookedRoom->id,
            'status'   => 'confirmed',
            'check_in' => Carbon::tomorrow(),
            'check_out'=> Carbon::tomorrow()->addDays(2),
        ]);

        $response = $this->actingAs($this->user)->get(route('bookings.available-rooms', [
            'check_in'  => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
        ]));

        $rooms = $response->json();
        $this->assertCount(1, $rooms); // only the unbooked room
        $this->assertEquals($this->room->id, $rooms[0]['id']);
    }

    public function test_available_rooms_requires_dates(): void
    {
        $response = $this->actingAs($this->user)->get(route('bookings.available-rooms'));

        $this->assertEquals([], $response->json());
    }
}
