@extends('layouts.app')

@section('page-header', 'Reports')
@section('title', 'Reports - ' . config('app.name'))

@section('content')
<div class="space-y-4 md:space-y-6">
    <div>
        <h1 class="text-2xl md:text-3xl font-bold text-blue-900">Reports</h1>
        <p class="text-sm md:text-base text-gray-600">Hotel performance overview</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6">
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-blue-500 p-4 md:p-6">
            <p class="text-xs md:text-sm font-medium text-gray-600">Total Inventory Value</p>
            <p class="text-lg md:text-2xl font-bold text-blue-900 mt-1">₱{{ number_format($totalInventoryValue, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $totalItems }} items in stock</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-green-500 p-4 md:p-6">
            <p class="text-xs md:text-sm font-medium text-gray-600">Total Paid Bills</p>
            <p class="text-lg md:text-2xl font-bold text-green-700 mt-1">₱{{ number_format($totalPaidBills, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $paidBillsCount }} bills paid</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-yellow-500 p-4 md:p-6">
            <p class="text-xs md:text-sm font-medium text-gray-600">Pending Bills</p>
            <p class="text-lg md:text-2xl font-bold text-yellow-700 mt-1">₱{{ number_format($pendingBillAmount, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $pendingBillsCount }} unpaid</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-purple-500 p-4 md:p-6">
            <p class="text-xs md:text-sm font-medium text-gray-600">Total Suppliers</p>
            <p class="text-lg md:text-2xl font-bold text-purple-700 mt-1">{{ $totalSuppliers }}</p>
            <p class="text-xs text-gray-500 mt-1">Supply partners</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
        <!-- Department Breakdown -->
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Department Inventory Value</h3>
            @if(count($departmentBreakdown) > 0)
            <div class="space-y-3 md:space-y-4">
                @php $maxVal = max(array_column($departmentBreakdown, 'value')); @endphp
                @foreach($departmentBreakdown as $dept)
                <div>
                    <div class="flex justify-between mb-1 text-sm">
                        <span class="font-medium truncate mr-2">{{ $dept['name'] }}</span>
                        <span class="text-gray-600 whitespace-nowrap">₱{{ number_format($dept['value'], 2) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $maxVal > 0 ? ($dept['value'] / $maxVal) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-500">No department data available.</p>
            @endif
        </div>

        <!-- Bill Type Breakdown -->
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Bills by Status</h3>
            <div class="flex items-center justify-center h-48">
                <div class="grid grid-cols-3 gap-6 text-center">
                    @php
                        $totalBills = max($paidBillsCount + $pendingBillsCount + $overdueBillsCount, 1);
                        $paidPct = round(($paidBillsCount / $totalBills) * 100);
                        $pendingPct = round(($pendingBillsCount / $totalBills) * 100);
                        $overduePct = round(($overdueBillsCount / $totalBills) * 100);
                    @endphp
                    <div>
                        <div class="w-16 h-16 mx-auto rounded-full border-4 border-green-500 flex items-center justify-center mb-2">
                            <span class="text-lg font-bold text-green-600">{{ $paidPct }}%</span>
                        </div>
                        <p class="text-sm font-medium text-gray-700">Paid</p>
                        <p class="text-xs text-gray-500">{{ $paidBillsCount }}</p>
                    </div>
                    <div>
                        <div class="w-16 h-16 mx-auto rounded-full border-4 border-yellow-500 flex items-center justify-center mb-2">
                            <span class="text-lg font-bold text-yellow-600">{{ $pendingPct }}%</span>
                        </div>
                        <p class="text-sm font-medium text-gray-700">Pending</p>
                        <p class="text-xs text-gray-500">{{ $pendingBillsCount }}</p>
                    </div>
                    <div>
                        <div class="w-16 h-16 mx-auto rounded-full border-4 border-red-500 flex items-center justify-center mb-2">
                            <span class="text-lg font-bold text-red-600">{{ $overduePct }}%</span>
                        </div>
                        <p class="text-sm font-medium text-gray-700">Overdue</p>
                        <p class="text-xs text-gray-500">{{ $overdueBillsCount }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Items -->
    @if(count($lowStockItems) > 0)
    <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
        <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Low Stock Items</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left">
                        <th class="pb-2 font-semibold text-gray-600">Item</th>
                        <th class="pb-2 font-semibold text-gray-600">Department</th>
                        <th class="pb-2 font-semibold text-gray-600 text-right">Stock</th>
                        <th class="pb-2 font-semibold text-gray-600 text-right">Min</th>
                        <th class="pb-2 font-semibold text-gray-600 text-right">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockItems as $item)
                    <tr class="border-b last:border-0">
                        <td class="py-2 font-medium">{{ $item->name }}</td>
                        <td class="py-2 text-gray-600">{{ $item->department?->name ?? 'N/A' }}</td>
                        <td class="py-2 text-right text-red-600 font-medium">{{ $item->current_stock }}</td>
                        <td class="py-2 text-right text-gray-600">{{ $item->min_stock }}</td>
                        <td class="py-2 text-right">
                            <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded text-xs font-medium">Low</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
