@extends('layouts.app')

@section('page-header', 'Record Movement')
@section('title', 'Record Movement - ' . config('app.name'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
        <h1 class="text-xl md:text-2xl font-bold text-blue-900 mb-6">Record Stock Movement</h1>

        <form action="{{ route('movements.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="item_id" class="block text-sm font-medium text-gray-700 mb-1">Item</label>
                <select name="item_id" id="item_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                    <option value="">Select Item</option>
                    @foreach($items as $itm)
                    <option value="{{ $itm->id }}" {{ old('item_id') == $itm->id ? 'selected' : '' }}
                        data-stock="{{ $itm->current_stock }}" data-dept="{{ $itm->department_id }}">
                        {{ $itm->name }} ({{ $itm->current_stock }} {{ $itm->unit }}) - {{ $itm->department?->name ?? 'N/A' }}
                    </option>
                    @endforeach
                </select>
                @error('item_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Movement Type</label>
                <select name="type" id="type" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required
                        x-data @change="document.getElementById('transfer_fields').style.display = $el.value === 'TRANSFER' ? 'block' : 'none'">
                    <option value="">Select Type</option>
                    <option value="IN" {{ old('type') == 'IN' ? 'selected' : '' }}>Stock In</option>
                    <option value="OUT" {{ old('type') == 'OUT' ? 'selected' : '' }}>Stock Out</option>
                    <option value="TRANSFER" {{ old('type') == 'TRANSFER' ? 'selected' : '' }}>Transfer</option>
                </select>
                @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                <input type="number" name="quantity" id="quantity" value="{{ old('quantity') }}" min="1"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                @error('quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div id="transfer_fields" style="{{ old('type') == 'TRANSFER' ? '' : 'display: none' }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-blue-50 rounded-lg">
                    <div>
                        <label for="from_department" class="block text-sm font-medium text-gray-700 mb-1">From Department</label>
                        <select name="from_department" id="from_department" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('from_department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="to_department" class="block text-sm font-medium text-gray-700 mb-1">To Department</label>
                        <select name="to_department" id="to_department" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('to_department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div>
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                <select name="reason" id="reason" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">Select Reason (optional)</option>
                    <option value="Purchase" {{ old('reason') == 'Purchase' ? 'selected' : '' }}>Purchase</option>
                    <option value="Usage" {{ old('reason') == 'Usage' ? 'selected' : '' }}>Usage</option>
                    <option value="Return" {{ old('reason') == 'Return' ? 'selected' : '' }}>Return</option>
                    <option value="Wastage" {{ old('reason') == 'Wastage' ? 'selected' : '' }}>Wastage</option>
                    <option value="Monthly Replenishment" {{ old('reason') == 'Monthly Replenishment' ? 'selected' : '' }}>Monthly Replenishment</option>
                    <option value="Other" {{ old('reason') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('reason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" id="notes" rows="3" 
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                          placeholder="Additional notes">{{ old('notes') }}</textarea>
                @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-4">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
                    Record Movement
                </button>
                <a href="{{ route('movements.index') }}" class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const transferFields = document.getElementById('transfer_fields');
    
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            transferFields.style.display = this.value === 'TRANSFER' ? 'block' : 'none';
        });
    }
});
</script>
@endpush
