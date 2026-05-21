<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Housekeeping</h2>
            <a href="{{ route('housekeeping.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition text-sm font-medium">+ New Task</a>
        </div>
    </x-slot>

    <div class="py-6">
        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-amber-400">
                <p class="text-sm text-gray-500">Pending</p>
                <p class="text-2xl font-bold text-amber-600">{{ $pendingCount }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-400">
                <p class="text-sm text-gray-500">In Progress</p>
                <p class="text-2xl font-bold text-blue-600">{{ $inProgressCount }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-400">
                <p class="text-sm text-gray-500">Completed Today</p>
                <p class="text-2xl font-bold text-emerald-600">{{ $completedToday }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-400">
                <p class="text-sm text-gray-500">Scheduled Today</p>
                <p class="text-2xl font-bold text-purple-600">{{ $totalToday }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full rounded border-gray-300 text-sm" onchange="this.form.submit()">
                        <option value="all">All</option>
                        <option value="pending" @selected(request('status')=='pending')>Pending</option>
                        <option value="in_progress" @selected(request('status')=='in_progress')>In Progress</option>
                        <option value="completed" @selected(request('status')=='completed')>Completed</option>
                        <option value="verified" @selected(request('status')=='verified')>Verified</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Task Type</label>
                    <select name="task_type" class="w-full rounded border-gray-300 text-sm" onchange="this.form.submit()">
                        <option value="all">All</option>
                        <option value="cleaning" @selected(request('task_type')=='cleaning')>Cleaning</option>
                        <option value="maintenance" @selected(request('task_type')=='maintenance')>Maintenance</option>
                        <option value="inspection" @selected(request('task_type')=='inspection')>Inspection</option>
                        <option value="turndown" @selected(request('task_type')=='turndown')>Turndown</option>
                        <option value="deep_clean" @selected(request('task_type')=='deep_clean')>Deep Clean</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <select name="priority" class="w-full rounded border-gray-300 text-sm" onchange="this.form.submit()">
                        <option value="all">All</option>
                        <option value="urgent" @selected(request('priority')=='urgent')>Urgent</option>
                        <option value="high" @selected(request('priority')=='high')>High</option>
                        <option value="normal" @selected(request('priority')=='normal')>Normal</option>
                        <option value="low" @selected(request('priority')=='low')>Low</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Room</label>
                    <input type="text" name="search" placeholder="Room #..." value="{{ request('search') }}" class="w-full rounded border-gray-300 text-sm">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Filter</button>
                    <a href="{{ route('housekeeping.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">Reset</a>
                </div>
            </form>
        </div>

        {{-- Task Board --}}
        <div class="space-y-3">
            @forelse($tasks as $task)
            <div class="bg-white rounded-lg shadow p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4 flex-1">
                        {{-- Priority badge --}}
                        @php
                            $priorityColors = ['urgent'=>'bg-red-100 text-red-700','high'=>'bg-orange-100 text-orange-700','normal'=>'bg-blue-100 text-blue-700','low'=>'bg-gray-100 text-gray-600'];
                        @endphp
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $priorityColors[$task->priority] ?? 'bg-gray-100' }}">{{ ucfirst($task->priority) }}</span>

                        <div>
                            <a href="{{ route('housekeeping.show', $task) }}" class="font-medium text-gray-800 hover:text-blue-600">
                                Room {{ $task->room->room_number ?? 'N/A' }}
                            </a>
                            <span class="text-sm text-gray-500 ml-2">{{ ucwords(str_replace('_', ' ', $task->task_type)) }}</span>
                        </div>

                        @if($task->assignedStaff)
                        <span class="text-xs text-gray-500">👤 {{ $task->assignedStaff->name }}</span>
                        @endif

                        <span class="text-xs text-gray-400">{{ $task->scheduled_date->format('M d') }}</span>
                    </div>

                    <div class="flex items-center gap-3">
                        @php
                            $statusColors = ['pending'=>'bg-amber-100 text-amber-700','in_progress'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700','verified'=>'bg-purple-100 text-purple-700'];
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$task->status] ?? 'bg-gray-100' }}">
                            {{ str_replace('_', ' ', ucwords($task->status)) }}
                        </span>
                        <a href="{{ route('housekeeping.show', $task) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">View</a>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">No housekeeping tasks found.</div>
            @endforelse

            <div class="mt-4">{{ $tasks->links() }}</div>
        </div>
    </div>
</x-app-layout>
