@extends('layouts.app')

@section('page-header', 'Inventory')
@section('title', 'Inventory - ' . config('app.name'))

@section('content')
<div class="space-y-4 md:space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-blue-900">Inventory</h1>
            <p class="text-sm md:text-base text-gray-600">Track inventory items</p>
        </div>
        <a href="{{ route('inventory.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors w-full sm:w-auto">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Item
        </a>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
        <form action="{{ route('inventory.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 flex items-center gap-2">
                <svg class="w-4 h-4 md:w-5 md:h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search items..." class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <select name="department_id" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-900 text-white text-sm rounded-lg hover:bg-blue-800 transition-colors">Filter</button>
            @if(request()->anyFilled(['search', 'department_id']))
            <a href="{{ route('inventory.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition-colors text-center">Clear</a>
            @endif
        </form>
    </div>

    <!-- Items Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
        @forelse($items as $item)
        @php $isLowStock = $item->current_stock <= $item->min_stock; @endphp
        <div class="bg-white rounded-xl shadow-sm {{ $isLowStock ? 'border-l-4 border-l-red-500' : 'border-l-4 border-l-green-500' }} p-4 md:p-6">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1 min-w-0 mr-2">
                    <h3 class="text-base md:text-lg font-semibold text-gray-800 truncate">{{ $item->name }}</h3>
                    <p class="text-xs md:text-sm text-gray-500 truncate">{{ $item->category }}</p>
                </div>
                @if($isLowStock)
                <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-medium whitespace-nowrap">Low</span>
                @endif
            </div>

            <div class="space-y-2 text-xs md:text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Department:</span>
                    <span class="font-medium truncate ml-2">{{ $item->department?->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Stock:</span>
                    <span class="font-bold {{ $isLowStock ? 'text-red-600' : 'text-green-600' }}">
                        {{ $item->current_stock }} {{ $item->unit }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Min:</span>
                    <span class="font-medium">{{ $item->min_stock }} {{ $item->unit }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Cost:</span>
                    <span class="font-medium">₱{{ number_format($item->purchase_cost, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Value:</span>
                    <span class="font-bold text-blue-900">₱{{ number_format($item->current_stock * $item->purchase_cost, 2) }}</span>
                </div>
                <div class="flex gap-2 mt-3 md:mt-4">
                    <a href="{{ route('inventory.show', $item) }}" 
                       class="flex-1 inline-flex items-center justify-center px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="w-3 h-3 md:w-4 md:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Details
                    </a>
                    <a href="{{ route('inventory.edit', $item) }}" 
                       class="inline-flex items-center justify-center px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-xl shadow-sm p-8 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <p class="text-gray-500">No items found.</p>
            <a href="{{ route('inventory.create') }}" class="inline-block mt-4 px-4 py-2 bg-blue-900 text-white text-sm rounded-lg hover:bg-blue-800">Add Item</a>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $items->appends(request()->query())->links() }}
    </div>
</div>
@endsection
