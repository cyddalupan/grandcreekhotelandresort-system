<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Item;
use App\Models\Movement;
use Illuminate\Http\Request;

class MovementController extends Controller
{
    public function index(Request $request)
    {
        $query = Movement::with(['item', 'fromDepartment', 'toDepartment']);

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->input('item_id'));
        }

        $movements = $query->recent()->paginate(20);
        $items = Item::orderBy('name')->get();

        return view('movements.index', compact('movements', 'items'));
    }

    public function create()
    {
        $items = Item::with('department')->orderBy('name')->get();
        $departments = Department::where('active', true)->orderBy('name')->get();
        return view('movements.create', compact('items', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'type' => 'required|in:IN,OUT,TRANSFER',
            'quantity' => 'required|integer|min:1',
            'from_department' => 'nullable|required_if:type,OUT|required_if:type,TRANSFER|exists:departments,id',
            'to_department' => 'nullable|required_if:type,IN|required_if:type,TRANSFER|exists:departments,id',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['user'] = auth()->user()?->name ?? 'System';
        $validated['date'] = now();

        $item = Item::findOrFail($validated['item_id']);

        // Stock update logic
        if ($validated['type'] === 'IN') {
            $item->increment('current_stock', $validated['quantity']);
        } elseif ($validated['type'] === 'OUT') {
            if ($item->current_stock < $validated['quantity']) {
                return back()->withErrors(['quantity' => 'Insufficient stock. Available: ' . $item->current_stock])
                    ->withInput();
            }
            $item->decrement('current_stock', $validated['quantity']);
        } elseif ($validated['type'] === 'TRANSFER') {
            if ($item->current_stock < $validated['quantity']) {
                return back()->withErrors(['quantity' => 'Insufficient stock for transfer. Available: ' . $item->current_stock])
                    ->withInput();
            }
            $item->decrement('current_stock', $validated['quantity']);
        }

        Movement::create($validated);

        // Update department item counts
        if (isset($validated['to_department'])) {
            Department::find($validated['to_department'])?->increment('item_count', $validated['quantity']);
        }

        return redirect()->route('movements.index')
            ->with('success', 'Movement recorded successfully.');
    }
}
