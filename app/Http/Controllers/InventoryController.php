<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with(['department', 'supplier']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        $items = $query->orderBy('name')->paginate(20);
        $departments = Department::where('active', true)->orderBy('name')->get();

        return view('inventory.index', compact('items', 'departments'));
    }

    public function create()
    {
        $departments = Department::where('active', true)->orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        return view('inventory.create', compact('departments', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'current_stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'purchase_cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date',
        ]);

        Item::create($validated);

        return redirect()->route('inventory.index')
            ->with('success', 'Item created successfully.');
    }

    public function show(Item $item)
    {
        $item->load(['department', 'supplier', 'movements' => function ($q) {
            $q->recent()->take(10);
        }]);
        return view('inventory.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $departments = Department::where('active', true)->orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        return view('inventory.edit', compact('item', 'departments', 'suppliers'));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'current_stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'purchase_cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date',
        ]);

        $item->update($validated);

        return redirect()->route('inventory.index')
            ->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()->route('inventory.index')
            ->with('success', 'Item deleted successfully.');
    }
}
