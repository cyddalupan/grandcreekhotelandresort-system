@extends('layouts.app')

@section('page-header', 'Payroll')
@section('title', 'Payroll - ' . config('app.name'))

@section('content')
<div class="space-y-4 md:space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-blue-900">Payroll</h1>
            <p class="text-sm md:text-base text-gray-600">Manage employee payroll and salary records</p>
        </div>
        <div class="flex gap-2">
            <button x-data @click="$dispatch('open-modal', 'batch-modal')"
                    class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Batch Generate
            </button>
            <a href="{{ route('payrolls.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Payroll
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total Paid</p>
            <p class="text-2xl font-bold text-green-700">₱{{ number_format($stats['total_paid'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Pending Payments</p>
            <p class="text-2xl font-bold text-yellow-700">₱{{ number_format($stats['total_pending'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Draft Total</p>
            <p class="text-2xl font-bold text-gray-700">₱{{ number_format($stats['total_draft'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Employees on Payroll</p>
            <p class="text-2xl font-bold text-blue-900">{{ $stats['employee_count'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Month</label>
                <select name="month" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Year</label>
                <select name="year" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All</option>
                    @foreach(range(now()->year, now()->year - 2, -1) as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Employee</label>
                <select name="employee_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg">Filter</button>
            <a href="{{ route('payrolls.index') }}" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900">Clear</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">Period</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Days</th>
                        <th class="px-4 md:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gross</th>
                        <th class="px-4 md:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Deductions</th>
                        <th class="px-4 md:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net</th>
                        <th class="px-4 md:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 md:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($payrolls as $p)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-blue-900 text-white flex items-center justify-center text-xs font-bold mr-3 flex-shrink-0">
                                    {{ substr($p->employee->first_name, 0, 1) }}{{ substr($p->employee->last_name, 0, 1) }}
                                </div>
                                <div>
                                    <a href="{{ route('payrolls.show', $p) }}" class="text-sm font-medium text-gray-900 hover:text-blue-900">{{ $p->employee->full_name }}</a>
                                    <p class="text-xs text-gray-500">{{ $p->employee->department?->name ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-700 hidden sm:table-cell">
                            {{ $p->period_start->format('M d') }} - {{ $p->period_end->format('M d, Y') }}
                        </td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-700 hidden md:table-cell">{{ $p->work_days }}</td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">₱{{ number_format($p->gross_pay, 2) }}</td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right hidden lg:table-cell">₱{{ number_format($p->deductions, 2) }}</td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm font-semibold text-right">₱{{ number_format($p->net_pay, 2) }}</td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap text-center">
                            @php
                                $badge = ['draft' => 'bg-gray-100 text-gray-800', 'pending' => 'bg-yellow-100 text-yellow-800', 'paid' => 'bg-green-100 text-green-800', 'cancelled' => 'bg-red-100 text-red-800'];
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $badge[$p->status] ?? '' }}">{{ ucfirst($p->status) }}</span>
                        </td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('payrolls.show', $p) }}" class="p-1.5 text-gray-400 hover:text-blue-900" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @if($p->status === 'draft')
                                <a href="{{ route('payrolls.edit', $p) }}" class="p-1.5 text-gray-400 hover:text-blue-900" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('payrolls.approve', $p) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-green-600" title="Approve">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                </form>
                                @endif
                                @if($p->status === 'pending')
                                <form action="{{ route('payrolls.pay', $p) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-green-600" title="Mark Paid" onclick="return confirm('Mark this payroll as paid?')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </button>
                                </form>
                                @endif
                                @if(in_array($p->status, ['draft', 'cancelled']))
                                <form action="{{ route('payrolls.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('Delete this payroll record?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <p class="text-gray-500">No payroll records found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payrolls->hasPages())
        <div class="px-4 md:px-6 py-4 border-t border-gray-200">{{ $payrolls->links() }}</div>
        @endif
    </div>

    {{-- Batch Generate Modal --}}
    <div x-data="{ open: false }" @open-modal.window="if ($event.detail === 'batch-modal') open = true" x-cloak>
        <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
                <div x-show="open" @click="open = false" class="fixed inset-0 bg-black/50 transition-opacity"></div>
                <div x-show="open" @click.away="open = false" class="relative bg-white rounded-xl shadow-2xl max-w-lg w-full p-6 md:p-8 text-left">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Batch Generate Payroll</h3>
                    <form action="{{ route('payrolls.batch-create') }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Period Start *</label>
                                    <input type="date" name="period_start" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Period End *</label>
                                    <input type="date" name="period_end" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Work Days *</label>
                                <input type="number" name="work_days" min="1" max="31" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">Select Employees *</label>
                                <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-2 space-y-1">
                                    @foreach($employees as $emp)
                                    <label class="flex items-center gap-2 px-2 py-1 hover:bg-gray-50 rounded">
                                        <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" class="rounded border-gray-300 text-blue-900">
                                        <span class="text-sm text-gray-700">{{ $emp->full_name }} — <span class="text-gray-500">{{ $emp->position }}</span></span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            <p class="text-xs text-gray-500">Pay is auto-calculated: (salary / 22) × work days. Standard 10% deduction applied.</p>
                        </div>
                        <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                            <button type="button" @click="open = false" class="text-sm text-gray-600 hover:text-gray-900">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Generate</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
