@extends('layouts.app')

@section('page-header', 'Add Payroll')
@section('title', 'Add Payroll - ' . config('app.name'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('payrolls.index') }}" class="text-sm text-gray-500 hover:text-blue-900 transition-colors">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Payroll
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Create Payroll Record</h2>

        <form action="{{ route('payrolls.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Employee *</label>
                <select id="employee_id" name="employee_id" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('employee_id') border-red-500 @enderror">
                    <option value="">Select Employee</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" data-salary="{{ $emp->salary }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->full_name }} — {{ $emp->position }} (₱{{ number_format($emp->salary, 2) }}/mo)
                        </option>
                    @endforeach
                </select>
                @error('employee_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="period_start" class="block text-sm font-medium text-gray-700 mb-1">Period Start *</label>
                    <input type="date" id="period_start" name="period_start" value="{{ old('period_start', $defaultStart->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('period_start') border-red-500 @enderror">
                </div>
                <div>
                    <label for="period_end" class="block text-sm font-medium text-gray-700 mb-1">Period End *</label>
                    <input type="date" id="period_end" name="period_end" value="{{ old('period_end', $defaultEnd->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('period_end') border-red-500 @enderror">
                </div>
            </div>

            <div>
                <label for="work_days" class="block text-sm font-medium text-gray-700 mb-1">Work Days *</label>
                <input type="number" id="work_days" name="work_days" min="1" max="31" value="{{ old('work_days', 15) }}" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('work_days') border-red-500 @enderror">
                @error('work_days') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="gross_pay" class="block text-sm font-medium text-gray-700 mb-1">Gross Pay (₱) *</label>
                    <input type="number" step="0.01" id="gross_pay" name="gross_pay" value="{{ old('gross_pay') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('gross_pay') border-red-500 @enderror">
                </div>
                <div>
                    <label for="deductions" class="block text-sm font-medium text-gray-700 mb-1">Deductions (₱) *</label>
                    <input type="number" step="0.01" id="deductions" name="deductions" value="{{ old('deductions', 0) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('deductions') border-red-500 @enderror">
                </div>
                <div>
                    <label for="net_pay" class="block text-sm font-medium text-gray-700 mb-1">Net Pay (₱) *</label>
                    <input type="number" step="0.01" id="net_pay" name="net_pay" value="{{ old('net_pay') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 font-semibold @error('net_pay') border-red-500 @enderror">
                </div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
            </div>

            <p class="text-xs text-gray-500 italic">💡 Tip: Use <strong>Batch Generate</strong> from the payroll list to auto-calculate pay for multiple employees at once.</p>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('payrolls.index') }}" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">Create Payroll</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const employeeSelect = document.getElementById('employee_id');
    const workDaysInput = document.getElementById('work_days');
    const grossInput = document.getElementById('gross_pay');
    const deductionsInput = document.getElementById('deductions');
    const netInput = document.getElementById('net_pay');

    function calcPay() {
        const salary = parseFloat(employeeSelect.selectedOptions[0]?.dataset?.salary || 0);
        const days = parseInt(workDaysInput.value) || 0;
        const gross = Math.round((salary / 22) * days * 100) / 100;
        const deductions = Math.round(gross * 0.10 * 100) / 100;
        const net = gross - deductions;
        grossInput.value = gross.toFixed(2);
        deductionsInput.value = deductions.toFixed(2);
        netInput.value = net.toFixed(2);
    }

    employeeSelect.addEventListener('change', calcPay);
    workDaysInput.addEventListener('input', calcPay);
});
</script>
@endsection
