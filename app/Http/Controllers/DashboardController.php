<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Item;
use App\Models\Movement;
use App\Models\Setting;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $items = Item::with('department')->get();
        $settings = Setting::getSettings();

        $totalInventoryValue = $items->sum(fn($item) => $item->current_stock * $item->purchase_cost);
        $monthlyExpenses = Bill::paid()->sum('amount');
        $pendingBillsCount = Bill::pending()->count();
        $totalMovements = Movement::count();

        $lowStockItems = $items->filter(fn($item) => $item->current_stock <= $item->min_stock);
        $overdueBills = Bill::overdue()->get();
        $recentMovements = Movement::with(['item', 'fromDepartment', 'toDepartment'])
            ->recent()
            ->take(5)
            ->get();

        $pendingBills = Bill::pending()->orderBy('due_date')->take(5)->get();

        $departmentSpending = [];
        foreach ($items->groupBy('department_id') as $deptId => $deptItems) {
            $deptName = $deptItems->first()->department?->name ?? 'Unassigned';
            $spending = $deptItems->sum(fn($item) => $item->current_stock * $item->purchase_cost);
            if ($spending > 0) {
                $departmentSpending[$deptName] = $spending;
            }
        }

        $paidBillsCount = Bill::paid()->count();

        return view('dashboard', compact(
            'totalInventoryValue', 'monthlyExpenses', 'pendingBillsCount',
            'totalMovements', 'lowStockItems', 'overdueBills',
            'recentMovements', 'pendingBills', 'departmentSpending', 'settings',
            'paidBillsCount'
        ));
    }
}
