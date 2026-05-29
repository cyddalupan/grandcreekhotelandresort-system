@extends('layouts.app')

@section('page-header', 'Bookings')
@section('title', 'Bookings - ' . config('app.name'))

@section('content')
<div class="space-y-4 md:space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-blue-900">Bookings</h1>
            <p class="text-sm md:text-base text-gray-600">Manage guest reservations and check-in/out</p>
        </div>
        <a href="{{ route('bookings.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Booking
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total Bookings</p>
            <p class="text-2xl font-bold text-blue-900">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Pending</p>
            <p class="text-2xl font-bold text-yellow-700">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Checked In</p>
            <p class="text-2xl font-bold text-green-700">{{ $stats['checked_in'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Arriving Today</p>
            <p class="text-2xl font-bold text-indigo-700">{{ $stats['today_in'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Departing Today</p>
            <p class="text-2xl font-bold text-orange-700">{{ $stats['today_out'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                    <option value="checked_out" {{ request('status') == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Guest</label>
                <input type="text" name="guest" value="{{ request('guest') }}" placeholder="Search name..."
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg">Filter</button>
            <a href="{{ route('bookings.index') }}" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900">Clear</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                <tr>
                    <th class="text-left px-4 py-3">Guest</th>
                    <th class="text-left px-4 py-3">Room</th>
                    <th class="text-left px-4 py-3">Check In</th>
                    <th class="text-left px-4 py-3">Check Out</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-right px-4 py-3">Total</th>
                    <th class="text-right px-4 py-3">Paid</th>
                    <th class="text-center px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($bookings as $booking)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('bookings.show', $booking) }}" class="font-medium text-blue-900 hover:underline">
                            {{ $booking->guest_name }}
                        </a>
                        @if($booking->guest_phone)
                        <p class="text-xs text-gray-500">{{ $booking->guest_phone }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="font-medium">{{ $booking->room->room_number }}</span>
                        <p class="text-xs text-gray-500">{{ $booking->room->roomType->name }}</p>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $booking->check_in->format('M d, Y') }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $booking->check_out->format('M d, Y') }}</td>
                    <td class="px-4 py-3">
                        @php
                            $badge = match($booking->status) {
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'confirmed' => 'bg-blue-100 text-blue-700',
                                'checked_in' => 'bg-green-100 text-green-700',
                                'checked_out' => 'bg-gray-100 text-gray-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                            };
                        @endphp
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $badge }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                    </td>
                    <td class="px-4 py-3 text-right font-medium">₱{{ number_format($booking->total_amount, 2) }}</td>
                    <td class="px-4 py-3 text-right {{ $booking->paid_amount >= $booking->total_amount ? 'text-green-700' : 'text-yellow-700' }}">
                        ₱{{ number_format($booking->paid_amount, 2) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('bookings.show', $booking) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @if(!in_array($booking->status, ['checked_out', 'cancelled']))
                            <a href="{{ route('bookings.edit', $booking) }}" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @endif
                            @if(in_array($booking->status, ['pending', 'cancelled']))
                            <form action="{{ route('bookings.destroy', $booking) }}" method="POST" onsubmit="return confirm('Delete booking for {{ addslashes($booking->guest_name) }}? This cannot be undone.')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-500">No bookings found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($bookings->hasPages())
    <div class="mt-4">{{ $bookings->links() }}</div>
    @endif
</div>
@endsection
