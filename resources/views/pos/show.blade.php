@extends('layouts.app')

@section('page-header', 'Receipt #' . $sale->receipt_number)
@section('title', 'Receipt - ' . config('app.name'))

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-5 text-center text-white">
            <p class="text-xs opacity-80">GRAND CREEK HOTEL & RESORT</p>
            <h2 class="text-xl font-bold">SALE RECEIPT</h2>
            <p class="text-sm opacity-90 mt-1">{{ $sale->receipt_number }}</p>
        </div>

        <div class="p-6 space-y-4">
            {{-- Date & Cashier --}}
            <div class="flex justify-between text-sm text-gray-600 border-b pb-3">
                <span>{{ $sale->created_at->format('M d, Y · h:i A') }}</span>
                <span>Cashier: {{ $sale->user?->name ?? '—' }}</span>
            </div>

            {{-- Items --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Items</p>
                <div class="space-y-1.5">
                    @foreach($sale->items as $item)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-700">
                            {{ $item['quantity'] }}× {{ $item['name'] }}
                        </span>
                        <span class="font-medium">₱{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Totals --}}
            <div class="border-t pt-3 space-y-1">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Subtotal</span>
                    <span>₱{{ number_format($sale->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Tax ({{ $sale->tax_percent }}%)</span>
                    <span>₱{{ number_format($sale->tax_amount, 2) }}</span>
                </div>
                @if($sale->discount > 0)
                <div class="flex justify-between text-sm text-red-600">
                    <span>Discount</span>
                    <span>-₱{{ number_format($sale->discount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-lg font-bold border-t pt-1">
                    <span>TOTAL</span>
                    <span class="text-blue-900">₱{{ number_format($sale->total, 2) }}</span>
                </div>
            </div>

            {{-- Payment --}}
            <div class="border-t pt-3 space-y-1">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Payment</span>
                    <span class="font-medium text-gray-800">{{ ucfirst($sale->payment_method) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Tendered</span>
                    <span class="font-medium">₱{{ number_format($sale->tendered_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Change</span>
                    <span class="font-bold text-green-700">₱{{ number_format($sale->change, 2) }}</span>
                </div>
            </div>

            @if($sale->notes)
            <div class="border-t pt-3">
                <p class="text-xs text-gray-500">Notes: {{ $sale->notes }}</p>
            </div>
            @endif
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t text-center">
            <p class="text-xs text-gray-500">Thank you for your patronage!</p>
            <div class="flex justify-center gap-3 mt-3">
                <button onclick="window.print()" class="px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm rounded-lg">Print</button>
                <a href="{{ route('pos.history') }}" class="px-4 py-2 border border-gray-300 text-sm rounded-lg text-gray-700 hover:bg-gray-50">Back to Sales</a>
            </div>
        </div>
    </div>
</div>
@endsection
