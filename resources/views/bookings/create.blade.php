@extends('layouts.app')

@section('page-header', 'New Booking')
@section('title', 'New Booking - ' . config('app.name'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('bookings.index') }}" class="text-sm text-gray-500 hover:text-blue-900 transition-colors">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Bookings
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">New Booking</h2>

        <form action="{{ route('bookings.store') }}" method="POST" class="space-y-5" x-data="bookingForm()">
            @csrf

            {{-- Guest Info --}}
            <div class="border-b border-gray-200 pb-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Guest Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label for="guest_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input type="text" id="guest_name" name="guest_name" value="{{ old('guest_name') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Juan Dela Cruz">
                    </div>
                    <div>
                        <label for="guest_phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" id="guest_phone" name="guest_phone" value="{{ old('guest_phone') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="09XX XXX XXXX">
                    </div>
                </div>
                <div class="mt-4">
                    <label for="guest_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="guest_email" name="guest_email" value="{{ old('guest_email') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="guest@email.com">
                </div>
            </div>

            {{-- Dates & Room --}}
            <div class="border-b border-gray-200 pb-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Stay Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="check_in" class="block text-sm font-medium text-gray-700 mb-1">Check In *</label>
                        <input type="date" id="check_in" name="check_in" value="{{ old('check_in', date('Y-m-d')) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               x-model="checkIn" @change.debounce="loadRooms()">
                    </div>
                    <div>
                        <label for="check_out" class="block text-sm font-medium text-gray-700 mb-1">Check Out *</label>
                        <input type="date" id="check_out" name="check_out" value="{{ old('check_out') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               x-model="checkOut" @change.debounce="loadRooms()">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="room_type_filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Type</label>
                        <select id="room_type_filter" x-model="roomTypeFilter" @change="loadRooms()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="">All Room Types</option>
                            @foreach($roomTypes as $rt)
                            <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-4">
                        <div>
                            <label for="adults" class="block text-sm font-medium text-gray-700 mb-1">Adults *</label>
                            <input type="number" id="adults" name="adults" min="1" max="20" value="{{ old('adults', 1) }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="children" class="block text-sm font-medium text-gray-700 mb-1">Children</label>
                            <input type="number" id="children" name="children" min="0" max="10" value="{{ old('children', 0) }}"
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
                                   :class="selectedRoom === room.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="room_id" :value="room.id" x-model="selectedRoom"
                                       class="sr-only" @change="updatePrice(room.price)">
                                <p class="font-bold text-gray-800" x-text="room.number"></p>
                                <p class="text-xs text-gray-500" x-text="room.type"></p>
                                <p class="text-xs text-gray-400" x-text="'Floor ' + room.floor + ' · ' + room.capacity + ' guests'"></p>
                                <p class="text-sm font-bold text-green-700 mt-1" x-text="'₱' + numberFormat(room.price) + '/night'"></p>
                            </label>
                        </template>
                    </div>
                    <div x-show="checkIn && checkOut && rooms.length === 0 && loaded" class="p-4 bg-yellow-50 text-yellow-800 text-sm rounded-lg">
                        No available rooms for the selected dates and type.
                    </div>
                    <div x-show="!checkIn || !checkOut" class="p-4 bg-gray-50 text-gray-500 text-sm rounded-lg">
                        Select check-in and check-out dates to see available rooms.
                    </div>
                </div>
            </div>

            {{-- Payment --}}
            <div class="border-b border-gray-200 pb-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Payment</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price per Night</label>
                        <p class="px-4 py-2 bg-gray-50 rounded-lg text-gray-700 text-sm" x-text="'₱' + numberFormat(pricePerNight)"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nights</label>
                        <p class="px-4 py-2 bg-gray-50 rounded-lg text-gray-700 text-sm" x-text="nights"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount *</label>
                        <input type="number" step="0.01" min="0" id="total_amount" name="total_amount"
                               x-model="totalAmount" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-bold">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="paid_amount" class="block text-sm font-medium text-gray-700 mb-1">Paid Amount</label>
                        <input type="number" step="0.01" min="0" id="paid_amount" name="paid_amount" value="{{ old('paid_amount', 0) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <select id="payment_method" name="payment_method"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="">Select...</option>
                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="gcash" {{ old('payment_method') == 'gcash' ? 'selected' : '' }}>GCash</option>
                            <option value="maya" {{ old('payment_method') == 'maya' ? 'selected' : '' }}>Maya</option>
                            <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Credit/Debit Card</option>
                            <option value="bank" {{ old('payment_method') == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes / Special Requests</label>
                <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('bookings.index') }}" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">Create Booking</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function bookingForm() {
    return {
        checkIn: '{{ old('check_in', date('Y-m-d')) }}',
        checkOut: '{{ old('check_out') }}',
        roomTypeFilter: '{{ old('room_type_id') }}',
        selectedRoom: '{{ old('room_id') }}',
        rooms: [],
        loaded: false,
        pricePerNight: 0,
        totalAmount: 0,

        get nights() {
            if (!this.checkIn || !this.checkOut) return 0;
            const start = new Date(this.checkIn);
            const end = new Date(this.checkOut);
            const diff = Math.max(0, Math.floor((end - start) / (1000 * 60 * 60 * 24)));
            return diff;
        },

        updatePrice(price) {
            this.pricePerNight = price;
            this.calcTotal();
        },

        calcTotal() {
            this.totalAmount = this.pricePerNight * this.nights;
        },

        numberFormat(n) {
            return Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

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
                    this.rooms = data;
                    this.loaded = true;
                    // Clear selection if room no longer available
                    if (!data.find(r => r.id == this.selectedRoom)) {
                        this.selectedRoom = '';
                        this.pricePerNight = 0;
                        this.calcTotal();
                    }
                });
        }
    }
}
</script>
@endpush
