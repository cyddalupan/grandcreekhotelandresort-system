<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Employee;
use App\Models\Housekeeping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HousekeepingController extends Controller
{
    public function index(Request $request)
    {
        $query = Housekeeping::with(['room', 'assignedStaff']);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('task_type') && $request->input('task_type') !== 'all') {
            $query->where('task_type', $request->input('task_type'));
        }

        if ($request->filled('priority') && $request->input('priority') !== 'all') {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->whereHas('room', fn($r) => $r->where('room_number', 'like', "%{$s}%"))
                  ->orWhere('notes', 'like', "%{$s}%");
            });
        }

        $tasks = $query->orderByRaw("CASE priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'normal' THEN 2 WHEN 'low' THEN 3 END")
                       ->orderBy('scheduled_date')
                       ->paginate(20);

        // Stats
        $pendingCount  = Housekeeping::where('status', 'pending')->count();
        $inProgressCount = Housekeeping::where('status', 'in_progress')->count();
        $completedToday = Housekeeping::where('status', 'completed')
            ->whereDate('completed_at', today())->count();
        $totalToday = Housekeeping::whereDate('scheduled_date', today())->count();

        return view('housekeeping.index', compact('tasks', 'pendingCount', 'inProgressCount', 'completedToday', 'totalToday'));
    }

    public function create()
    {
        $rooms     = Room::orderBy('room_number')->get();
        $employees = Employee::orderBy('name')->get();
        return view('housekeeping.create', compact('rooms', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id'        => 'required|exists:rooms,id',
            'task_type'      => 'required|in:' . implode(',', Housekeeping::TASK_TYPES),
            'priority'       => 'required|in:' . implode(',', Housekeeping::PRIORITIES),
            'assigned_to'    => 'nullable|exists:employees,id',
            'notes'          => 'nullable|string|max:1000',
            'scheduled_date' => 'required|date',
        ]);

        $validated['status'] = 'pending';

        Housekeeping::create($validated);

        return redirect()->route('housekeeping.index')
            ->with('success', 'Housekeeping task created.');
    }

    public function show(Housekeeping $housekeeping)
    {
        $housekeeping->load(['room.roomType', 'assignedStaff', 'completedBy']);
        return view('housekeeping.show', compact('housekeeping'));
    }

    public function edit(Housekeeping $housekeeping)
    {
        if (in_array($housekeeping->status, ['completed', 'verified'])) {
            return back()->with('error', 'Cannot edit a completed task.');
        }
        $rooms     = Room::orderBy('room_number')->get();
        $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();
        return view('housekeeping.edit', compact('housekeeping', 'rooms', 'employees'));
    }

    public function update(Request $request, Housekeeping $housekeeping)
    {
        if (in_array($housekeeping->status, ['completed', 'verified'])) {
            return back()->with('error', 'Cannot edit a completed task.');
        }

        $validated = $request->validate([
            'room_id'        => 'required|exists:rooms,id',
            'task_type'      => 'required|in:' . implode(',', Housekeeping::TASK_TYPES),
            'priority'       => 'required|in:' . implode(',', Housekeeping::PRIORITIES),
            'assigned_to'    => 'nullable|exists:employees,id',
            'notes'          => 'nullable|string|max:1000',
            'scheduled_date' => 'required|date',
        ]);

        $housekeeping->update($validated);

        return redirect()->route('housekeeping.show', $housekeeping)
            ->with('success', 'Housekeeping task updated.');
    }

    public function destroy(Housekeeping $housekeeping)
    {
        if ($housekeeping->status === 'completed') {
            return back()->with('error', 'Cannot delete a completed task.');
        }
        $housekeeping->delete();
        return redirect()->route('housekeeping.index')
            ->with('success', 'Housekeeping task deleted.');
    }

    // ─── Workflow ───

    public function assign(Request $request, Housekeeping $housekeeping)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:employees,id',
        ]);

        $housekeeping->update([
            'assigned_to' => $validated['assigned_to'],
            'status'      => 'pending',
        ]);

        return back()->with('success', 'Staff assigned to task.');
    }

    public function start(Housekeeping $housekeeping)
    {
        if ($housekeeping->status !== 'pending') {
            return back()->with('error', 'Only pending tasks can be started.');
        }
        $housekeeping->update(['status' => 'in_progress']);
        return back()->with('success', 'Task started.');
    }

    public function complete(Housekeeping $housekeeping)
    {
        if ($housekeeping->status !== 'in_progress') {
            return back()->with('error', 'Only in-progress tasks can be completed.');
        }
        $housekeeping->update([
            'status'       => 'completed',
            'completed_at' => now(),
            'completed_by' => Auth::id(),
        ]);
        return back()->with('success', 'Task completed.');
    }
}
