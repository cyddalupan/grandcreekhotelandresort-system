<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with('room.roomType');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('check_in', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('check_out', '<=', $request->date_to);
        }
        if ($request->filled('guest')) {
            $query->where('guest_name', 'like', '%' . $request->guest . '%');
        }

        $bookings = $query->latest()->paginate(15);

        // Stats
        $stats = [
            'total'     => Booking::count(),
            'checked_in' => Booking::where('status', 'checked_in')->count(),
            'pending'   => Booking::where('status', 'pending')->count(),
            'today_in'  => Booking::whereDate('check_in', today())->count(),
            'today_out' => Booking::whereDate('check_out', today())->count(),
        ];

        return view('bookings.index', compact('bookings', 'stats'));
    }

    public function create()
    {
        $roomTypes = RoomType::with('rooms')->where('is_active', true)->get();
        $nextBookingNumber = Booking::max('id') + 1;
        return view('bookings.create', compact('roomTypes', 'nextBookingNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_name'  => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'room_id'     => 'required|exists:rooms,id',
            'check_in'    => 'required|date|after_or_equal:today',
            'check_out'   => 'required|date|after:check_in',
            'adults'      => 'required|integer|min:1|max:20',
            'children'    => 'required|integer|min:0|max:10',
            'total_amount'=> 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'notes'       => 'nullable|string|max:1000',
        ]);

        // Check room availability
        $conflict = Booking::where('room_id', $validated['room_id'])
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('check_in', [$validated['check_in'], $validated['check_out']])
                  ->orWhereBetween('check_out', [$validated['check_in'], $validated['check_out']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('check_in', '<=', $validated['check_in'])
                         ->where('check_out', '>=', $validated['check_out']);
                  });
            })->exists();

        if ($conflict) {
            return back()->withInput()->withErrors(['room_id' => 'This room is already booked for the selected dates.']);
        }

        Booking::create($validated);

        return redirect()->route('bookings.index')->with('success', 'Booking created successfully.');
    }

    public function show(Booking $booking)
    {
        $booking->load('room.roomType');
        return view('bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        if (in_array($booking->status, ['checked_out', 'cancelled'])) {
            return redirect()->route('bookings.index')->with('error', 'Cannot edit a completed or cancelled booking.');
        }

        $roomTypes = RoomType::with('rooms')->where('is_active', true)->get();
        return view('bookings.edit', compact('booking', 'roomTypes'));
    }

    public function update(Request $request, Booking $booking)
    {
        if (in_array($booking->status, ['checked_out', 'cancelled'])) {
            return redirect()->route('bookings.index')->with('error', 'Cannot edit a completed or cancelled booking.');
        }

        $validated = $request->validate([
            'guest_name'  => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'room_id'     => 'required|exists:rooms,id',
            'check_in'    => 'required|date',
            'check_out'   => 'required|date|after:check_in',
            'adults'      => 'required|integer|min:1|max:20',
            'children'    => 'required|integer|min:0|max:10',
            'total_amount'=> 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'notes'       => 'nullable|string|max:1000',
        ]);

        // Check availability excluding this booking
        $conflict = Booking::where('room_id', $validated['room_id'])
            ->where('id', '!=', $booking->id)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('check_in', [$validated['check_in'], $validated['check_out']])
                  ->orWhereBetween('check_out', [$validated['check_in'], $validated['check_out']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('check_in', '<=', $validated['check_in'])
                         ->where('check_out', '>=', $validated['check_out']);
                  });
            })->exists();

        if ($conflict) {
            return back()->withInput()->withErrors(['room_id' => 'This room is already booked for the selected dates.']);
        }

        $booking->update($validated);

        return redirect()->route('bookings.index')->with('success', 'Booking updated successfully.');
    }

    public function destroy(Booking $booking)
    {
        if (!in_array($booking->status, ['pending', 'cancelled'])) {
            return back()->with('error', 'Only pending or cancelled bookings can be deleted.');
        }

        $booking->delete();
        return redirect()->route('bookings.index')->with('success', 'Booking deleted.');
    }

    // ─── Status Actions ───

    public function confirm(Booking $booking)
    {
        if ($booking->status !== 'pending') {
            return back()->with('error', 'Only pending bookings can be confirmed.');
        }
        $booking->update(['status' => 'confirmed']);
        return back()->with('success', 'Booking confirmed.');
    }

    public function checkIn(Booking $booking)
    {
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'Only pending/confirmed bookings can be checked in.');
        }
        $booking->update(['status' => 'checked_in']);
        // Update room status to occupied
        $booking->room->update(['status' => 'occupied']);
        return back()->with('success', 'Guest checked in successfully.');
    }

    public function checkOut(Booking $booking)
    {
        if ($booking->status !== 'checked_in') {
            return back()->with('error', 'Only checked-in bookings can be checked out.');
        }
        $booking->update(['status' => 'checked_out']);
        // Update room back to available
        $booking->room->update(['status' => 'cleaning']);
        return back()->with('success', 'Guest checked out successfully. Room marked for cleaning.');
    }

    public function cancel(Booking $booking)
    {
        if (in_array($booking->status, ['checked_out', 'cancelled'])) {
            return back()->with('error', 'Booking is already completed or cancelled.');
        }

        $wasCheckedIn = $booking->status === 'checked_in';
        $booking->update(['status' => 'cancelled']);

        // If was checked in, free the room
        if ($wasCheckedIn) {
            $booking->room->update(['status' => 'available']);
        }

        return back()->with('success', 'Booking cancelled.');
    }

    // ─── AJAX: Available Rooms ───
    public function availableRooms(Request $request)
    {
        $checkIn = $request->input('check_in');
        $checkOut = $request->input('check_out');

        if (!$checkIn || !$checkOut) {
            return response()->json([]);
        }

        $bookedRoomIds = Booking::whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in', [$checkIn, $checkOut])
                  ->orWhereBetween('check_out', [$checkIn, $checkOut])
                  ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                      $q2->where('check_in', '<=', $checkIn)
                         ->where('check_out', '>=', $checkOut);
                  });
            })
            ->pluck('room_id');

        $roomTypeId = $request->input('room_type_id');
        $rooms = Room::with('roomType')
            ->where('status', 'available')
            ->whereNotIn('id', $bookedRoomIds)
            ->when($roomTypeId, fn($q) => $q->where('room_type_id', $roomTypeId))
            ->get()
            ->map(fn($r) => [
                'id'       => $r->id,
                'number'   => $r->room_number,
                'type'     => $r->roomType->name,
                'floor'    => $r->floor,
                'price'    => $r->roomType->price_per_night,
                'capacity' => $r->roomType->capacity,
            ]);

        return response()->json($rooms);
    }
}
