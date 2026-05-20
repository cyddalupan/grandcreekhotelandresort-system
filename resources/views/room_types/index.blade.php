@extends('layouts.app')

@section('page-header', 'Room Types')
@section('title', 'Room Types - ' . config('app.name'))

@section('content')
<div class="space-y-4 md:space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-blue-900">Room Types</h1>
            <p class="text-sm md:text-base text-gray-600">Manage room categories, pricing, and amenities</p>
        </div>
        <a href="{{ route('room-types.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Room Type
        </a>
    </div>

    {{-- Room Type Cards --}}
    @forelse($roomTypes as $rt)
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 md:p-6">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center text-xl flex-shrink-0">
                        {{ $rt->icon ?? '🏠' }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-lg font-bold text-gray-800">{{ $rt->name }}</h3>
                            @if(!$rt->is_active)
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600">Inactive</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mt-1">{{ $rt->description ?? 'No description' }}</p>
                        <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                            <span>🛏️ Up to {{ $rt->capacity }} guests</span>
                            <span>🚪 {{ $rt->rooms_count }} room(s)</span>
                            <span>💰 <strong class="text-green-700">₱{{ number_format($rt->price_per_night, 2) }}</strong>/night</span>
                        </div>
                        @if($rt->amenities_list)
                        <div class="flex flex-wrap gap-1.5 mt-3">
                            @foreach($rt->amenities_list as $amenity)
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-xs rounded-full">{{ $amenity }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                <div class="flex gap-1 flex-shrink-0">
                    <a href="{{ route('room-types.show', $rt) }}" class="p-1.5 text-gray-400 hover:text-blue-900" title="View">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                    <a href="{{ route('room-types.edit', $rt) }}" class="p-1.5 text-gray-400 hover:text-blue-900" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    @if($rt->rooms_count === 0)
                    <form action="{{ route('room-types.destroy', $rt) }}" method="POST" class="inline" onsubmit="return confirm('Delete this room type?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600" title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <p class="text-gray-500">No room types yet. Create your first one!</p>
    </div>
    @endforelse
</div>
@endsection
