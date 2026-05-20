@extends('layouts.app')

@section('page-header', 'Edit Department')
@section('title', 'Edit Department - ' . config('app.name'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
        <h1 class="text-xl md:text-2xl font-bold text-blue-900 mb-6">Edit Department</h1>

        <form action="{{ route('departments.update', $department) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Department Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $department->name) }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                       placeholder="e.g., Front Office" required>
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" id="description" rows="3" 
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                          placeholder="Department description">{{ old('description', $department->description) }}</textarea>
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="manager" class="block text-sm font-medium text-gray-700 mb-1">Manager</label>
                <input type="text" name="manager" id="manager" value="{{ old('manager', $department->manager) }}"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                       placeholder="Manager name">
                @error('manager') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center space-x-2">
                <input type="checkbox" name="active" id="active" value="1" {{ old('active', $department->active) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="active" class="text-sm font-medium text-gray-700">Active</label>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-4">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
                    Update Department
                </button>
                <a href="{{ route('departments.index') }}" class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
