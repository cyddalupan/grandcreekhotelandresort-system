@extends('layouts.app')

@section('page-header', 'Payroll Details')
@section('title', 'Payroll - ' . config('app.name'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('payrolls.index') }}" class="text-sm text-gray-500 hover:text-blue-900 transition-colors">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Payroll
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-6 md:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-white/20 text-white flex items-center justify-center text-xl font-bold">
                        {{ substr($payroll->employee->first_name, 0, 1) }}{{ substr($payroll->employee->last_name, 0, 1) }}
                    </div>
                    <div class="text-white">
                        <h2 class="text-xl font-bold">{{ $payroll->employee->full_name }}</h2>
                        <p class="text-blue-100 text-sm">{{ $payroll->employee->position }} · {{ $payroll->employee->department?->name }}</p>
                        <p class="text-blue-200 text-xs mt-1">{{ $payroll->period_start->format('M d, Y') }} — {{ $payroll->period_end->format('M d, Y') }}</p>
                    </div>
                </div>
                @php
                    $badge = ['draft' => 'bg-gray-500', 'pending' => 'bg-yellow-500', 'paid' => 'bg-green-500', 'cancelled' => 'bg-red-500'];
                @endphp
                <span class="px-3 py-1 text-xs font-medium rounded-full text-white {{ $badge[$payroll->status] ?? 'bg-gray-500' }}">{{ ucfirst($payroll->status) }}</span>
            </div>
        </div>

        {{-- Pay Breakdown --}}
        <div class="p-6 md:p-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gray-50 rounded-xl p-5 text-center">
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Gross Pay</p>
                <p class="text-2xl font-bold text-gray-800">₱{{ number_format($payroll->gross_pay, 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $payroll->work_days }} work days</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-5 text-center">
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Deductions</p>
                <p class="text-2xl font-bold text-red-600">₱{{ number_format($payroll->deductions, 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $payroll->deductions > 0 ? round(($payroll->deductions / $payroll->gross_pay) * 100, 1) . '%' : 'None' }}</p>
            </div>
            <div class="bg-green-50 rounded-xl p-5 text-center border-2 border-green-200">
                <p class="text-xs text-green-700 uppercase tracking-wider mb-1 font-semibold">Net Pay</p>
                <p class="text-3xl font-bold text-green-700">₱{{ number_format($payroll->net_pay, 2) }}</p>
            </div>
        </div>

        {{-- Details --}}
        <div class="px-6 md:px-8 pb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Employee Info</h4>
                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Employee ID</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $payroll->employee->employee_id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Monthly Salary</dt>
                        <dd class="text-sm font-medium text-gray-900">₱{{ number_format($payroll->employee->salary, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Department</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $payroll->employee->department?->name ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>
            <div>
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Payroll Details</h4>
                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Period</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $payroll->period_start->format('M d') }} — {{ $payroll->period_end->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Work Days</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $payroll->work_days }}</dd>
                    </div>
                    @if($payroll->paid_at)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Paid At</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $payroll->paid_at->format('M d, Y h:i A') }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        @if($payroll->notes)
        <div class="px-6 md:px-8 pb-6">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Notes</h4>
            <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3">{{ $payroll->notes }}</p>
        </div>
        @endif

        {{-- Actions --}}
        <div class="px-6 md:px-8 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <div></div>
            <div class="flex gap-2">
                @if($payroll->status === 'draft')
                <a href="{{ route('payrolls.edit', $payroll) }}" class="px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">Edit</a>
                <form action="{{ route('payrolls.approve', $payroll) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">Approve for Payment</button>
                </form>
                @endif
                @if($payroll->status === 'pending')
                <form action="{{ route('payrolls.pay', $payroll) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" onclick="return confirm('Mark as paid?')" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Mark as Paid
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
