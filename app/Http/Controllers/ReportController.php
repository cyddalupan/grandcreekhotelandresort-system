<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Department;
use App\Models\Item;
use App\Models\Setting;
use App\Models\Supplier;

class ReportController extends Controller
{
    public function index()
    {
        $settings = Setting::getSettings();
        $items = Item::with('department')->get();

        $totalInventoryValue = $items->sum(fn($item) => $item->current_stock * $item->purchase_cost);
        $totalItems = Item::count();

        $totalPaidBills = Bill::paid()->sum('amount');
        $paidBillsCount = Bill::paid()->count();
        $pendingBillAmount = Bill::pending()->sum('amount');
        $pendingBillsCount = Bill::pending()->count();
        $overdueBillAmount = Bill::overdue()->sum('amount');
        $overdueBillsCount = Bill::overdue()->count();
        $totalSuppliers = Supplier::count();

        // Department breakdown
        $departmentBreakdown = [];
        foreach (Department::orderBy('name')->get() as $dept) {
            $deptItems = $items->where('department_id', $dept->id);
            $value = $deptItems->sum(fn($item) => $item->current_stock * $item->purchase_cost);
            $departmentBreakdown[] = [
                'name' => $dept->name,
                'value' => $value,
            ];
        }

        // Low stock items
        $lowStockItems = $items->filter(fn($item) => $item->current_stock <= $item->min_stock);

        return view('reports.index', compact(
            'totalInventoryValue', 'totalItems',
            'totalPaidBills', 'paidBillsCount',
            'pendingBillAmount', 'pendingBillsCount',
            'overdueBillAmount', 'overdueBillsCount',
            'totalSuppliers',
            'departmentBreakdown', 'lowStockItems',
            'settings'
        ));
    }
}
