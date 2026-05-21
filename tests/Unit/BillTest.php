<?php

namespace Tests\Unit;

use App\Models\Bill;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_scope_returns_only_pending_bills()
    {
        Bill::factory()->count(3)->create(['status' => 'Pending']);
        Bill::factory()->count(2)->create(['status' => 'Paid']);
        Bill::factory()->create(['status' => 'Overdue']);

        $pendingBills = Bill::pending()->get();

        $this->assertCount(3, $pendingBills);
        $pendingBills->each(fn($b) => $this->assertEquals('Pending', $b->status));
    }

    public function test_paid_scope_returns_only_paid_bills()
    {
        Bill::factory()->count(4)->create(['status' => 'Paid']);
        Bill::factory()->create(['status' => 'Pending']);

        $paidBills = Bill::paid()->get();

        $this->assertCount(4, $paidBills);
        $paidBills->each(fn($b) => $this->assertEquals('Paid', $b->status));
    }

    public function test_overdue_scope_returns_only_overdue_bills()
    {
        Bill::factory()->count(2)->create(['status' => 'Overdue']);
        Bill::factory()->count(3)->create(['status' => 'Pending']);

        $overdueBills = Bill::overdue()->get();

        $this->assertCount(2, $overdueBills);
        $overdueBills->each(fn($b) => $this->assertEquals('Overdue', $b->status));
    }

    public function test_bill_paid_amount_can_be_updated()
    {
        $bill = Bill::factory()->create(['amount' => 10000, 'status' => 'Pending']);
        $bill->status = 'Paid';
        $bill->save();
        $bill->refresh();

        $this->assertEquals('Paid', $bill->status);
    }

    public function test_bill_due_date_is_a_date()
    {
        $bill = Bill::factory()->create(['due_date' => now()->addDays(7)]);

        $this->assertInstanceOf(Carbon::class, $bill->due_date);
    }

    public function test_all_scopes_are_mutually_exclusive_for_same_bill()
    {
        Bill::factory()->create(['status' => 'Pending']);
        Bill::factory()->create(['status' => 'Paid']);
        Bill::factory()->create(['status' => 'Overdue']);

        $this->assertCount(0, Bill::pending()->paid()->get());
        $this->assertCount(0, Bill::pending()->overdue()->get());
        $this->assertCount(0, Bill::paid()->overdue()->get());
    }
}
