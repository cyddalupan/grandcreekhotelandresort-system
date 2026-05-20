@extends('layouts.app')

@section('page-header', 'Employees')
@section('title', 'Employees - ' . config('app.name'))

@section('content')
<div class="space-y-4 md:space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-blue-900">Employees</h1>
            <p class="text-sm md:text-base text-gray-600">Manage hotel staff and personnel</p>
        </div>
        <a href="{{ route('employees.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors w-full sm:w-auto">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Add Employee
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Total Employees</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Active</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-indigo-100 rounded-lg">
                    <svg class="w-6 h-6 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Departments</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['departments']->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Employees Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Position</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Department</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Salary</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 md:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($employees as $emp)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500">{{ $emp->employee_id }}</td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-blue-900 text-white flex items-center justify-center text-xs font-bold mr-3 flex-shrink-0">
                                    {{ substr($emp->first_name, 0, 1) }}{{ substr($emp->last_name, 0, 1) }}
                                </div>
                                <div>
                                    <a href="{{ route('employees.show', $emp) }}" class="text-sm font-medium text-gray-900 hover:text-blue-900">{{ $emp->full_name }}</a>
                                    <p class="text-xs text-gray-500 sm:hidden">{{ $emp->position }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-700 hidden sm:table-cell">{{ $emp->position }}</td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-700 hidden md:table-cell">{{ $emp->department?->name ?? 'N/A' }}</td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-700 hidden lg:table-cell">₱{{ number_format($emp->salary, 2) }}</td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                            @php
                                $statusClasses = [
                                    'active' => 'bg-green-100 text-green-800',
                                    'inactive' => 'bg-yellow-100 text-yellow-800',
                                    'terminated' => 'bg-red-100 text-red-800',
                                ][$emp->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClasses }}">{{ ucfirst($emp->status) }}</span>
                        </td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap text-right text-sm">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('employees.show', $emp) }}" class="text-gray-400 hover:text-blue-900 transition-colors" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('employees.edit', $emp) }}" class="text-gray-400 hover:text-blue-900 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('employees.destroy', $emp) }}" method="POST" class="inline" onsubmit="return confirm('Remove {{ $emp->full_name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="Remove">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <p class="text-gray-500">No employees found.</p>
                            <a href="{{ route('employees.create') }}" class="inline-block mt-4 px-4 py-2 bg-blue-900 text-white text-sm rounded-lg hover:bg-blue-800">Add Employee</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($employees->hasPages())
        <div class="px-4 md:px-6 py-4 border-t border-gray-200">
            {{ $employees->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
