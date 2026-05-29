@extends('layouts.app')

@section('page-header', 'Edit Booking')
@section('title', 'Edit Booking - ' . config('app.name'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('bookings.index') }}" class="text-sm text-gray-500 hover:text-blue-900 transition-colors">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Bookings
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Edit Booking — {{ $booking->guest_name }}</h2>

        <form action="{{ route('bookings.update', $booking) }}" method="POST" class="space-y-5" x-data="editForm()">
            @csrf @method('PUT')

            <div class="border-b border-gray-200 pb-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Guest Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input type="text" name="guest_name" value="{{ old('guest_name', $booking->guest_name) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="guest_phone" value="{{ old('guest_phone', $booking->guest_phone) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="guest_email" value="{{ old('guest_email', $booking->guest_email) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="border-b border-gray-200 pb-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Stay Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Check In *</label>
                        <input type="date" name="check_in" value="{{ old('check_in', $booking->check_in->format('Y-m-d')) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               x-model="checkIn" @change.debounce="loadRooms()">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Check Out *</label>
                        <input type="date" name="check_out" value="{{ old('check_out', $booking->check_out->format('Y-m-d')) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               x-model="checkOut" @change.debounce="loadRooms()">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Type</label>
                        <select x-model="roomTypeFilter" @change="loadRooms()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="">All Room Types</option>
                            @foreach($roomTypes as $rt)
                            <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Adults *</label>
                            <input type="number" name="adults" min="1" max="20" value="{{ old('adults', $booking->adults) }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Children</label>
                            <input type="number" name="children" min="0" max="10" value="{{ old('children', $booking->children) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                {{-- Room Selection --}}
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Room *</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" x-show="rooms.length > 0">
                        <template x-for="room in rooms" :key="room.id">
                            <label class="relative block p-3 border-2 rounded-xl cursor-pointer transition-all"
                                   :class="selectedRoom == room.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="room_id" :value="room.id" x-model="selectedRoom"
                                       class="sr-only" @change="updatePrice(room.price)">
                                <p class="font-bold text-gray-800" x-text="room.number"></p>
                                <p class="text-xs text-gray-500" x-text="room.type"></p>
                                <p class="text-xs text-gray-400" x-text="'Floor ' + room.floor"></p>
                                <p class="text-sm font-bold text-green-700 mt-1" x-text="'₱' + numberFormat(room.price) + '/night'"></p>
                            </label>
                        </template>
                    </div>
                    <div x-show="checkIn && checkOut && rooms.length === 0 && loaded" class="p-4 bg-yellow-50 text-yellow-800 text-sm rounded-lg">
                        No available rooms for the selected dates.
                    </div>
                </div>
            </div>

            <div class="border-b border-gray-200 pb-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Payment</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount *</label>
                        <input type="number" step="0.01" min="0" name="total_amount"
                               value="{{ old('total_amount', $booking->total_amount) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-bold">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Paid Amount</label>
                        <input type="number" step="0.01" min="0" name="paid_amount"
                               value="{{ old('paid_amount', $booking->paid_amount) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="">Select...</option>
                            <option value="cash" {{ old('payment_method', $booking->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="gcash" {{ old('payment_method', $booking->payment_method) == 'gcash' ? 'selected' : '' }}>GCash</option>
                            <option value="maya" {{ old('payment_method', $booking->payment_method) == 'maya' ? 'selected' : '' }}>Maya</option>
                            <option value="card" {{ old('payment_method', $booking->payment_method) == 'card' ? 'selected' : '' }}>Credit/Debit Card</option>
                            <option value="bank" {{ old('payment_method', $booking->payment_method) == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                        </select>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('notes', $booking->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('bookings.index') }}" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">Update Booking</button>
            </div>
        </form>
    </div>

    @if(in_array($booking->status, ['pending', 'cancelled']))
    <div class="mt-6 bg-white rounded-xl shadow-sm p-6 md:p-8 border-2 border-red-200">
        <h2 class="text-lg font-bold text-red-700 mb-1">Danger Zone</h2>
        <p class="text-sm text-gray-600 mb-4">Only pending or cancelled bookings can be deleted. This action cannot be undone.</p>
        <form action="{{ route('bookings.destroy', $booking) }}" method="POST" onsubmit="return confirm('Delete booking for {{ addslashes($booking->guest_name) }}? This action is permanent.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                Delete Booking
            </button>
        </form>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function editForm() {
    return {
        checkIn: '{{ old('check_in', $booking->check_in->format('Y-m-d')) }}',
        checkOut: '{{ old('check_out', $booking->check_out->format('Y-m-d')) }}',
        roomTypeFilter: '',
        selectedRoom: '{{ old('room_id', $booking->room_id) }}',
        rooms: [],
        loaded: false,

        loadRooms() {
            if (!this.checkIn || !this.checkOut) return;
            this.loaded = false;
            const params = new URLSearchParams({
                check_in: this.checkIn,
                check_out: this.checkOut,
                room_type_id: this.roomTypeFilter
            });
            fetch('{{ route('bookings.available-rooms') }}?' + params)
                .then(r => r.json())
                .then(data => {
                    // Include current room so the guest can stay
                    if (!data.find(r => r.id == {{ $booking->room_id }})) {
                        data.push({
                            id: {{ $booking->room_id }},
                            number: '{{ $booking->room->room_number }}',
                            type: '{{ $booking->room->roomType->name }}',
                            floor: {{ $booking->room->floor }},
                            price: {{ $booking->room->roomType->price_per_night }},
                        });
                    }
                    this.rooms = data;
                    this.loaded = true;
                });
        },

        updatePrice(price) {},

        numberFormat(n) {
            return Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }
}
</script>
@endpush
