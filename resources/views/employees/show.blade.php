@extends('layouts.app')

@section('page-header', 'Employee Details')
@section('title', 'Employee - ' . config('app.name'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('employees.index') }}" class="text-sm text-gray-500 hover:text-blue-900 transition-colors">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Employees
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        {{-- Header --}}
        <div class="bg-blue-900 px-6 py-8 md:px-8">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-white/20 text-white flex items-center justify-center text-2xl font-bold">
                    {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                </div>
                <div class="text-white">
                    <h2 class="text-2xl font-bold">{{ $employee->full_name }}</h2>
                    <p class="text-blue-100">{{ $employee->position }}</p>
                    <p class="text-blue-200 text-sm">{{ $employee->employee_id }}</p>
                </div>
                <div class="ml-auto">
                    @php
                        $statusClasses = [
                            'active' => 'bg-green-500',
                            'inactive' => 'bg-yellow-500',
                            'terminated' => 'bg-red-500',
                        ][$employee->status] ?? 'bg-gray-500';
                    @endphp
                    <span class="px-3 py-1 text-xs font-medium rounded-full {{ $statusClasses }} text-white">{{ ucfirst($employee->status) }}</span>
                </div>
            </div>
        </div>

        {{-- Details --}}
        <div class="p-6 md:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Employment Details</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Department</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $employee->department?->name ?? 'N/A' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Hire Date</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $employee->hire_date->format('M d, Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Salary</dt>
                            <dd class="text-sm font-medium text-gray-900">₱{{ number_format($employee->salary, 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Tenure</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $employee->hire_date->diffInMonths(now()) }} months</dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Contact Information</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Email</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $employee->email ?? 'N/A' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Phone</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $employee->phone ?? 'N/A' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Address</dt>
                            <dd class="text-sm font-medium text-gray-900 text-right max-w-[200px]">{{ $employee->address ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Emergency Contact --}}
            @if($employee->emergency_contact)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Emergency Contact</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Contact Person</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $employee->emergency_contact }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Phone</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $employee->emergency_phone ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="px-6 md:px-8 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3">
            <a href="{{ route('employees.edit', $employee) }}" class="px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Employee
            </a>
        </div>
    </div>
</div>
@endsection
