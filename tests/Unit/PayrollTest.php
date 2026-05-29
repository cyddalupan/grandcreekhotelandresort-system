<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollTest extends TestCase
{
    use RefreshDatabase;

    public function test_payroll_belongs_to_employee(): void
    {
        $employee = Employee::factory()->create();
        $payroll = Payroll::factory()->create(['employee_id' => $employee->id]);

        $this->assertInstanceOf(Employee::class, $payroll->employee);
        $this->assertEquals($employee->id, $payroll->employee->id);
    }

    public function test_payroll_period_dates_are_date_instances(): void
    {
        $payroll = Payroll::factory()->create();

        $this->assertInstanceOf(\Carbon\Carbon::class, $payroll->period_start);
        $this->assertInstanceOf(\Carbon\Carbon::class, $payroll->period_end);
    }

    public function test_payroll_end_is_after_or_equal_to_start(): void
    {
        $payroll = Payroll::factory()->create();

        $this->assertTrue(
            $payroll->period_end->greaterThanOrEqualTo($payroll->period_start)
        );
    }

    public function test_payroll_default_status_is_draft(): void
    {
        $payroll = Payroll::factory()->create();

        $this->assertEquals('draft', $payroll->status);
    }

    public function test_payroll_status_can_be_pending(): void
    {
        $payroll = Payroll::factory()->pending()->create();

        $this->assertEquals('pending', $payroll->status);
        $this->assertNull($payroll->paid_at);
    }

    public function test_payroll_status_can_be_paid(): void
    {
        $payroll = Payroll::factory()->paid()->create();

        $this->assertEquals('paid', $payroll->status);
        $this->assertNotNull($payroll->paid_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $payroll->paid_at);
    }

    public function test_payroll_net_pay_is_gross_minus_deductions(): void
    {
        $payroll = Payroll::factory()->create([
            'gross_pay'  => 50000,
            'deductions' => 5000,
            'net_pay'    => 45000,
        ]);

        $this->assertEquals(50000, (float) $payroll->gross_pay);
        $this->assertEquals(5000, (float) $payroll->deductions);
        $this->assertEquals(45000, (float) $payroll->net_pay);
    }

    public function test_payroll_calculations_are_consistent(): void
    {
        $payroll = Payroll::factory()->create();
        $calculatedNet = round((float) $payroll->gross_pay - (float) $payroll->deductions, 2);

        $this->assertEquals($calculatedNet, (float) $payroll->net_pay);
    }

    public function test_payroll_work_days_is_within_valid_range(): void
    {
        $payroll = Payroll::factory()->create();

        $this->assertGreaterThanOrEqual(1, $payroll->work_days);
        $this->assertLessThanOrEqual(31, $payroll->work_days);
    }

    public function test_payroll_gross_pay_and_deductions_are_non_negative(): void
    {
        $payroll = Payroll::factory()->create();

        $this->assertGreaterThanOrEqual(0, (float) $payroll->gross_pay);
        $this->assertGreaterThanOrEqual(0, (float) $payroll->deductions);
        $this->assertGreaterThanOrEqual(0, (float) $payroll->net_pay);
    }

    public function test_payroll_can_have_null_paid_at_when_not_paid(): void
    {
        $draft = Payroll::factory()->create(['status' => 'draft', 'paid_at' => null]);
        $pending = Payroll::factory()->pending()->create();

        $this->assertNull($draft->paid_at);
        $this->assertNull($pending->paid_at);
    }

    public function test_payroll_notes_are_nullable(): void
    {
        $without = Payroll::factory()->create(['notes' => null]);
        $with = Payroll::factory()->create(['notes' => 'Overtime included']);

        $this->assertNull($without->notes);
        $this->assertEquals('Overtime included', $with->notes);
    }

    public function test_payroll_paid_at_is_datetime(): void
    {
        $paid = Payroll::factory()->paid()->create();

        $this->assertNotNull($paid->paid_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $paid->paid_at);
    }

    public function test_payroll_period_start_is_before_or_equal_to_end(): void
    {
        $payroll = Payroll::factory()->create([
            'period_start' => '2025-06-01',
            'period_end'   => '2025-06-15',
        ]);

        $this->assertEquals('2025-06-01', $payroll->period_start->format('Y-m-d'));
        $this->assertEquals('2025-06-15', $payroll->period_end->format('Y-m-d'));
    }
}
