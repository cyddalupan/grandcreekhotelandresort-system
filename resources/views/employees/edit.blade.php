@extends('layouts.app')

@section('page-header', 'Edit Employee')
@section('title', 'Edit Employee - ' . config('app.name'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('employees.index') }}" class="text-sm text-gray-500 hover:text-blue-900 transition-colors">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Employees
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Edit Employee: {{ $employee->full_name }}</h2>

        <form action="{{ route('employees.update', $employee) }}" method="POST" class="space-y-6">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Employee ID *</label>
                    <input type="text" id="employee_id" name="employee_id" value="{{ old('employee_id', $employee->employee_id) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('employee_id') border-red-500 @enderror">
                    @error('employee_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select id="department_id" name="department_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('department_id') border-red-500 @enderror">
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $employee->first_name) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('first_name') border-red-500 @enderror">
                    @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $employee->last_name) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('last_name') border-red-500 @enderror">
                    @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position *</label>
                    <input type="text" id="position" name="position" value="{{ old('position', $employee->position) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('position') border-red-500 @enderror">
                    @error('position') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="hire_date" class="block text-sm font-medium text-gray-700 mb-1">Hire Date *</label>
                    <input type="date" id="hire_date" name="hire_date" value="{{ old('hire_date', $employee->hire_date->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('hire_date') border-red-500 @enderror">
                    @error('hire_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="salary" class="block text-sm font-medium text-gray-700 mb-1">Salary (PHP) *</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-500">₱</span>
                    <input type="number" step="0.01" min="0" id="salary" name="salary" value="{{ old('salary', $employee->salary) }}"
                           class="w-full pl-8 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('salary') border-red-500 @enderror">
                </div>
                @error('salary') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $employee->email) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $employee->phone) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea id="address" name="address" rows="2"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('address', $employee->address) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="emergency_contact" class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact Person</label>
                    <input type="text" id="emergency_contact" name="emergency_contact" value="{{ old('emergency_contact', $employee->emergency_contact) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label for="emergency_phone" class="block text-sm font-medium text-gray-700 mb-1">Emergency Phone</label>
                    <input type="text" id="emergency_phone" name="emergency_phone" value="{{ old('emergency_phone', $employee->emergency_phone) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="terminated" {{ old('status', $employee->status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('employees.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Update Employee
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
