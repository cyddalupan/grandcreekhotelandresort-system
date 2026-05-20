@extends('layouts.app')

@section('page-header', 'Booking #' . $booking->id)
@section('title', 'Booking #' . $booking->id . ' - ' . config('app.name'))

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('bookings.index') }}" class="text-sm text-gray-500 hover:text-blue-900 transition-colors">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Bookings
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-6 md:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-bold text-white">{{ $booking->guest_name }}</h2>
                        <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-white/30 text-white">
                            @switch($booking->status)
                                @case('pending') Pending @break
                                @case('confirmed') Confirmed @break
                                @case('checked_in') Checked In @break
                                @case('checked_out') Checked Out @break
                                @case('cancelled') Cancelled @break
                            @endswitch
                        </span>
                    </div>
                    <p class="text-white/80 mt-1">Room {{ $booking->room->room_number }} · {{ $booking->room->roomType->name }}</p>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-6 md:p-8 space-y-6">
            {{-- Stay Dates --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-green-50 rounded-xl p-4 text-center">
                    <p class="text-xs text-green-600 uppercase tracking-wider font-semibold">Check In</p>
                    <p class="text-lg font-bold text-green-800 mt-1">{{ $booking->check_in->format('M d, Y') }}</p>
                </div>
                <div class="bg-orange-50 rounded-xl p-4 text-center">
                    <p class="text-xs text-orange-600 uppercase tracking-wider font-semibold">Check Out</p>
                    <p class="text-lg font-bold text-orange-800 mt-1">{{ $booking->check_out->format('M d, Y') }}</p>
                </div>
            </div>

            {{-- Guest Info --}}
            <div>
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Guest Info</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500">Phone</p>
                        <p class="font-medium text-gray-800">{{ $booking->guest_phone ?? '—' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="font-medium text-gray-800 break-all">{{ $booking->guest_email ?? '—' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500">Guests</p>
                        <p class="font-medium text-gray-800">{{ $booking->adults }} adult(s){{ $booking->children ? ', ' . $booking->children . ' child(ren)' : '' }}</p>
                    </div>
                </div>
            </div>

            {{-- Room Details --}}
            <div>
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Room Details</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500">Room</p>
                        <p class="font-medium text-gray-800">{{ $booking->room->room_number }} (Floor {{ $booking->room->floor }})</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500">Type</p>
                        <p class="font-medium text-gray-800">{{ $booking->room->roomType->name }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500">Capacity</p>
                        <p class="font-medium text-gray-800">{{ $booking->room->roomType->capacity }} guests</p>
                    </div>
                </div>

                @if($booking->room->roomType->amenities_list)
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($booking->room->roomType->amenities_list as $amenity)
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-xs rounded-full">{{ $amenity }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Payment Breakdown --}}
            <div>
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Payment</h4>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500">Price/Night</p>
                        <p class="font-bold text-gray-800">₱{{ number_format($booking->room->roomType->price_per_night, 2) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500">Nights</p>
                        <p class="font-bold text-gray-800">{{ $booking->nights() }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500">Total</p>
                        <p class="font-bold text-gray-800">₱{{ number_format($booking->total_amount, 2) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500">Paid</p>
                        <p class="font-bold {{ $booking->paid_amount >= $booking->total_amount ? 'text-green-700' : 'text-yellow-700' }}">
                            ₱{{ number_format($booking->paid_amount, 2) }}
                        </p>
                    </div>
                </div>
                @if($booking->payment_method)
                <p class="text-sm text-gray-500 mt-2">Method: <span class="font-medium">{{ ucfirst($booking->payment_method) }}</span></p>
                @endif
                @if($booking->balance() > 0)
                <div class="mt-2 p-3 bg-yellow-50 text-yellow-800 text-sm rounded-lg">
                    Balance: ₱{{ number_format($booking->balance(), 2) }}
                </div>
                @endif
            </div>

            {{-- Notes --}}
            @if($booking->notes)
            <div>
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Notes</h4>
                <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3">{{ $booking->notes }}</p>
            </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="px-6 md:px-8 py-4 bg-gray-50 border-t border-gray-200 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                @if($booking->status === 'pending')
                <form action="{{ route('bookings.confirm', $booking) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm font-medium rounded-lg transition-colors">Confirm</button>
                </form>
                @endif
                @if(in_array($booking->status, ['pending', 'confirmed']))
                <form action="{{ route('bookings.check-in', $booking) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-green-100 hover:bg-green-200 text-green-700 text-sm font-medium rounded-lg transition-colors">Check In</button>
                </form>
                @endif
                @if($booking->status === 'checked_in')
                <form action="{{ route('bookings.check-out', $booking) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm font-medium rounded-lg transition-colors">Check Out</button>
                </form>
                @endif
                @if(!in_array($booking->status, ['checked_out', 'cancelled']))
                <form action="{{ route('bookings.cancel', $booking) }}" method="POST" class="inline" onsubmit="return confirm('Cancel this booking?')">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-medium rounded-lg transition-colors">Cancel</button>
                </form>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if(!in_array($booking->status, ['checked_out', 'cancelled']))
                <a href="{{ route('bookings.edit', $booking) }}" class="px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">Edit Booking</a>
                @endif
                <form action="{{ route('bookings.destroy', $booking) }}" method="POST" class="inline" onsubmit="return confirm('Delete this booking?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-sm text-red-600 hover:text-red-800">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
