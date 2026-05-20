@extends('layouts.app')

@section('page-header', 'Bills')
@section('title', 'Bills - ' . config('app.name'))

@section('content')
<div class="space-y-4 md:space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-blue-900">Bills &amp; Utilities</h1>
            <p class="text-sm md:text-base text-gray-600">Manage bills and utilities</p>
        </div>
        <a href="{{ route('bills.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors w-full sm:w-auto">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Bill
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-6">
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-green-500 p-4 md:p-6">
            <p class="text-xs md:text-sm font-medium text-gray-600">Total Paid</p>
            <p class="text-lg md:text-2xl font-bold text-green-700">₱{{ number_format($totalPaid, 2) }}</p>
            <p class="text-xs text-gray-500">{{ $paidCount }} paid bills</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-yellow-500 p-4 md:p-6">
            <p class="text-xs md:text-sm font-medium text-gray-600">Total Pending</p>
            <p class="text-lg md:text-2xl font-bold text-yellow-700">₱{{ number_format($totalPending, 2) }}</p>
            <p class="text-xs text-gray-500">{{ $pendingCount }} pending bills</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-red-500 p-4 md:p-6">
            <p class="text-xs md:text-sm font-medium text-gray-600">Total Overdue</p>
            <p class="text-lg md:text-2xl font-bold text-red-700">₱{{ number_format($totalOverdue, 2) }}</p>
            <p class="text-xs text-gray-500">{{ $overdueCount }} overdue bills</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
        <form action="{{ route('bills.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search bills..." class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <select name="status" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                <option value="Overdue" {{ request('status') == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-900 text-white text-sm rounded-lg hover:bg-blue-800 transition-colors">Filter</button>
            @if(request()->anyFilled(['search', 'status']))
            <a href="{{ route('bills.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition-colors text-center">Clear</a>
            @endif
        </form>
    </div>

    <!-- Bills List -->
    <div class="space-y-3">
        @forelse($bills as $bill)
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-5 border-l-4 
            {{ $bill->status === 'Paid' ? 'border-l-green-500' : ($bill->status === 'Overdue' ? 'border-l-red-500' : ($bill->status === 'Pending' ? 'border-l-yellow-500' : 'border-l-gray-400')) }}">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="text-base font-semibold text-gray-800">{{ $bill->type }}</h3>
                        <span class="px-2 py-0.5 text-xs font-medium rounded
                            {{ $bill->status === 'Paid' ? 'bg-green-100 text-green-800' : ($bill->status === 'Overdue' ? 'bg-red-100 text-red-800' : ($bill->status === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                            {{ $bill->status }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600">{{ $bill->provider }}</p>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-xs text-gray-500">
                        <span>Account: {{ $bill->account_number ?? 'N/A' }}</span>
                        <span>Period: {{ $bill->billing_period ?? 'N/A' }}</span>
                        <span>Due: {{ $bill->due_date->format('M d, Y') }}</span>
                        @if($bill->payment_date)
                        <span>Paid: {{ $bill->payment_date->format('M d, Y') }}</span>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold text-blue-900">₱{{ number_format($bill->amount, 2) }}</p>
                    <div class="flex gap-2 mt-2 justify-end">
                        @if($bill->status !== 'Paid' && $bill->status !== 'Cancelled')
                        <a href="{{ route('bills.edit', $bill) }}" 
                           class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            Edit
                        </a>
                        <a href="#" @click.prevent="document.getElementById('pay-form-{{ $bill->id }}').classList.toggle('hidden')"
                           class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-medium hover:bg-green-700 transition-colors">
                            Pay
                        </a>
                        @else
                        <a href="{{ route('bills.edit', $bill) }}" 
                           class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            View
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Pay Form -->
            <div id="pay-form-{{ $bill->id }}" class="hidden mt-4 p-4 bg-green-50 rounded-lg border border-green-200">
                <form action="{{ route('bills.pay', $bill) }}" method="POST" class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    <input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}" 
                           class="rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm" required>
                    <select name="payment_method" class="rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm" required>
                        <option value="">Payment Method</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Check">Check</option>
                        <option value="Cash">Cash</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="GCash">GCash</option>
                    </select>
                    <input type="text" name="payment_reference" placeholder="Reference #" 
                           class="rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors whitespace-nowrap">
                        Confirm Payment
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm p-8 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-gray-500">No bills found.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $bills->appends(request()->query())->links() }}
    </div>
</div>
@endsection
