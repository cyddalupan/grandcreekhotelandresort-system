<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Department;
use App\Models\Item;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email_verified_at' => now()]);
    }

    // ── Authentication gates ──

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $this->get(route('reports.index'))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_cannot_export_csv(): void
    {
        $this->get(route('reports.export-csv'))->assertRedirect(route('login'));
    }

    // ── Reports index page ──

    public function test_reports_index_loads_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $response->assertStatus(200);

        $response->assertViewHasAll([
            'totalInventoryValue', 'totalItems',
            'totalPaidBills', 'paidBillsCount',
            'pendingBillAmount', 'pendingBillsCount',
            'overdueBillAmount', 'overdueBillsCount',
            'totalSuppliers',
            'departmentBreakdown', 'lowStockItems',
            'settings',
        ]);
    }

    // ── Inventory calculations ──

    public function test_reports_calculates_correct_total_inventory_value(): void
    {
        $dept = Department::factory()->create();

        Item::factory()->create([
            'department_id' => $dept->id,
            'current_stock' => 10,
            'purchase_cost' => 150.00,
        ]);
        Item::factory()->create([
            'department_id' => $dept->id,
            'current_stock' => 20,
            'purchase_cost' => 75.50,
        ]);

        $expectedValue = (10 * 150.00) + (20 * 75.50); // 1500 + 1510 = 3010

        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $response->assertViewHas('totalInventoryValue', $expectedValue);
        $response->assertViewHas('totalItems', 2);
    }

    public function test_reports_inventory_value_is_zero_when_no_items(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $response->assertViewHas('totalInventoryValue', 0);
        $response->assertViewHas('totalItems', 0);
    }

    // ── Bill calculations ──

    public function test_reports_calculates_paid_bill_totals_correctly(): void
    {
        Bill::factory()->paid()->create(['amount' => 50000]);
        Bill::factory()->paid()->create(['amount' => 25000]);

        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $response->assertViewHas('totalPaidBills', 75000.00);
        $response->assertViewHas('paidBillsCount', 2);
    }

    public function test_reports_counts_pending_bills_correctly(): void
    {
        Bill::factory()->pending()->count(5)->create(['amount' => 1000]);
        Bill::factory()->paid()->count(3)->create(['amount' => 1000]);

        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $response->assertViewHas('pendingBillsCount', 5);
        $response->assertViewHas('pendingBillAmount', 5000.00);
    }

    public function test_reports_counts_overdue_bills_correctly(): void
    {
        Bill::factory()->overdue()->count(2)->create(['amount' => 15000]);
        Bill::factory()->pending()->create(['amount' => 5000]);
        Bill::factory()->paid()->create(['amount' => 10000]);

        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $response->assertViewHas('overdueBillsCount', 2);
        $response->assertViewHas('overdueBillAmount', 30000.00);
    }

    public function test_reports_bill_totals_are_zero_when_no_bills(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $response->assertViewHas('totalPaidBills', 0);
        $response->assertViewHas('paidBillsCount', 0);
        $response->assertViewHas('pendingBillAmount', 0);
        $response->assertViewHas('pendingBillsCount', 0);
        $response->assertViewHas('overdueBillAmount', 0);
        $response->assertViewHas('overdueBillsCount', 0);
    }

    // ── Supplier count ──

    public function test_reports_counts_suppliers_correctly(): void
    {
        Supplier::factory()->count(4)->create();

        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $response->assertViewHas('totalSuppliers', 4);
    }

    public function test_reports_supplier_count_is_zero_when_no_suppliers(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $response->assertViewHas('totalSuppliers', 0);
    }

    // ── Department breakdown ──

    public function test_reports_builds_department_breakdown_correctly(): void
    {
        $kitchen = Department::factory()->create(['name' => 'Kitchen']);
        $housekeeping = Department::factory()->create(['name' => 'Housekeeping']);

        Item::factory()->create([
            'department_id' => $kitchen->id,
            'current_stock' => 5,
            'purchase_cost' => 200.00,
        ]);
        Item::factory()->create([
            'department_id' => $housekeeping->id,
            'current_stock' => 10,
            'purchase_cost' => 50.00,
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $breakdown = $response->viewData('departmentBreakdown');

        $this->assertCount(2, $breakdown);

        $kitchenEntry = collect($breakdown)->firstWhere('name', 'Kitchen');
        $housekeepingEntry = collect($breakdown)->firstWhere('name', 'Housekeeping');

        $this->assertEquals(1000.00, $kitchenEntry['value']); // 5 × 200
        $this->assertEquals(500.00, $housekeepingEntry['value']); // 10 × 50
    }

    public function test_reports_department_breakdown_is_empty_when_no_items(): void
    {
        Department::factory()->create(['name' => 'Kitchen']); // has dept but no items

        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $breakdown = $response->viewData('departmentBreakdown');

        $this->assertCount(1, $breakdown);
        $this->assertEquals(0, $breakdown[0]['value']);
    }

    // ── Low stock filtering ──

    public function test_reports_identifies_low_stock_items(): void
    {
        $dept = Department::factory()->create();

        $lowItem = Item::factory()->create([
            'department_id' => $dept->id,
            'current_stock' => 3,
            'min_stock' => 10,
        ]);
        Item::factory()->create([
            'department_id' => $dept->id,
            'current_stock' => 50,
            'min_stock' => 10,
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $lowStockItems = $response->viewData('lowStockItems');

        $this->assertCount(1, $lowStockItems);
        $this->assertEquals($lowItem->id, $lowStockItems->first()->id);
    }

    public function test_reports_low_stock_is_empty_when_all_stock_ok(): void
    {
        $dept = Department::factory()->create();
        Item::factory()->count(3)->create([
            'department_id' => $dept->id,
            'current_stock' => 100,
            'min_stock' => 10,
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $this->assertCount(0, $response->viewData('lowStockItems'));
    }

    // ── Settings ──

    public function test_reports_loads_settings(): void
    {
        Setting::create([
            'hotel_name' => 'Grand Creek Hotel & Resort',
            'currency' => 'PHP',
            'low_stock_threshold' => 30,
            'bill_alert_days' => 7,
            'notifications' => ['low_stock' => true, 'bill_due' => true],
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $settings = $response->viewData('settings');

        $this->assertEquals('Grand Creek Hotel & Resort', $settings->hotel_name);
    }

    // ── CSV export ──



    public function test_reports_csv_export_contains_item_data(): void
    {
        $dept = Department::factory()->create(['name' => 'Kitchen']);
        Item::factory()->create([
            'name' => 'Test Item',
            'department_id' => $dept->id,
            'current_stock' => 10,
            'min_stock' => 5,
            'unit' => 'pieces',
            'purchase_cost' => 100.00,
            'selling_price' => 150.00,
            'category' => 'Food',
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.export-csv'));
        $content = $response->streamedContent();

        $this->assertStringContainsString('Test Item', $content);
        $this->assertStringContainsString('Kitchen', $content);
        $this->assertStringContainsString('Food', $content);
        $this->assertStringContainsString('10', $content);
        $this->assertStringContainsString('pieces', $content);
        $this->assertStringContainsString('100.00', $content);
        $this->assertStringContainsString('150.00', $content);
    }

    public function test_reports_csv_export_marks_low_stock_items(): void
    {
        $dept = Department::factory()->create();
        Item::factory()->create([
            'current_stock' => 3,
            'min_stock' => 10,
            'department_id' => $dept->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.export-csv'));
        $content = $response->streamedContent();

        $this->assertStringContainsString('Low Stock', $content);
    }

    public function test_reports_csv_export_marks_normal_stock_items(): void
    {
        $dept = Department::factory()->create();
        Item::factory()->create([
            'current_stock' => 50,
            'min_stock' => 10,
            'department_id' => $dept->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.export-csv'));
        $content = $response->streamedContent();

        $this->assertStringContainsString('OK', $content);
    }

    public function test_reports_csv_export_includes_summary_section(): void
    {
        $dept = Department::factory()->create();
        Item::factory()->count(3)->create([
            'department_id' => $dept->id,
            'current_stock' => 5,
            'purchase_cost' => 100,
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.export-csv'));
        $content = $response->streamedContent();

        $this->assertStringContainsString('=== Summary ===', $content);
        $this->assertStringContainsString('Total Items', $content);
        $this->assertStringContainsString('3', $content); // 3 items
        $this->assertStringContainsString('Low Stock Items', $content);
        $this->assertStringContainsString('Total Inventory Value', $content);
    }

    public function test_reports_csv_export_includes_header_row(): void
    {
        $dept = Department::factory()->create();

        $response = $this->actingAs($this->user)->get(route('reports.export-csv'));
        $content = $response->streamedContent();

        $this->assertStringContainsString('Item Name', $content);
        $this->assertStringContainsString('Category', $content);
        $this->assertStringContainsString('Department', $content);
        $this->assertStringContainsString('Current Stock', $content);
        $this->assertStringContainsString('Min Stock', $content);
        $this->assertStringContainsString('Unit', $content);
        $this->assertStringContainsString('Purchase Cost', $content);
        $this->assertStringContainsString('Selling Price', $content);
        $this->assertStringContainsString('Stock Value', $content);
        $this->assertStringContainsString('Status', $content);
    }

    public function test_reports_csv_export_handles_empty_inventory(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.export-csv'));
        $content = $response->streamedContent();

        // Should still have headers and summary
        $this->assertStringContainsString('Item Name', $content);
        $this->assertStringContainsString('=== Summary ===', $content);
        $this->assertStringContainsString('0', $content); // 0 items
    }
}
