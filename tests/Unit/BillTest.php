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

    /*
     * Scope tests — business grouping of bills by payment status
     */

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

    public function test_all_scopes_are_mutually_exclusive_for_same_bill()
    {
        Bill::factory()->create(['status' => 'Pending']);
        Bill::factory()->create(['status' => 'Paid']);
        Bill::factory()->create(['status' => 'Overdue']);

        $this->assertCount(0, Bill::pending()->paid()->get());
        $this->assertCount(0, Bill::pending()->overdue()->get());
        $this->assertCount(0, Bill::paid()->overdue()->get());
    }

    /*
     * Business logic: payment tracking
     */

    public function test_bill_status_transitions_from_pending_to_paid()
    {
        $bill = Bill::factory()->create(['amount' => 15000, 'status' => 'Pending']);

        $bill->status = 'Paid';
        $bill->payment_date = now();
        $bill->payment_method = 'Bank Transfer';
        $bill->payment_reference = 'REF-20260524-ABC';
        $bill->save();
        $bill->refresh();

        $this->assertEquals('Paid', $bill->status);
        $this->assertEquals('Bank Transfer', $bill->payment_method);
        $this->assertEquals('REF-20260524-ABC', $bill->payment_reference);
        $this->assertInstanceOf(Carbon::class, $bill->payment_date);
    }

    public function test_bill_status_transitions_from_pending_to_overdue()
    {
        $bill = Bill::factory()->create([
            'status' => 'Pending',
            'due_date' => now()->subDays(5),
        ]);

        $bill->status = 'Overdue';
        $bill->save();
        $bill->refresh();

        $this->assertEquals('Overdue', $bill->status);
    }

    /*
     * Business logic: bill has all required billing metadata
     */

    public function test_bill_has_type_and_provider()
    {
        $bill = Bill::factory()->create([
            'type' => 'Electricity',
            'provider' => 'MERALCO',
            'account_number' => '1234567890',
        ]);

        $this->assertEquals('Electricity', $bill->type);
        $this->assertEquals('MERALCO', $bill->provider);
        $this->assertEquals('1234567890', $bill->account_number);
    }

    public function test_bill_has_billing_period()
    {
        $bill = Bill::factory()->create([
            'billing_period' => 'May 2026',
        ]);

        $this->assertEquals('May 2026', $bill->billing_period);
    }

    public function test_bill_amount_is_positive()
    {
        $bill = Bill::factory()->create(['amount' => 5000]);

        $this->assertGreaterThan(0, $bill->amount);
    }

    public function test_overdue_bill_has_past_due_date()
    {
        $bill = Bill::factory()->overdue()->create();

        $this->assertEquals('Overdue', $bill->status);
        $this->assertTrue(
            Carbon::parse($bill->due_date)->isPast(),
            'Overdue bill should have a past due date'
        );
    }

    /*
     * Business logic: bill types the hotel commonly deals with
     */

    public function test_bill_types_include_hotel_business_essentials()
    {
        $types = ['Electricity', 'Water', 'Internet', 'Telephone', 'Gas', 'Maintenance', 'Insurance', 'Rent'];

        $bill = Bill::factory()->create();

        $this->assertContains($bill->type, $types);
    }

    /*
     * Date handling
     */

    public function test_bill_due_date_is_a_date()
    {
        $bill = Bill::factory()->create(['due_date' => now()->addDays(7)]);

        $this->assertInstanceOf(Carbon::class, $bill->due_date);
    }

    public function test_bill_payment_date_is_null_when_unpaid()
    {
        $bill = Bill::factory()->pending()->create();

        $this->assertNull($bill->payment_date);
        $this->assertNull($bill->payment_method);
        $this->assertNull($bill->payment_reference);
    }
}

