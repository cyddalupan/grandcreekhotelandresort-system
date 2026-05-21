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

    public function test_booking_belongs_to_room_type()
    {
        $roomType = RoomType::factory()->create();
        $booking = Booking::factory()->create(['room_type_id' => $roomType->id]);

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
}
