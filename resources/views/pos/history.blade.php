@extends('layouts.app')

@section('page-header', 'Sales History')
@section('title', 'Sales History - ' . config('app.name'))

@section('content')
<div class="space-y-4 md:space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-blue-900">Sales History</h1>
            <p class="text-sm text-gray-600">Transaction records</p>
        </div>
        <a href="{{ route('pos.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m6 0a3 3 0 100-6H9a3 3 0 000 6m6 0a3 3 0 010 6H9a3 3 0 01-6 0"/></svg>
            Open POS
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total Transactions</p>
            <p class="text-2xl font-bold text-blue-900">{{ $stats['total_sales'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Today's Sales</p>
            <p class="text-2xl font-bold text-green-700">₱{{ number_format($stats['today_sales'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Today's Count</p>
            <p class="text-2xl font-bold text-indigo-700">{{ $stats['today_count'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total Revenue</p>
            <p class="text-2xl font-bold text-blue-900">₱{{ number_format($stats['total_revenue'], 2) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
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
                <label class="block text-xs font-medium text-gray-600 mb-1">Payment</label>
                <select name="payment_method" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="gcash" {{ request('payment_method') == 'gcash' ? 'selected' : '' }}>GCash</option>
                    <option value="maya" {{ request('payment_method') == 'maya' ? 'selected' : '' }}>Maya</option>
                    <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                    <option value="bank" {{ request('payment_method') == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg">Filter</button>
            <a href="{{ route('pos.history') }}" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900">Clear</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                <tr>
                    <th class="text-left px-4 py-3">Receipt #</th>
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-left px-4 py-3">Items</th>
                    <th class="text-right px-4 py-3">Total</th>
                    <th class="text-left px-4 py-3">Payment</th>
                    <th class="text-left px-4 py-3">Cashier</th>
                    <th class="text-center px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($sales as $sale)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs font-medium">{{ $sale->receipt_number }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $sale->created_at->format('M d, Y h:i A') }}</td>
                    <td class="px-4 py-3">
                        <span class="text-gray-800">{{ count($sale->items) }} item(s)</span>
                        <p class="text-xs text-gray-500 truncate max-w-[200px]">
                            {{ collect($sale->items)->pluck('name')->implode(', ') }}
                        </p>
                    </td>
                    <td class="px-4 py-3 text-right font-bold">₱{{ number_format($sale->total, 2) }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs capitalize px-2 py-0.5 rounded-full
                            @switch($sale->payment_method)
                                @case('cash') bg-green-100 text-green-700 @break
                                @case('gcash') bg-blue-100 text-blue-700 @break
                                @case('maya') bg-purple-100 text-purple-700 @break
                                @case('card') bg-yellow-100 text-yellow-700 @break
                                @default bg-gray-100 text-gray-700
                            @endswitch">
                            {{ ucfirst($sale->payment_method) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $sale->user?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('pos.show', $sale) }}" class="text-blue-600 hover:underline text-xs">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-gray-500">No sales yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sales->hasPages())
    <div class="mt-4">{{ $sales->links() }}</div>
    @endif
</div>
@endSection
