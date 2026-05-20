<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::with('roomType');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }
        if ($request->filled('floor')) {
            $query->where('floor', $request->floor);
        }

        $rooms = $query->orderBy('floor')->orderBy('room_number')->paginate(20);
        $roomTypes = RoomType::where('is_active', true)->orderBy('name')->get();
        $floors = Room::select('floor')->distinct()->orderBy('floor')->pluck('floor');

        $stats = [
            'total'       => Room::count(),
            'available'   => Room::where('status', 'available')->count(),
            'occupied'    => Room::where('status', 'occupied')->count(),
            'maintenance' => Room::where('status', 'maintenance')->count(),
        ];

        return view('rooms.index', compact('rooms', 'roomTypes', 'floors', 'stats'));
    }

    public function create()
    {
        $roomTypes = RoomType::where('is_active', true)->orderBy('name')->get();
        $nextNumber = $this->nextRoomNumber();
        return view('rooms.create', compact('roomTypes', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number'  => 'required|string|max:10|unique:rooms,room_number',
            'room_type_id' => 'required|exists:room_types,id',
            'floor'        => 'required|integer|min:1|max:50',
            'status'       => 'required|in:available,occupied,maintenance,cleaning',
            'notes'        => 'nullable|string',
        ]);

        Room::create($validated);

        return redirect()->route('rooms.index')
            ->with('success', "Room {$validated['room_number']} created successfully.");
    }

    public function show(Room $room)
    {
        $room->load('roomType');
        return view('rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        $roomTypes = RoomType::where('is_active', true)->orderBy('name')->get();
        return view('rooms.edit', compact('room', 'roomTypes'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_number'  => 'required|string|max:10|unique:rooms,room_number,' . $room->id,
            'room_type_id' => 'required|exists:room_types,id',
            'floor'        => 'required|integer|min:1|max:50',
            'status'       => 'required|in:available,occupied,maintenance,cleaning',
            'notes'        => 'nullable|string',
        ]);

        $room->update($validated);

        return redirect()->route('rooms.index')
            ->with('success', "Room {$validated['room_number']} updated successfully.");
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return redirect()->route('rooms.index')
            ->with('success', "Room {$room->room_number} deleted.");
    }

    private function nextRoomNumber(): string
    {
        $last = Room::orderByDesc('room_number')->first();
        if (!$last) return '101';
        $num = intval($last->room_number) + 1;
        return (string) $num;
    }
}
