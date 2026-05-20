@extends('layouts.app')

@section('page-header', 'Room ' . $room->room_number)
@section('title', 'Room ' . $room->room_number . ' - ' . config('app.name'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('rooms.index') }}" class="text-sm text-gray-500 hover:text-blue-900 transition-colors">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Rooms
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r px-6 py-6 md:px-8
            @switch($room->status)
                @case('available') from-green-600 to-green-500 @break
                @case('occupied') from-red-600 to-red-500 @break
                @case('maintenance') from-yellow-600 to-yellow-500 @break
                @case('cleaning') from-blue-600 to-blue-500 @break
            @endswitch
        ">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-3xl font-bold text-white">Room {{ $room->room_number }}</h2>
                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-white/30 text-white">{{ ucfirst($room->status) }}</span>
                    </div>
                    <p class="text-white/80 mt-1">{{ $room->roomType->name }} · Floor {{ $room->floor }}</p>
                </div>
            </div>
        </div>

        {{-- Details --}}
        <div class="p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Room Info</h4>
                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Room Number</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $room->room_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Floor</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $room->floor }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Status</dt>
                        <dd class="text-sm font-medium">
                            <span class="inline-block px-2 py-0.5 text-xs rounded-full
                                @switch($room->status)
                                    @case('available') bg-green-100 text-green-700 @break
                                    @case('occupied') bg-red-100 text-red-700 @break
                                    @case('maintenance') bg-yellow-100 text-yellow-700 @break
                                    @case('cleaning') bg-blue-100 text-blue-700 @break
                                @endswitch
                            ">{{ ucfirst($room->status) }}</dd>
                    </div>
                </dl>
            </div>
            <div>
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Room Type Details</h4>
                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Type</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $room->roomType->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Capacity</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $room->roomType->capacity }} guests</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Price/Night</dt>
                        <dd class="text-sm font-bold text-green-700">₱{{ number_format($room->roomType->price_per_night, 2) }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Amenities --}}
        @if($room->roomType->amenities_list)
        <div class="px-6 md:px-8 pb-6">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Amenities</h4>
            <div class="flex flex-wrap gap-2">
                @foreach($room->roomType->amenities_list as $amenity)
                <span class="px-3 py-1 bg-blue-50 text-blue-700 text-sm rounded-full">{{ $amenity }}</span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Notes --}}
        @if($room->notes)
        <div class="px-6 md:px-8 pb-6">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Notes</h4>
            <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3">{{ $room->notes }}</p>
        </div>
        @endif

        {{-- Actions --}}
        <div class="px-6 md:px-8 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
            <form action="{{ route('rooms.destroy', $room) }}" method="POST" class="inline" onsubmit="return confirm('Delete this room?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-sm text-red-600 hover:text-red-800">Delete Room</button>
            </form>
            <a href="{{ route('rooms.edit', $room) }}" class="px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">Edit Room</a>
        </div>
    </div>
</div>
@endsection
