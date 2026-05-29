@extends('layouts.app')

@section('page-header', 'Departments')
@section('title', 'Departments - ' . config('app.name'))

@section('content')
<div class="space-y-4 md:space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-blue-900">Departments</h1>
            <p class="text-sm md:text-base text-gray-600">Manage departments</p>
        </div>
        <a href="{{ route('departments.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors w-full sm:w-auto">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Department
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
        @forelse($departments as $dept)
        <div class="bg-white rounded-xl shadow-sm {{ $dept->active ? 'border-l-4 border-l-green-500' : 'border-l-4 border-l-gray-400 opacity-60' }} p-4 md:p-6">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1 min-w-0 mr-2">
                    <h3 class="text-base md:text-lg font-semibold text-gray-800 truncate">{{ $dept->name }}</h3>
                    <p class="text-xs md:text-sm text-gray-500 truncate">{{ $dept->description }}</p>
                </div>
                <span class="px-2 py-1 rounded text-xs font-medium whitespace-nowrap {{ $dept->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $dept->active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <div class="space-y-2 text-xs md:text-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="text-gray-600 mr-2">Manager:</span>
                    <span class="font-medium truncate">{{ $dept->manager ?? 'N/A' }}</span>
                </div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span class="text-gray-600 mr-2">Items:</span>
                    <span class="font-medium">{{ $dept->item_count }}</span>
                </div>
                <div class="flex gap-2 mt-3 md:mt-4">
                    <a href="{{ route('inventory.index', ['department_id' => $dept->id]) }}" 
                       class="flex-1 inline-flex items-center justify-center px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="w-3 h-3 md:w-4 md:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        View Items
                    </a>
                    <a href="{{ route('departments.edit', $dept) }}" 
                       class="inline-flex items-center justify-center px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form action="{{ route('departments.destroy', $dept) }}" method="POST" onsubmit="return confirm('Delete {{ addslashes($dept->name) }}? This cannot be undone.')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 border border-red-200 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">
                            <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-xl shadow-sm p-8 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <p class="text-gray-500">No departments found.</p>
            <a href="{{ route('departments.create') }}" class="inline-block mt-4 px-4 py-2 bg-blue-900 text-white text-sm rounded-lg hover:bg-blue-800">Create Department</a>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $departments->links() }}
    </div>
</div>
@endsection
