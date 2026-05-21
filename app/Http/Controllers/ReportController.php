<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Department;
use App\Models\Item;
use App\Models\Setting;
use App\Models\Supplier;
use Illuminate\Http\Request;

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

    public function exportCsv()
    {
        $items = Item::with('department')->get();
        $lowStockItems = $items->filter(fn($item) => $item->current_stock <= $item->min_stock);

        $filename = 'grand-creek-report-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($items, $lowStockItems) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'Item Name',
                'Category',
                'Department',
                'Current Stock',
                'Min Stock',
                'Unit',
                'Purchase Cost (₱)',
                'Selling Price (₱)',
                'Stock Value (₱)',
                'Status',
            ]);

            foreach ($items as $item) {
                $isLow = $item->current_stock <= $item->min_stock;
                fputcsv($handle, [
                    $item->name,
                    $item->category ?? 'N/A',
                    $item->department?->name ?? 'N/A',
                    $item->current_stock,
                    $item->min_stock,
                    $item->unit,
                    number_format($item->purchase_cost, 2),
                    number_format($item->selling_price, 2),
                    number_format($item->current_stock * $item->purchase_cost, 2),
                    $isLow ? 'Low Stock' : 'OK',
                ]);
            }

            // Blank row
            fputcsv($handle, []);

            // Summary section
            fputcsv($handle, ['=== Summary ===']);
            fputcsv($handle, ['Total Items', $items->count()]);
            fputcsv($handle, ['Low Stock Items', $lowStockItems->count()]);
            fputcsv($handle, ['Total Inventory Value (₱)', number_format($items->sum(fn($i) => $i->current_stock * $i->purchase_cost), 2)]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
