@extends('layouts.app')

@section('page-header', $roomType->name)
@section('title', $roomType->name . ' - Room Types - ' . config('app.name'))

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('room-types.index') }}" class="text-sm text-gray-500 hover:text-blue-900 transition-colors">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Room Types
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-6 md:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="text-4xl">{{ $roomType->icon ?? '🏠' }}</div>
                    <div class="text-white">
                        <h2 class="text-2xl font-bold">{{ $roomType->name }}</h2>
                        <p class="text-blue-100">{{ $roomType->rooms_count }} room(s) · Up to {{ $roomType->capacity }} guests</p>
                    </div>
                </div>
                <span class="px-3 py-1 text-xs font-medium rounded-full {{ $roomType->is_active ? 'bg-green-500 text-white' : 'bg-gray-500 text-white' }}">
                    {{ $roomType->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>

        {{-- Details --}}
        <div class="p-6 md:p-8 space-y-6">
            {{-- Price --}}
            <div class="bg-green-50 rounded-xl p-5 text-center border-2 border-green-200">
                <p class="text-sm text-green-700 uppercase tracking-wider font-semibold">Price per Night</p>
                <p class="text-3xl font-bold text-green-700">₱{{ number_format($roomType->price_per_night, 2) }}</p>
            </div>

            {{-- Description --}}
            @if($roomType->description)
            <div>
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Description</h4>
                <p class="text-sm text-gray-700">{{ $roomType->description }}</p>
            </div>
            @endif

            {{-- Amenities --}}
            @if($roomType->amenities_list)
            <div>
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Amenities</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($roomType->amenities_list as $amenity)
                    <span class="px-3 py-1 bg-blue-50 text-blue-700 text-sm rounded-full">{{ $amenity }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Rooms of this type --}}
            <div>
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Rooms</h4>
                @if($roomType->rooms->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach($roomType->rooms as $room)
                    <div class="border border-gray-200 rounded-lg p-3 text-center hover:shadow-sm transition-shadow">
                        <p class="text-sm font-bold text-gray-800">{{ $room->room_number }}</p>
                        <p class="text-xs text-gray-500">Floor {{ $room->floor }}</p>
                        <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded-full
                            @switch($room->status)
                                @case('available') bg-green-100 text-green-700 @break
                                @case('occupied') bg-red-100 text-red-700 @break
                                @case('maintenance') bg-yellow-100 text-yellow-700 @break
                                @case('cleaning') bg-blue-100 text-blue-700 @break
                            @endswitch">{{ ucfirst($room->status) }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-500">No rooms assigned to this type yet.</p>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="px-6 md:px-8 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-2">
            <a href="{{ route('room-types.edit', $roomType) }}" class="px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">Edit</a>
        </div>
    </div>
</div>
@endsection
