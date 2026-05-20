@extends('layouts.app')

@section('page-header', 'Settings')
@section('title', 'Settings - ' . config('app.name'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
        <h1 class="text-xl md:text-2xl font-bold text-blue-900 mb-6">Settings</h1>

        @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg text-sm font-medium">
            {{ session('success') }}
        </div>
        @endif

        <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="hotel_name" class="block text-sm font-medium text-gray-700 mb-1">Hotel Name</label>
                <input type="text" name="hotel_name" id="hotel_name" value="{{ old('hotel_name', $settings->hotel_name) }}"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                @error('hotel_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                <select name="currency" id="currency" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="PHP" {{ old('currency', $settings->currency) == 'PHP' ? 'selected' : '' }}>₱ - Philippine Peso</option>
                    <option value="USD" {{ old('currency', $settings->currency) == 'USD' ? 'selected' : '' }}>$ - US Dollar</option>
                    <option value="EUR" {{ old('currency', $settings->currency) == 'EUR' ? 'selected' : '' }}>€ - Euro</option>
                    <option value="JPY" {{ old('currency', $settings->currency) == 'JPY' ? 'selected' : '' }}>¥ - Japanese Yen</option>
                </select>
                @error('currency') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="low_stock_threshold" class="block text-sm font-medium text-gray-700 mb-1">Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" id="low_stock_threshold" 
                           value="{{ old('low_stock_threshold', $settings->low_stock_threshold) }}" min="1"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <p class="text-xs text-gray-500 mt-1">Items below this will show low stock alerts</p>
                    @error('low_stock_threshold') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="bill_alert_days" class="block text-sm font-medium text-gray-700 mb-1">Bill Alert (Days Before)</label>
                    <input type="number" name="bill_alert_days" id="bill_alert_days" 
                           value="{{ old('bill_alert_days', $settings->bill_alert_days) }}" min="1"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <p class="text-xs text-gray-500 mt-1">Days before due date to show alerts</p>
                    @error('bill_alert_days') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="border-t pt-6">
                <h3 class="text-sm font-semibold text-gray-800 mb-4">Notifications</h3>
                <div class="space-y-3">
                    @php $notifications = $settings->notifications ?? []; @endphp
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="notifications_low_stock" value="1" 
                               {{ old('notifications_low_stock', $notifications['low_stock'] ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Low Stock Alerts</span>
                    </label>
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="notifications_bill_due" value="1" 
                               {{ old('notifications_bill_due', $notifications['bill_due'] ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Bill Due Reminders</span>
                    </label>
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="notifications_overdue_bill" value="1" 
                               {{ old('notifications_overdue_bill', $notifications['overdue_bill'] ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Overdue Bill Alerts</span>
                    </label>
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="notifications_purchase_approval" value="1" 
                               {{ old('notifications_purchase_approval', $notifications['purchase_approval'] ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Purchase Approval Requests</span>
                    </label>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
