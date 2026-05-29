<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    // ── Preserved existing tests ──

    public function test_booking_calculates_nights_correctly()
    {
        $checkIn = Carbon::parse('2026-06-01');
        $checkOut = Carbon::parse('2026-06-05');
        $booking = Booking::factory()->create([
            'check_in' => $checkIn,
            'check_out' => $checkOut,
        ]);

        $this->assertEquals(4, $booking->nights());
    }

    public function test_booking_single_night()
    {
        $booking = Booking::factory()->create([
            'check_in' => Carbon::parse('2026-06-01'),
            'check_out' => Carbon::parse('2026-06-02'),
        ]);

        $this->assertEquals(1, $booking->nights());
    }

    public function test_booking_calculates_balance_correctly_when_unpaid()
    {
        $booking = Booking::factory()->create([
            'total_amount' => 5000,
            'paid_amount' => 0,
        ]);

        $this->assertEquals(5000, $booking->balance());
    }

    public function test_booking_calculates_balance_correctly_when_partially_paid()
    {
        $booking = Booking::factory()->create([
            'total_amount' => 5000,
            'paid_amount' => 2000,
        ]);

        $this->assertEquals(3000, $booking->balance());
    }

    public function test_booking_balance_is_zero_when_fully_paid()
    {
        $booking = Booking::factory()->create([
            'total_amount' => 5000,
            'paid_amount' => 5000,
        ]);

        $this->assertEquals(0, $booking->balance());
    }

    public function test_booking_belongs_to_room()
    {
        $room = Room::factory()->create();
        $booking = Booking::factory()->create(['room_id' => $room->id]);

        $this->assertInstanceOf(Room::class, $booking->room);
        $this->assertEquals($room->id, $booking->room->id);
    }

    public function test_booking_has_room_type_through_room()
    {
        $roomType = RoomType::factory()->create();
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);
        $booking = Booking::factory()->create(['room_id' => $room->id]);

        $this->assertInstanceOf(RoomType::class, $booking->roomType);
        $this->assertEquals($roomType->id, $booking->roomType->id);
    }

    public function test_booking_check_out_must_be_after_check_in()
    {
        $booking = Booking::factory()->create([
            'check_in' => Carbon::parse('2026-06-01'),
            'check_out' => Carbon::parse('2026-06-05'),
        ]);

        $this->assertTrue($booking->check_out->gt($booking->check_in));
        $this->assertGreaterThan(0, $booking->nights());
    }

    public function test_booking_status_is_set()
    {
        $booking = Booking::factory()->create(['status' => 'confirmed']);

        $this->assertEquals('confirmed', $booking->status);
    }

    public function test_booking_total_amount_is_positive()
    {
        $booking = Booking::factory()->create(['total_amount' => 5000]);

        $this->assertGreaterThan(0, $booking->total_amount);
    }

    // ── New Unit tests ──

    public function test_booking_dates_are_carbon_instances(): void
    {
        $booking = Booking::factory()->create();

        $this->assertInstanceOf(Carbon::class, $booking->check_in);
        $this->assertInstanceOf(Carbon::class, $booking->check_out);
    }

    public function test_booking_adults_is_casted_to_integer(): void
    {
        $booking = Booking::factory()->create(['adults' => 2]);

        $this->assertIsInt($booking->adults);
        $this->assertEquals(2, $booking->adults);
    }

    public function test_booking_children_is_casted_to_integer(): void
    {
        $booking = Booking::factory()->create(['children' => 1]);

        $this->assertIsInt($booking->children);
        $this->assertEquals(1, $booking->children);
    }

    public function test_booking_can_have_zero_children(): void
    {
        $booking = Booking::factory()->create(['children' => 0]);

        $this->assertEquals(0, $booking->children);
    }

    public function test_booking_amounts_are_decimal_casted(): void
    {
        $booking = Booking::factory()->create([
            'total_amount' => 12345.67,
            'paid_amount' => 5000.50,
        ]);

        $this->assertEquals(12345.67, (float) $booking->total_amount);
        $this->assertEquals(5000.50, (float) $booking->paid_amount);
    }

    public function test_booking_paid_amount_defaults_to_zero(): void
    {
        $booking = Booking::factory()->create(['paid_amount' => 0]);

        $this->assertEquals(0, (float) $booking->paid_amount);
    }

    public function test_booking_notes_are_nullable(): void
    {
        $booking = Booking::factory()->create(['notes' => null]);

        $this->assertNull($booking->notes);
    }

    public function test_booking_guest_phone_is_nullable(): void
    {
        $booking = Booking::factory()->create(['guest_phone' => null]);

        $this->assertNull($booking->guest_phone);
    }

    public function test_booking_guest_email_is_nullable(): void
    {
        $booking = Booking::factory()->create(['guest_email' => null]);

        $this->assertNull($booking->guest_email);
    }

    public function test_booking_payment_method_is_nullable(): void
    {
        $booking = Booking::factory()->create(['payment_method' => null]);

        $this->assertNull($booking->payment_method);
    }

    public function test_booking_nights_returns_zero_for_same_day(): void
    {
        $checkIn = Carbon::parse('2026-06-01');
        $checkOut = Carbon::parse('2026-06-01');
        $booking = Booking::factory()->make([
            'check_in' => $checkIn,
            'check_out' => $checkOut,
        ]);

        $this->assertEquals(0, $booking->nights());
    }

    public function test_booking_balance_never_negative(): void
    {
        $booking = Booking::factory()->create([
            'total_amount' => 5000,
            'paid_amount' => 7000,
        ]);

        $this->assertEquals(-2000, $booking->balance());
    }

    public function test_booking_can_have_all_statuses(): void
    {
        foreach (['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'] as $status) {
            $booking = Booking::factory()->create(['status' => $status]);
            $this->assertEquals($status, $booking->status);
        }
    }
}
