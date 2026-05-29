<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $categories = Item::select('category')->distinct()->whereNotNull('category')->pluck('category');
        $items = Item::where('current_stock', '>', 0)->orderBy('name')->get();
        $recent = Sale::latest()->take(5)->get();
        $posItems = $items->map(fn($i) => [
            'id'       => $i->id,
            'name'     => $i->name,
            'price'    => (float) $i->selling_price,
            'stock'    => $i->current_stock,
            'unit'     => $i->unit,
            'category' => $i->category,
        ])->values();
        return view('pos.index', compact('categories', 'items', 'recent', 'posItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items'            => 'required|json',
            'subtotal'         => 'required|numeric|min:0',
            'tax_percent'      => 'required|numeric|min:0|max:100',
            'tax_amount'       => 'required|numeric|min:0',
            'discount'         => 'required|numeric|min:0',
            'total'            => 'required|numeric|min:0',
            'payment_method'   => 'required|string|max:50',
            'tendered_amount'  => 'required|numeric|min:0',
            'change'           => 'required|numeric|min:0',
            'notes'            => 'nullable|string|max:500',
        ]);

        $items = json_decode($validated['items'], true);

        DB::transaction(function () use ($items, $validated) {
            $sale = Sale::create([
                'receipt_number'   => Sale::generateReceiptNumber(),
                'items'            => $items,
                'subtotal'         => $validated['subtotal'],
                'tax_percent'      => $validated['tax_percent'],
                'tax_amount'       => $validated['tax_amount'],
                'discount'         => $validated['discount'],
                'total'            => $validated['total'],
                'payment_method'   => $validated['payment_method'],
                'tendered_amount'  => $validated['tendered_amount'],
                'change'           => $validated['change'],
                'user_id'          => Auth::id(),
                'notes'            => $validated['notes'] ?? null,
            ]);

            // Deduct stock for inventory items
            foreach ($items as $item) {
                if (isset($item['item_id'])) {
                    $invItem = Item::find($item['item_id']);
                    if ($invItem) {
                        $invItem->decrement('current_stock', $item['quantity']);
                    }
                }
            }
        });

        return response()->json(['success' => true]);
    }

    public function show(Sale $sale)
    {
        return view('pos.show', compact('sale'));
    }

    public function history(Request $request)
    {
        $query = Sale::with('user');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->latest()->paginate(20);

        $stats = [
            'total_sales'       => Sale::count(),
            'today_sales'       => Sale::whereDate('created_at', today())->sum('total'),
            'total_revenue'     => Sale::sum('total'),
            'today_count'       => Sale::whereDate('created_at', today())->count(),
        ];

        return view('pos.history', compact('sales', 'stats'));
    }

    public function searchItems(Request $request)
    {
        $query = Item::where('current_stock', '>', 0);

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        return response()->json($query->orderBy('name')->get()->map(fn($i) => [
            'id'       => $i->id,
            'name'     => $i->name,
            'price'    => (float) $i->selling_price,
            'stock'    => $i->current_stock,
            'unit'     => $i->unit,
            'category' => $i->category,
        ]));
    }
}
