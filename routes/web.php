<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\HousekeepingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::resource('inventory', InventoryController::class);
    Route::resource('movements', MovementController::class)->except(['edit', 'update', 'destroy', 'show']);
    Route::resource('bills', BillController::class);
    Route::post('/bills/{bill}/pay', [BillController::class, 'pay'])->name('bills.pay');
    Route::resource('suppliers', SupplierController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('payrolls', PayrollController::class);
    Route::post('/payrolls/batch-create', [PayrollController::class, 'batchCreate'])->name('payrolls.batch-create');
    Route::post('/payrolls/{payroll}/approve', [PayrollController::class, 'approve'])->name('payrolls.approve');
    Route::post('/payrolls/{payroll}/pay', [PayrollController::class, 'pay'])->name('payrolls.pay');
    Route::resource('room-types', RoomTypeController::class);
    Route::resource('rooms', RoomController::class);
    Route::get('/bookings/available-rooms', [BookingController::class, 'availableRooms'])->name('bookings.available-rooms');
    Route::resource('bookings', BookingController::class);
    Route::post('/bookings/{booking}/confirm', [BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::post('/bookings/{booking}/check-in', [BookingController::class, 'checkIn'])->name('bookings.check-in');
    Route::post('/bookings/{booking}/check-out', [BookingController::class, 'checkOut'])->name('bookings.check-out');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');
    Route::get('/pos/history', [PosController::class, 'history'])->name('pos.history');
    Route::get('/pos/{sale}', [PosController::class, 'show'])->name('pos.show');
    Route::get('/pos/items/search', [PosController::class, 'searchItems'])->name('pos.search-items');
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
    Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::get('/purchase-orders/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');
    Route::put('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');
    Route::delete('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');
    Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::post('/purchase-orders/{purchaseOrder}/send', [PurchaseOrderController::class, 'send'])->name('purchase-orders.send');
    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
    Route::get('/purchase-orders/supplier/{supplier}/items', [PurchaseOrderController::class, 'supplierItems'])->name('purchase-orders.supplier-items');
    Route::get('/housekeeping', [HousekeepingController::class, 'index'])->name('housekeeping.index');
    Route::get('/housekeeping/create', [HousekeepingController::class, 'create'])->name('housekeeping.create');
    Route::post('/housekeeping', [HousekeepingController::class, 'store'])->name('housekeeping.store');
    Route::get('/housekeeping/{housekeeping}', [HousekeepingController::class, 'show'])->name('housekeeping.show');
    Route::get('/housekeeping/{housekeeping}/edit', [HousekeepingController::class, 'edit'])->name('housekeeping.edit');
    Route::put('/housekeeping/{housekeeping}', [HousekeepingController::class, 'update'])->name('housekeeping.update');
    Route::delete('/housekeeping/{housekeeping}', [HousekeepingController::class, 'destroy'])->name('housekeeping.destroy');
    Route::post('/housekeeping/{housekeeping}/assign', [HousekeepingController::class, 'assign'])->name('housekeeping.assign');
    Route::post('/housekeeping/{housekeeping}/start', [HousekeepingController::class, 'start'])->name('housekeeping.start');
    Route::post('/housekeeping/{housekeeping}/complete', [HousekeepingController::class, 'complete'])->name('housekeeping.complete');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
