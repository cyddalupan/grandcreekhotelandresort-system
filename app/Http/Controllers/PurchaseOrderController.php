<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'creator', 'approver']);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('po_number', 'like', "%{$s}%")
                  ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        // Stats
        $draftCount  = PurchaseOrder::where('status', 'draft')->count();
        $pendingCount = PurchaseOrder::whereIn('status', ['approved', 'sent', 'partially_received'])->count();
        $receivedCount = PurchaseOrder::where('status', 'received')->count();

        return view('purchase-orders.index', compact('orders', 'draftCount', 'pendingCount', 'receivedCount'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $items     = Item::orderBy('name')->get();
        return view('purchase-orders.create', compact('suppliers', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items'       => 'required|json',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $items = json_decode($validated['items'], true);
        if (!$items || count($items) === 0) {
            return back()->withErrors(['items' => 'At least one item is required.'])->withInput();
        }

        $total = collect($items)->sum('total');

        DB::beginTransaction();
        try {
            $po = PurchaseOrder::create([
                'po_number'    => PurchaseOrder::generatePoNumber(),
                'supplier_id'  => $validated['supplier_id'],
                'items'        => $items,
                'total_amount' => $total,
                'status'       => 'draft',
                'notes'        => $validated['notes'],
                'created_by'   => Auth::id(),
            ]);
            DB::commit();
            return redirect()->route('purchase-orders.show', $po)
                ->with('success', "PO {$po->po_number} created.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create PO: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'creator', 'approver']);
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canCancel()) {
            return back()->with('error', 'Cannot edit a received/cancelled PO.');
        }
        $suppliers = Supplier::orderBy('name')->get();
        $items     = Item::orderBy('name')->get();
        return view('purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'items'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canCancel()) {
            return back()->with('error', 'Cannot edit a received/cancelled PO.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items'       => 'required|json',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $items = json_decode($validated['items'], true);
        if (!$items || count($items) === 0) {
            return back()->withErrors(['items' => 'At least one item is required.'])->withInput();
        }

        $total = collect($items)->sum('total');

        $status = $purchaseOrder->status;
        // If status was approved/sent, reset to draft on edit
        if (in_array($status, ['approved', 'sent'])) {
            $status = 'draft';
        }

        $purchaseOrder->update([
            'supplier_id'  => $validated['supplier_id'],
            'items'        => $items,
            'total_amount' => $total,
            'status'       => $status,
            'notes'        => $validated['notes'],
        ]);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', "PO {$purchaseOrder->po_number} updated.");
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canCancel()) {
            return back()->with('error', 'Only draft POs can be deleted.');
        }
        $purchaseOrder->delete();
        return redirect()->route('purchase-orders.index')
            ->with('success', "PO deleted.");
    }

    // ─── Workflow actions ───

    public function approve(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canApprove()) {
            return back()->with('error', 'Only draft POs can be approved.');
        }
        $purchaseOrder->update([
            'status'       => 'approved',
            'approved_by'  => Auth::id(),
            'approved_at'  => now(),
        ]);
        return back()->with('success', "PO {$purchaseOrder->po_number} approved.");
    }

    public function send(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canSend()) {
            return back()->with('error', 'Only approved POs can be sent.');
        }
        $purchaseOrder->update(['status' => 'sent']);
        return back()->with('success', "PO {$purchaseOrder->po_number} marked as sent to supplier.");
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canReceive()) {
            return back()->with('error', 'PO cannot be received in its current status.');
        }

        $validated = $request->validate([
            'received_items' => 'nullable|json',
        ]);

        DB::beginTransaction();
        try {
            $items = $purchaseOrder->items;
            $receivedItems = $validated['received_items']
                ? json_decode($validated['received_items'], true)
                : null;

            $allReceived = true;
            foreach ($items as $idx => &$item) {
                $qtyOrdered = (float) ($item['quantity'] ?? $item['qty'] ?? 0);
                $qtyReceived = 0;

                if ($receivedItems && isset($receivedItems[$idx])) {
                    $qtyReceived = (float) $receivedItems[$idx];
                }

                $item['qty_received'] = ($item['qty_received'] ?? 0) + $qtyReceived;
                $item['qty_received'] = min($item['qty_received'], $qtyOrdered);

                // Update stock in items table
                $inventoryItem = Item::find($item['item_id'] ?? null);
                if ($inventoryItem && $qtyReceived > 0) {
                    $inventoryItem->increment('current_stock', $qtyReceived);
                }

                if ($item['qty_received'] < $qtyOrdered) {
                    $allReceived = false;
                }
            }

            $purchaseOrder->items = $items;
            $purchaseOrder->status = $allReceived ? 'received' : 'partially_received';
            if ($allReceived) {
                $purchaseOrder->received_at = now();
            }
            $purchaseOrder->save();

            DB::commit();
            return back()->with('success', "PO {$purchaseOrder->po_number} received.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to receive PO: ' . $e->getMessage());
        }
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canCancel()) {
            return back()->with('error', 'Cannot cancel a received PO.');
        }
        $purchaseOrder->update(['status' => 'cancelled']);
        return back()->with('success', "PO {$purchaseOrder->po_number} cancelled.");
    }

    // ─── AJAX: get supplier items ───
    public function supplierItems(Supplier $supplier)
    {
        $items = Item::where('supplier_id', $supplier->id)
            ->orderBy('name')
            ->get(['id', 'name', 'purchase_cost', 'unit']);
        return response()->json($items);
    }
}
