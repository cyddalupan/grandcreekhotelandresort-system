<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $query = Bill::query();

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('type', 'like', "%{$search}%")
                  ->orWhere('provider', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%");
            });
        }

        $bills = $query->orderBy('due_date')->paginate(20);

        $totalPending = Bill::pending()->sum('amount');
        $totalOverdue = Bill::overdue()->sum('amount');
        $totalPaid = Bill::paid()->sum('amount');
        $pendingCount = Bill::pending()->count();
        $overdueCount = Bill::overdue()->count();

        $paidCount = Bill::paid()->count();

        return view('bills.index', compact(
            'bills', 'totalPending', 'totalOverdue',
            'totalPaid', 'pendingCount', 'overdueCount', 'paidCount'
        ));
    }

    public function create()
    {
        return view('bills.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|in:Pending,Paid,Overdue,Cancelled',
            'billing_period' => 'nullable|string|max:255',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:255',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        Bill::create($validated);

        return redirect()->route('bills.index')
            ->with('success', 'Bill created successfully.');
    }

    public function edit(Bill $bill)
    {
        return view('bills.edit', compact('bill'));
    }

    public function update(Request $request, Bill $bill)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|in:Pending,Paid,Overdue,Cancelled',
            'billing_period' => 'nullable|string|max:255',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:255',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $bill->update($validated);

        return redirect()->route('bills.index')
            ->with('success', 'Bill updated successfully.');
    }

    public function pay(Request $request, Bill $bill)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:255',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $bill->update([
            'status' => 'Paid',
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'payment_reference' => $validated['payment_reference'],
        ]);

        return redirect()->route('bills.index')
            ->with('success', 'Bill marked as paid successfully.');
    }

    public function destroy(Bill $bill)
    {
        $bill->delete();

        return redirect()->route('bills.index')
            ->with('success', 'Bill deleted successfully.');
    }
}
