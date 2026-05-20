@extends('layouts.app')

@section('page-header', 'Item Details')
@section('title', 'Item Details - ' . config('app.name'))

@section('content')
<div class="max-w-4xl mx-auto">
    @php $isLowStock = $item->current_stock <= $item->min_stock; @endphp

    <div class="bg-white rounded-xl shadow-sm {{ $isLowStock ? 'border-l-4 border-l-red-500' : 'border-l-4 border-l-green-500' }} p-6 md:p-8 mb-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-blue-900">{{ $item->name }}</h1>
                <p class="text-sm text-gray-500">{{ $item->category }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('inventory.edit', $item) }}" class="px-3 py-1.5 bg-blue-900 text-white text-sm rounded-lg hover:bg-blue-800 transition-colors">
                    Edit
                </a>
                <a href="{{ route('inventory.index') }}" class="px-3 py-1.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition-colors">
                    Back
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Stock Status</p>
                <p class="text-lg font-bold {{ $isLowStock ? 'text-red-600' : 'text-green-600' }}">
                    {{ $item->current_stock }} {{ $item->unit }}
                </p>
                <p class="text-xs text-gray-500">Min: {{ $item->min_stock }} {{ $item->unit }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Department</p>
                <p class="font-semibold">{{ $item->department?->name ?? 'N/A' }}</p>
                <p class="text-xs text-gray-500">Supplier: {{ $item->supplier?->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Valuation</p>
                <p class="font-semibold">Cost: ₱{{ number_format($item->purchase_cost, 2) }}</p>
                <p class="text-xs text-gray-500">Total: ₱{{ number_format($item->current_stock * $item->purchase_cost, 2) }}</p>
            </div>
        </div>

        @if($item->expiry_date)
        <div class="mt-4 p-3 {{ $item->expiry_date->isPast() ? 'bg-red-50 text-red-700' : 'bg-yellow-50 text-yellow-700' }} rounded-lg text-sm">
            <span class="font-medium">Expiry Date:</span> {{ $item->expiry_date->format('M d, Y') }}
            @if($item->expiry_date->isPast()) (Expired) @endif
        </div>
        @endif
    </div>

    <!-- Recent Movements -->
    <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Movements</h2>
        @if($item->movements->count() > 0)
        <div class="space-y-3">
            @foreach($item->movements as $movement)
            <div class="flex items-center p-3 border rounded-lg">
                <div class="flex-shrink-0 mr-3">
                    @if($movement->type === 'IN')
                        <span class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        </span>
                    @elseif($movement->type === 'OUT')
                        <span class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        </span>
                    @else
                        <span class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        </span>
                    @endif
                </div>
                <div class="flex-1 min-w-0 mr-2">
                    <p class="text-sm font-medium">{{ $movement->type }} · {{ $movement->quantity }} {{ $item->unit }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $movement->user }} · {{ $movement->date->format('M d, Y h:i A') }}
                        @if($movement->notes) · {{ $movement->notes }} @endif
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-500">No movements recorded for this item.</p>
        @endif
    </div>
</div>
@endsection
