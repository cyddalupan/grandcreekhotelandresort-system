@extends('layouts.app')

@section('page-header', 'Movements')
@section('title', 'Movements - ' . config('app.name'))

@section('content')
<div class="space-y-4 md:space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-blue-900">Stock Movements</h1>
            <p class="text-sm md:text-base text-gray-600">Track stock movements</p>
        </div>
        <a href="{{ route('movements.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors w-full sm:w-auto">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Record Movement
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
        <form action="{{ route('movements.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <select name="type" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                <option value="">All Types</option>
                <option value="IN" {{ request('type') == 'IN' ? 'selected' : '' }}>Stock In</option>
                <option value="OUT" {{ request('type') == 'OUT' ? 'selected' : '' }}>Stock Out</option>
                <option value="TRANSFER" {{ request('type') == 'TRANSFER' ? 'selected' : '' }}>Transfer</option>
            </select>
            <select name="item_id" class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                <option value="">All Items</option>
                @foreach($items as $itm)
                <option value="{{ $itm->id }}" {{ request('item_id') == $itm->id ? 'selected' : '' }}>{{ $itm->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-900 text-white text-sm rounded-lg hover:bg-blue-800 transition-colors">Filter</button>
            @if(request()->anyFilled(['type', 'item_id']))
            <a href="{{ route('movements.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition-colors text-center">Clear</a>
            @endif
        </form>
    </div>

    <!-- Movements Timeline -->
    <div class="space-y-3">
        @forelse($movements as $movement)
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-5">
            <div class="flex items-start">
                <div class="flex-shrink-0 mr-4">
                    @if($movement->type === 'IN')
                        <span class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        </span>
                    @elseif($movement->type === 'OUT')
                        <span class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        </span>
                    @else
                        <span class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        </span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 text-xs font-medium rounded 
                            {{ $movement->type === 'IN' ? 'bg-green-100 text-green-800' : ($movement->type === 'OUT' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ $movement->type }}
                        </span>
                        <span class="text-sm font-semibold text-gray-800">{{ $movement->item?->name ?? 'Unknown Item' }}</span>
                    </div>
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">{{ $movement->quantity }}</span> units
                        @if($movement->fromDepartment)
                        from <span class="font-medium">{{ $movement->fromDepartment->name }}</span>
                        @endif
                        @if($movement->toDepartment)
                        to <span class="font-medium">{{ $movement->toDepartment->name }}</span>
                        @endif
                        @if($movement->reason)
                        · {{ $movement->reason }}
                        @endif
                    </p>
                    @if($movement->notes)
                    <p class="text-xs text-gray-400 mt-1">{{ $movement->notes }}</p>
                    @endif
                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                        <span>{{ $movement->user }}</span>
                        <span>{{ $movement->date->format('M d, Y h:i A') }}</span>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm p-8 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            <p class="text-gray-500">No movements recorded yet.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $movements->appends(request()->query())->links() }}
    </div>
</div>
@endsection
