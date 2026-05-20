@extends('layouts.app')

@section('page-header', 'Add Room Type')
@section('title', 'Add Room Type - ' . config('app.name'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('room-types.index') }}" class="text-sm text-gray-500 hover:text-blue-900 transition-colors">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Room Types
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Create Room Type</h2>

        <form action="{{ route('room-types.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Room Type Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="e.g., Deluxe King">
                </div>
                <div>
                    <label for="price_per_night" class="block text-sm font-medium text-gray-700 mb-1">Price per Night (₱) *</label>
                    <input type="number" step="0.01" id="price_per_night" name="price_per_night" value="{{ old('price_per_night') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="2500.00">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Describe the room type features...">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1">Capacity *</label>
                    <input type="number" id="capacity" name="capacity" min="1" max="20" value="{{ old('capacity', 2) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="icon" class="block text-sm font-medium text-gray-700 mb-1">Icon (emoji)</label>
                    <input type="text" id="icon" name="icon" value="{{ old('icon') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="🏠 🛏️ 🏖️">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Active</label>
                    <label class="inline-flex items-center gap-2 mt-2">
                        <input type="checkbox" name="is_active" value="1" checked
                               class="rounded border-gray-300 text-blue-900">
                        <span class="text-sm text-gray-700">Available for booking</span>
                    </label>
                </div>
            </div>

            <div x-data="{ amenities: [] }">
                <label class="block text-sm font-medium text-gray-700 mb-2">Amenities</label>
                <div class="flex flex-wrap gap-2 mb-3">
                    <template x-for="(a, i) in amenities" :key="i">
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 text-sm rounded-full">
                            <span x-text="a"></span>
                            <button type="button" @click="amenities.splice(i, 1)" class="text-blue-400 hover:text-red-500">&times;</button>
                        </span>
                    </template>
                </div>
                <div class="flex gap-2">
                    <input type="text" x-model="newAmenity" @keydown.enter.prevent="if(newAmenity.trim()) { amenities.push(newAmenity.trim()); newAmenity = ''; }"
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500" placeholder="Type amenity and press Enter">
                    <button type="button" @click="if(newAmenity.trim()) { amenities.push(newAmenity.trim()); newAmenity = ''; }"
                            class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-sm rounded-lg">Add</button>
                </div>
                <template x-for="(a, i) in amenities" :key="i">
                    <input type="hidden" name="amenities[]" :value="a">
                </template>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('room-types.index') }}" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">Create Room Type</button>
            </div>
        </form>
    </div>
</div>
@endsection
