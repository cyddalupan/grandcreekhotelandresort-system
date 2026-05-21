<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Department;
use App\Models\Item;
use App\Models\Movement;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email_verified_at' => now()]);

        // Create default settings
        Setting::create([
            'hotel_name' => 'Grand Creek Hotel & Resort',
            'currency' => 'PHP',
            'low_stock_threshold' => 30,
            'bill_alert_days' => 7,
            'notifications' => [
                'low_stock' => true,
                'bill_due' => true,
                'overdue_bill' => true,
                'purchase_approval' => true,
            ],
        ]);
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_dashboard_loads_successfully_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertViewHasAll([
            'totalInventoryValue', 'monthlyExpenses', 'pendingBillsCount',
            'totalMovements', 'lowStockItems', 'overdueBills',
            'recentMovements', 'pendingBills', 'departmentSpending', 'settings',
        ]);
    }

    public function test_dashboard_shows_correct_total_inventory_value(): void
    {
        $department = Department::factory()->create();

        Item::factory()->create([
            'department_id' => $department->id,
            'current_stock' => 10,
            'purchase_cost' => 100.00,
        ]);
        Item::factory()->create([
            'department_id' => $department->id,
            'current_stock' => 5,
            'purchase_cost' => 200.00,
        ]);

        $expectedValue = (10 * 100.00) + (5 * 200.00); // 1000 + 1000 = 2000

        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertViewHas('totalInventoryValue', $expectedValue);
    }

    public function test_dashboard_shows_correct_monthly_expenses_from_paid_bills(): void
    {
        // Create paid bills totaling 30,000
        Bill::factory()->paid()->create(['amount' => 10000]);
        Bill::factory()->paid()->create(['amount' => 20000]);
        // Create a pending bill that should NOT be counted
        Bill::factory()->pending()->create(['amount' => 50000]);

        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertViewHas('monthlyExpenses', 30000.00);
    }

    public function test_dashboard_counts_pending_bills_correctly(): void
    {
        Bill::factory()->pending()->count(3)->create();
        Bill::factory()->paid()->count(2)->create();
        Bill::factory()->overdue()->count(1)->create();

        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertViewHas('pendingBillsCount', 3);
    }

    public function test_dashboard_counts_total_movements_correctly(): void
    {
        Movement::factory()->count(7)->create();

        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertViewHas('totalMovements', 7);
    }

    public function test_dashboard_lists_low_stock_items_correctly(): void
    {
        $department = Department::factory()->create();

        // Low stock item: current_stock (3) <= min_stock (10)
        $lowStockItem = Item::factory()->lowStock()->create([
            'department_id' => $department->id,
            'current_stock' => 3,
            'min_stock' => 10,
        ]);

        // Normal stock item
        Item::factory()->create([
            'department_id' => $department->id,
            'current_stock' => 50,
            'min_stock' => 10,
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');
        $lowStockItems = $response->viewData('lowStockItems');

        $this->assertCount(1, $lowStockItems);
        $this->assertEquals($lowStockItem->id, $lowStockItems->first()->id);
    }

    public function test_dashboard_lists_overdue_bills_correctly(): void
    {
        $overdueBill = Bill::factory()->overdue()->create();
        Bill::factory()->pending()->create();
        Bill::factory()->paid()->create();

        $response = $this->actingAs($this->user)->get('/dashboard');
        $overdueBills = $response->viewData('overdueBills');

        $this->assertCount(1, $overdueBills);
        $this->assertEquals($overdueBill->id, $overdueBills->first()->id);
    }

    public function test_dashboard_shows_max_five_recent_movements_ordered_by_date_desc(): void
    {
        // Create 7 movements spread across dates
        $movements = [];
        for ($i = 1; $i <= 7; $i++) {
            $movements[] = Movement::factory()->create([
                'date' => now()->subDays(7 - $i),
            ]);
        }

        $response = $this->actingAs($this->user)->get('/dashboard');
        $recentMovements = $response->viewData('recentMovements');

        // Should show only the 5 most recent
        $this->assertCount(5, $recentMovements);
        // Most recent should be first
        $this->assertEquals($movements[6]->id, $recentMovements[0]->id);
        $this->assertEquals($movements[5]->id, $recentMovements[1]->id);
        $this->assertEquals($movements[4]->id, $recentMovements[2]->id);
    }

    public function test_dashboard_shows_pending_bills_ordered_by_due_date(): void
    {
        $bill1 = Bill::factory()->pending()->create(['due_date' => '2026-06-15']);
        $bill2 = Bill::factory()->pending()->create(['due_date' => '2026-06-01']); // Earlier
        $bill3 = Bill::factory()->pending()->create(['due_date' => '2026-06-20']);

        $response = $this->actingAs($this->user)->get('/dashboard');
        $pendingBills = $response->viewData('pendingBills');

        $this->assertCount(3, $pendingBills);
        $this->assertEquals($bill2->id, $pendingBills[0]->id); // Earliest first
        $this->assertEquals($bill1->id, $pendingBills[1]->id);
        $this->assertEquals($bill3->id, $pendingBills[2]->id);
    }

    public function test_dashboard_calculates_department_spending_correctly(): void
    {
        $deptA = Department::factory()->create(['name' => 'Housekeeping']);
        $deptB = Department::factory()->create(['name' => 'Kitchen']);

        // Dept A items: 10 * 100 + 5 * 200 = 2000
        Item::factory()->create(['department_id' => $deptA->id, 'current_stock' => 10, 'purchase_cost' => 100]);
        Item::factory()->create(['department_id' => $deptA->id, 'current_stock' => 5, 'purchase_cost' => 200]);
        // Dept B: 20 * 50 = 1000
        Item::factory()->create(['department_id' => $deptB->id, 'current_stock' => 20, 'purchase_cost' => 50]);

        $response = $this->actingAs($this->user)->get('/dashboard');
        $deptSpending = $response->viewData('departmentSpending');

        $this->assertArrayHasKey('Housekeeping', $deptSpending);
        $this->assertArrayHasKey('Kitchen', $deptSpending);
        $this->assertEquals(2000, $deptSpending['Housekeeping']);
        $this->assertEquals(1000, $deptSpending['Kitchen']);
    }

    public function test_dashboard_handles_empty_data_gracefully(): void
    {
        // No items, no bills, no movements — just settings
        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertStatus(200);

        $response->assertViewHas('totalInventoryValue', 0);
        $response->assertViewHas('monthlyExpenses', 0);
        $response->assertViewHas('pendingBillsCount', 0);
        $response->assertViewHas('totalMovements', 0);
        $response->assertViewHas('departmentSpending', []);
    }

    public function test_dashboard_includes_all_status_bills_in_paid_count(): void
    {
        Bill::factory()->paid()->count(4)->create();
        Bill::factory()->pending()->count(3)->create();
        Bill::factory()->overdue()->count(2)->create();

        $response = $this->actingAs($this->user)->get('/dashboard');
        $paidCount = $response->viewData('paidBillsCount');

        $this->assertEquals(4, $paidCount);
    }
}
