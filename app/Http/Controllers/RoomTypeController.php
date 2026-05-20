<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    public function index()
    {
        $roomTypes = RoomType::withCount('rooms')->orderBy('name')->get();
        return view('room_types.index', compact('roomTypes'));
    }

    public function create()
    {
        return view('room_types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:100',
            'description'     => 'nullable|string',
            'capacity'        => 'required|integer|min:1|max:20',
            'price_per_night' => 'required|numeric|min:0',
            'amenities'       => 'nullable|array',
            'amenities.*'     => 'string|max:50',
            'icon'            => 'nullable|string|max:50',
            'is_active'       => 'boolean',
        ]);

        if (!isset($validated['amenities'])) {
            $validated['amenities'] = [];
        }
        $validated['is_active'] = $request->boolean('is_active');

        RoomType::create($validated);

        return redirect()->route('room-types.index')
            ->with('success', 'Room type created successfully.');
    }

    public function show(RoomType $roomType)
    {
        $roomType->loadCount('rooms');
        $roomType->load('rooms');
        return view('room_types.show', compact('roomType'));
    }

    public function edit(RoomType $roomType)
    {
        return view('room_types.edit', compact('roomType'));
    }

    public function update(Request $request, RoomType $roomType)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:100',
            'description'     => 'nullable|string',
            'capacity'        => 'required|integer|min:1|max:20',
            'price_per_night' => 'required|numeric|min:0',
            'amenities'       => 'nullable|array',
            'amenities.*'     => 'string|max:50',
            'icon'            => 'nullable|string|max:50',
            'is_active'       => 'boolean',
        ]);

        if (!isset($validated['amenities'])) {
            $validated['amenities'] = [];
        }
        $validated['is_active'] = $request->boolean('is_active');

        $roomType->update($validated);

        return redirect()->route('room-types.index')
            ->with('success', 'Room type updated successfully.');
    }

    public function destroy(RoomType $roomType)
    {
        if ($roomType->rooms()->count() > 0) {
            return back()->with('error', 'Cannot delete room type with existing rooms. Remove or reassign rooms first.');
        }
        $roomType->delete();
        return redirect()->route('room-types.index')
            ->with('success', 'Room type deleted.');
    }
}
