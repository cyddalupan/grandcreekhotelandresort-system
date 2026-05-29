@extends('layouts.app')

@section('page-header', 'Edit Supplier')
@section('title', 'Edit Supplier - ' . config('app.name'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
        <h1 class="text-xl md:text-2xl font-bold text-blue-900 mb-6">Edit Supplier</h1>

        <form action="{{ route('suppliers.update', $supplier) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Supplier Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $supplier->name) }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                    <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    @error('contact_person') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $supplier->phone) }}"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $supplier->email) }}"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="total_purchases" class="block text-sm font-medium text-gray-700 mb-1">Total Purchases</label>
                <input type="number" step="0.01" name="total_purchases" id="total_purchases" value="{{ old('total_purchases', $supplier->total_purchases) }}" min="0"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                @error('total_purchases') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-4">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
                    Update Supplier
                </button>
                <a href="{{ route('suppliers.index') }}" class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <div class="mt-6 bg-white rounded-xl shadow-sm p-6 md:p-8 border-2 border-red-200">
        <h2 class="text-lg font-bold text-red-700 mb-1">Danger Zone</h2>
        <p class="text-sm text-gray-600 mb-4">Deleting this supplier will remove their record. Inventory items linked to this supplier will become unassigned.</p>
        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" onsubmit="return confirm('Delete supplier \"{{ addslashes($supplier->name) }}"? This action is permanent.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                Delete Supplier
            </button>
        </form>
    </div>
</div>
@endsection
