@extends('layouts.app')

@section('page-header', 'Edit Payroll')
@section('title', 'Edit Payroll - ' . config('app.name'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('payrolls.index') }}" class="text-sm text-gray-500 hover:text-blue-900 transition-colors">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Payroll
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-2">Edit Payroll Record</h2>
        <p class="text-sm text-gray-500 mb-6">{{ $payroll->employee->full_name }} — {{ $payroll->period_start->format('M d') }} to {{ $payroll->period_end->format('M d, Y') }}</p>

        <form action="{{ route('payrolls.update', $payroll) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Employee *</label>
                <select id="employee_id" name="employee_id" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id', $payroll->employee_id) == $emp->id ? 'selected' : '' }}>{{ $emp->full_name }} — {{ $emp->position }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Period Start *</label>
                    <input type="date" name="period_start" value="{{ old('period_start', $payroll->period_start->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Period End *</label>
                    <input type="date" name="period_end" value="{{ old('period_end', $payroll->period_end->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Work Days *</label>
                <input type="number" name="work_days" min="1" max="31" value="{{ old('work_days', $payroll->work_days) }}" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gross Pay (₱) *</label>
                    <input type="number" step="0.01" name="gross_pay" value="{{ old('gross_pay', $payroll->gross_pay) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deductions (₱) *</label>
                    <input type="number" step="0.01" name="deductions" value="{{ old('deductions', $payroll->deductions) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Net Pay (₱) *</label>
                    <input type="number" step="0.01" name="net_pay" value="{{ old('net_pay', $payroll->net_pay) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 font-semibold">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('notes', $payroll->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('payrolls.index') }}" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg">Update Payroll</button>
            </div>
        </form>
    </div>
</div>
@endsection
