@extends('layouts.app')

@section('page-header', 'Rooms')
@section('title', 'Rooms - ' . config('app.name'))

@section('content')
<div class="space-y-4 md:space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-blue-900">Rooms</h1>
            <p class="text-sm md:text-base text-gray-600">Manage hotel rooms and their status</p>
        </div>
        <a href="{{ route('rooms.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Room
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total Rooms</p>
            <p class="text-2xl font-bold text-blue-900">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Available</p>
            <p class="text-2xl font-bold text-green-700">{{ $stats['available'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Occupied</p>
            <p class="text-2xl font-bold text-red-700">{{ $stats['occupied'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Maintenance</p>
            <p class="text-2xl font-bold text-yellow-700">{{ $stats['maintenance'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="occupied" {{ request('status') == 'occupied' ? 'selected' : '' }}>Occupied</option>
                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="cleaning" {{ request('status') == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Room Type</label>
                <select name="room_type_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All Types</option>
                    @foreach($roomTypes as $rt)
                        <option value="{{ $rt->id }}" {{ request('room_type_id') == $rt->id ? 'selected' : '' }}>{{ $rt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Floor</label>
                <select name="floor" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All Floors</option>
                    @foreach($floors as $f)
                        <option value="{{ $f }}" {{ request('floor') == $f ? 'selected' : '' }}>Floor {{ $f }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg">Filter</button>
            <a href="{{ route('rooms.index') }}" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900">Clear</a>
        </form>
    </div>

    {{-- Grid View --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 md:gap-4">
        @forelse($rooms as $room)
        <a href="{{ route('rooms.show', $room) }}" class="bg-white rounded-xl shadow-sm p-4 text-center hover:shadow-md transition-shadow border-t-4
            @switch($room->status)
                @case('available') border-green-500 @break
                @case('occupied') border-red-500 @break
                @case('maintenance') border-yellow-500 @break
                @case('cleaning') border-blue-500 @break
            @endswitch
        ">
            <p class="text-xl md:text-2xl font-bold text-gray-800">{{ $room->room_number }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $room->roomType->name }}</p>
            <p class="text-xs text-gray-400">Floor {{ $room->floor }}</p>
            <span class="inline-block mt-2 px-2 py-0.5 text-xs rounded-full
                @switch($room->status)
                    @case('available') bg-green-100 text-green-700 @break
                    @case('occupied') bg-red-100 text-red-700 @break
                    @case('maintenance') bg-yellow-100 text-yellow-700 @break
                    @case('cleaning') bg-blue-100 text-blue-700 @break
                @endswitch
            ">{{ ucfirst($room->status) }}</span>
        </a>
        @empty
        <div class="col-span-full bg-white rounded-xl shadow-sm p-12 text-center">
            <p class="text-gray-500">No rooms found.</p>
        </div>
        @endforelse
    </div>

    @if($rooms->hasPages())
    <div class="mt-4">{{ $rooms->links() }}</div>
    @endif
</div>
@endsection
