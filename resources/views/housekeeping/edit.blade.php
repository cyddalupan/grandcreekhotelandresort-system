<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Edit Housekeeping Task</h2>
    </x-slot>

    <div class="py-6">
        <form method="POST" action="{{ route('housekeeping.update', $housekeeping) }}" class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Room *</label>
                    <select name="room_id" required class="w-full rounded border-gray-300 text-sm">
                        <option value="">Select Room</option>
                        @foreach($rooms as $room)
                        <option value="{{ $room->id }}" @selected(old('room_id', $housekeeping->room_id)==$room->id)>
                            {{ $room->room_number }} @if($room->roomType) ({{ $room->roomType->name }}) @endif
                        </option>
                        @endforeach
                    </select>
                    @error('room_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Task Type *</label>
                    <select name="task_type" required class="w-full rounded border-gray-300 text-sm">
                        <option value="">Select Type</option>
                        <option value="cleaning" @selected(old('task_type', $housekeeping->task_type)=='cleaning')>Cleaning</option>
                        <option value="maintenance" @selected(old('task_type', $housekeeping->task_type)=='maintenance')>Maintenance</option>
                        <option value="inspection" @selected(old('task_type', $housekeeping->task_type)=='inspection')>Inspection</option>
                        <option value="turndown" @selected(old('task_type', $housekeeping->task_type)=='turndown')>Turndown Service</option>
                        <option value="deep_clean" @selected(old('task_type', $housekeeping->task_type)=='deep_clean')>Deep Clean</option>
                    </select>
                    @error('task_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority *</label>
                    <select name="priority" required class="w-full rounded border-gray-300 text-sm">
                        <option value="normal" @selected(old('priority', $housekeeping->priority)=='normal')>Normal</option>
                        <option value="low" @selected(old('priority', $housekeeping->priority)=='low')>Low</option>
                        <option value="high" @selected(old('priority', $housekeeping->priority)=='high')>High</option>
                        <option value="urgent" @selected(old('priority', $housekeeping->priority)=='urgent')>Urgent</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Schedule Date *</label>
                    <input type="date" name="scheduled_date" value="{{ old('scheduled_date', $housekeeping->scheduled_date->format('Y-m-d')) }}" required class="w-full rounded border-gray-300 text-sm">
                    @error('scheduled_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign To (optional)</label>
                    <select name="assigned_to" class="w-full rounded border-gray-300 text-sm">
                        <option value="">Unassigned</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected(old('assigned_to', $housekeeping->assigned_to)==$emp->id)>{{ $emp->full_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full rounded border-gray-300 text-sm" placeholder="Optional notes...">{{ old('notes', $housekeeping->notes) }}</textarea>
                @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 justify-end">
                <a href="{{ route('housekeeping.index') }}" class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 font-medium">Update Task</button>
            </div>
        </form>
    </div>
</x-app-layout>
