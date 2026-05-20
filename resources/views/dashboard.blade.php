@extends('layouts.app')

@section('page-header', 'Dashboard')
@section('title', 'Dashboard - ' . config('app.name'))

@section('content')
<div class="space-y-4 md:space-y-6">
    <!-- Welcome -->
    <div>
        <h1 class="text-2xl md:text-3xl font-bold text-blue-900">Dashboard</h1>
        <p class="text-sm md:text-base text-gray-600">Welcome to {{ $settings->hotel_name }}</p>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6">
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-blue-500 p-4 md:p-6">
            <p class="text-xs md:text-sm font-medium text-gray-600">Inventory Value</p>
            <p class="text-lg md:text-2xl font-bold text-blue-900 mt-1">₱{{ number_format($totalInventoryValue, 2) }}</p>
            <p class="text-xs text-green-600 flex items-center mt-1">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                Total stock value
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-yellow-500 p-4 md:p-6">
            <p class="text-xs md:text-sm font-medium text-gray-600">Monthly Expenses</p>
            <p class="text-lg md:text-2xl font-bold text-blue-900 mt-1">₱{{ number_format($monthlyExpenses, 2) }}</p>
            <p class="text-xs text-gray-600 flex items-center mt-1">{{ $paidBillsCount }} paid bills</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-red-500 p-4 md:p-6">
            <p class="text-xs md:text-sm font-medium text-gray-600">Pending Bills</p>
            <p class="text-lg md:text-2xl font-bold text-blue-900 mt-1">{{ $pendingBillsCount }}</p>
            <p class="text-xs text-gray-600 mt-1">{{ $overdueBills->count() }} overdue</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-purple-500 p-4 md:p-6">
            <p class="text-xs md:text-sm font-medium text-gray-600">Movements</p>
            <p class="text-lg md:text-2xl font-bold text-blue-900 mt-1">{{ $totalMovements }}</p>
            <p class="text-xs text-gray-600 mt-1">Total recorded</p>
        </div>
    </div>

    <!-- Alerts Section -->
    @if($lowStockItems->count() > 0 || $overdueBills->count() > 0)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
        @if($lowStockItems->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-yellow-500 p-4 md:p-6">
            <h3 class="flex items-center text-yellow-700 text-base md:text-lg font-semibold mb-3">
                <svg class="w-4 h-4 md:w-5 md:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Low Stock Alerts
            </h3>
            <div class="space-y-2">
                @foreach($lowStockItems->take(5) as $item)
                <div class="flex justify-between items-center p-2 bg-yellow-50 rounded text-sm">
                    <span class="font-medium truncate mr-2">{{ $item->name }}</span>
                    <span class="text-red-600 whitespace-nowrap">{{ $item->current_stock }} {{ $item->unit }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($overdueBills->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-red-500 p-4 md:p-6">
            <h3 class="flex items-center text-red-700 text-base md:text-lg font-semibold mb-3">
                <svg class="w-4 h-4 md:w-5 md:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Overdue Bills
            </h3>
            <div class="space-y-2">
                @foreach($overdueBills as $bill)
                <div class="flex justify-between items-center p-2 bg-red-50 rounded text-sm">
                    <span class="font-medium truncate mr-2">{{ $bill->provider }}</span>
                    <span class="text-red-600 whitespace-nowrap">₱{{ number_format($bill->amount, 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Department Spending -->
    <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
        <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Department Spending</h3>
        @if(count($departmentSpending) > 0)
        <div class="space-y-3 md:space-y-4">
            @foreach(array_slice($departmentSpending, 0, 6) as $dept => $amount)
                @php $percentage = $totalInventoryValue > 0 ? ($amount / $totalInventoryValue) * 100 : 0; @endphp
                <div>
                    <div class="flex justify-between mb-1 text-sm">
                        <span class="font-medium truncate mr-2">{{ $dept }}</span>
                        <span class="text-gray-600 whitespace-nowrap">₱{{ number_format($amount, 2) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-500">No department data available.</p>
        @endif
    </div>

    <!-- Recent Movements & Upcoming Bills -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
        <!-- Recent Movements -->
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Recent Movements</h3>
            @if($recentMovements->count() > 0)
            <div class="space-y-3">
                @foreach($recentMovements as $movement)
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
                        <p class="text-sm font-medium truncate">{{ $movement->item?->name ?? 'Unknown Item' }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $movement->type }} · {{ $movement->quantity }} units
                            @if($movement->toDepartment) → {{ $movement->toDepartment->name }} @endif
                            @if($movement->fromDepartment) ← {{ $movement->fromDepartment->name }} @endif
                        </p>
                    </div>
                    <span class="text-xs text-gray-500 whitespace-nowrap">{{ $movement->date->format('M d') }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-500">No movements recorded yet.</p>
            @endif
        </div>

        <!-- Upcoming Bills -->
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Upcoming Bills</h3>
            @if($pendingBills->count() > 0)
            <div class="space-y-3">
                @foreach($pendingBills as $bill)
                <div class="flex justify-between items-center p-3 border rounded-lg">
                    <div class="flex-1 min-w-0 mr-3">
                        <p class="font-medium text-sm truncate">{{ $bill->type }}</p>
                        <p class="text-xs text-gray-600 truncate">{{ $bill->provider }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-blue-900 text-sm whitespace-nowrap">₱{{ number_format($bill->amount, 2) }}</p>
                        <p class="text-xs text-gray-600 whitespace-nowrap">{{ $bill->due_date->format('M d, Y') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-500">No pending bills.</p>
            @endif
        </div>
    </div>
</div>
@endsection
