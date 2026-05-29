<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">
                Room {{ $housekeeping->room->room_number ?? 'N/A' }} — {{ ucwords(str_replace('_', ' ', $housekeeping->task_type)) }}
            </h2>
            <div class="flex gap-2">
                @if($housekeeping->status === 'pending')
                    <a href="{{ route('housekeeping.edit', $housekeeping) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Edit</a>
                @endif
                <a href="{{ route('housekeeping.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        @if(session('success')) <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-lg mb-4 text-sm">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">{{ session('error') }}</div> @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Details --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Task Details</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><span class="text-gray-500">Room:</span> <span class="font-medium">{{ $housekeeping->room->room_number ?? 'N/A' }}</span></div>
                        <div><span class="text-gray-500">Room Type:</span> {{ $housekeeping->room->roomType->name ?? 'N/A' }}</div>
                        <div><span class="text-gray-500">Task Type:</span> {{ ucwords(str_replace('_', ' ', $housekeeping->task_type)) }}</div>
                        <div><span class="text-gray-500">Priority:</span>
                            @php
                                $pColors = ['urgent'=>'bg-red-100 text-red-700','high'=>'bg-orange-100 text-orange-700','normal'=>'bg-blue-100 text-blue-700','low'=>'bg-gray-100 text-gray-600'];
                            @endphp
                            <span class="px-2 py-0.5 rounded text-xs font-medium {{ $pColors[$housekeeping->priority] ?? 'bg-gray-100' }}">{{ ucfirst($housekeeping->priority) }}</span>
                        </div>
                        <div><span class="text-gray-500">Status:</span>
                            @php
                                $sColors = ['pending'=>'bg-amber-100 text-amber-700','in_progress'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700','verified'=>'bg-purple-100 text-purple-700'];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $sColors[$housekeeping->status] ?? 'bg-gray-100' }}">{{ str_replace('_', ' ', ucwords($housekeeping->status)) }}</span>
                        </div>
                        <div><span class="text-gray-500">Scheduled:</span> {{ $housekeeping->scheduled_date->format('M d, Y') }}</div>
                        <div><span class="text-gray-500">Assigned To:</span> {{ $housekeeping->assignedStaff->full_name ?? 'Unassigned' }}</div>
                        @if($housekeeping->completed_at)
                        <div><span class="text-gray-500">Completed:</span> {{ $housekeeping->completed_at->format('M d, Y h:i A') }}</div>
                        <div><span class="text-gray-500">Completed By:</span> {{ $housekeeping->completedBy->name ?? 'N/A' }}</div>
                        @endif
                    </div>
                </div>

                @if($housekeeping->notes)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Notes</h3>
                    <p class="text-sm text-gray-600">{{ $housekeeping->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Sidebar - Actions --}}
            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Actions</h3>
                    <div class="space-y-3">
                        @if($housekeeping->status === 'pending')
                            @if(!$housekeeping->assigned_to)
                            <div>
                                <form method="POST" action="{{ route('housekeeping.assign', $housekeeping) }}">
                                    @csrf
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign Staff</label>
                                    <select name="assigned_to" required class="w-full rounded border-gray-300 text-sm mb-2">
                                        <option value="">Select Staff</option>
                                        @foreach(\App\Models\Employee::orderBy('first_name')->orderBy('last_name')->get() as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 font-medium">Assign</button>
                                </form>
                            </div>
                            @else
                            <form method="POST" action="{{ route('housekeeping.start', $housekeeping) }}">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 font-medium">Start Task</button>
                            </form>
                            @endif
                        @endif

                        @if($housekeeping->status === 'in_progress')
                        <form method="POST" action="{{ route('housekeeping.complete', $housekeeping) }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 font-medium">Mark Completed</button>
                        </form>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Status Timeline</h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-3 h-3 rounded-full {{ in_array($housekeeping->status, ['pending','in_progress','completed','verified']) ? 'bg-amber-500' : 'bg-gray-300' }}"></div>
                            <span>Pending</span>
                            <span class="text-xs text-gray-400">{{ $housekeeping->created_at->format('M d') }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-3 h-3 rounded-full {{ in_array($housekeeping->status, ['in_progress','completed','verified']) ? 'bg-blue-500' : 'bg-gray-300' }}"></div>
                            <span class="{{ in_array($housekeeping->status, ['in_progress','completed','verified']) ? 'text-gray-800' : 'text-gray-400' }}">In Progress</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-3 h-3 rounded-full {{ in_array($housekeeping->status, ['completed','verified']) ? 'bg-emerald-500' : 'bg-gray-300' }}"></div>
                            <span class="{{ in_array($housekeeping->status, ['completed','verified']) ? 'text-gray-800' : 'text-gray-400' }}">Completed</span>
                            @if($housekeeping->completed_at)
                            <span class="text-xs text-gray-400">{{ $housekeeping->completed_at->format('M d') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if(in_array($housekeeping->status, ['pending', 'in_progress']))
                <form method="POST" action="{{ route('housekeeping.destroy', $housekeeping) }}" onsubmit="return confirm('Delete this task?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2.5 bg-red-100 text-red-600 rounded-lg text-sm hover:bg-red-200">Delete Task</button>
                </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
