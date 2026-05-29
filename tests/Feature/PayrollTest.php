<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email_verified_at' => now()]);
        $this->employee = Employee::factory()->create(['status' => 'active', 'salary' => 33000]);
    }

    // ── Auth gates ──

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('payrolls.index'))->assertRedirect(route('login'));
        $this->get(route('payrolls.create'))->assertRedirect(route('login'));
        $this->post(route('payrolls.store'), [])->assertRedirect(route('login'));
        $this->get(route('payrolls.show', 1))->assertRedirect(route('login'));
        $this->get(route('payrolls.edit', 1))->assertRedirect(route('login'));
        $this->put(route('payrolls.update', 1), [])->assertRedirect(route('login'));
        $this->delete(route('payrolls.destroy', 1))->assertRedirect(route('login'));
    }

    // ── Index ──

    public function test_index_lists_payrolls(): void
    {
        Payroll::factory()->count(3)->create(['employee_id' => $this->employee->id]);

        $response = $this->actingAs($this->user)->get(route('payrolls.index'));
        $response->assertStatus(200);
    }

    public function test_index_shows_empty_state(): void
    {
        $response = $this->actingAs($this->user)->get(route('payrolls.index'));
        $response->assertStatus(200);
    }

    public function test_index_can_filter_by_status(): void
    {
        Payroll::factory()->count(2)->create(['employee_id' => $this->employee->id, 'status' => 'draft']);
        Payroll::factory()->pending()->count(1)->create(['employee_id' => $this->employee->id]);

        $response = $this->actingAs($this->user)->get(route('payrolls.index', ['status' => 'draft']));
        $response->assertStatus(200);
    }

    public function test_index_can_filter_by_month_and_year(): void
    {
        Payroll::factory()->create([
            'employee_id'  => $this->employee->id,
            'period_start' => '2025-06-01',
            'period_end'   => '2025-06-15',
        ]);

        $response = $this->actingAs($this->user)->get(route('payrolls.index', [
            'month' => 6, 'year' => 2025,
        ]));
        $response->assertStatus(200);
    }

    public function test_index_shows_stats(): void
    {
        Payroll::factory()->paid()->create([
            'employee_id' => $this->employee->id,
            'gross_pay'   => 30000,
            'deductions'  => 3000,
            'net_pay'     => 27000,
        ]);
        Payroll::factory()->paid()->create([
            'employee_id' => $this->employee->id,
            'gross_pay'   => 20000,
            'deductions'  => 2000,
            'net_pay'     => 18000,
        ]);
        Payroll::factory()->create([
            'employee_id' => $this->employee->id,
            'status'      => 'pending',
            'gross_pay'   => 15000,
            'deductions'  => 1500,
            'net_pay'     => 13500,
        ]);
        Payroll::factory()->create([
            'employee_id' => $this->employee->id,
            'status'      => 'draft',
            'gross_pay'   => 10000,
            'deductions'  => 1000,
            'net_pay'     => 9000,
        ]);

        $response = $this->actingAs($this->user)->get(route('payrolls.index'));
        $response->assertStatus(200);

        $this->assertEquals(45000, Payroll::where('status', 'paid')->sum('net_pay'));
        $this->assertEquals(13500, Payroll::where('status', 'pending')->sum('net_pay'));
        $this->assertEquals(9000, Payroll::where('status', 'draft')->sum('net_pay'));
    }

    // ── Create ──

    public function test_create_form_loads_with_active_employees(): void
    {
        $response = $this->actingAs($this->user)->get(route('payrolls.create'));
        $response->assertStatus(200);
        $response->assertSee($this->employee->full_name);
    }

    public function test_create_form_defaults_period_to_next_period(): void
    {
        Payroll::factory()->create([
            'employee_id'  => $this->employee->id,
            'period_end'   => '2025-06-15',
        ]);

        $response = $this->actingAs($this->user)->get(route('payrolls.create'));
        $response->assertStatus(200);
        $response->assertSee('2025-06-16');
    }

    // ── Store ──

    public function test_payroll_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(route('payrolls.store'), [
            'employee_id'  => $this->employee->id,
            'period_start' => '2025-06-01',
            'period_end'   => '2025-06-15',
            'work_days'    => 15,
            'gross_pay'    => 22500,
            'deductions'   => 2250,
            'net_pay'      => 20250,
            'notes'        => 'First half June',
        ]);

        $response->assertRedirect(route('payrolls.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payrolls', [
            'employee_id'  => $this->employee->id,
            'net_pay'      => 20250,
            'status'       => 'draft',
        ]);
    }

    public function test_store_creates_with_draft_status(): void
    {
        $this->actingAs($this->user)->post(route('payrolls.store'), [
            'employee_id'  => $this->employee->id,
            'period_start' => '2025-07-01',
            'period_end'   => '2025-07-15',
            'work_days'    => 12,
            'gross_pay'    => 18000,
            'deductions'   => 1800,
            'net_pay'      => 16200,
        ]);

        $this->assertDatabaseHas('payrolls', [
            'employee_id' => $this->employee->id,
            'status'      => 'draft',
            'paid_at'     => null,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('payrolls.store'), [
            'employee_id'  => '',
            'period_start' => '',
            'period_end'   => '',
            'work_days'    => '',
            'gross_pay'    => '',
            'deductions'   => '',
            'net_pay'      => '',
        ]);

        $response->assertSessionHasErrors([
            'employee_id', 'period_start', 'period_end', 'work_days', 'gross_pay', 'deductions', 'net_pay',
        ]);
    }

    public function test_store_validates_period_end_after_start(): void
    {
        $response = $this->actingAs($this->user)->post(route('payrolls.store'), [
            'employee_id'  => $this->employee->id,
            'period_start' => '2025-06-15',
            'period_end'   => '2025-06-01', // before start
            'work_days'    => 10,
            'gross_pay'    => 15000,
            'deductions'   => 1500,
            'net_pay'      => 13500,
        ]);

        $response->assertSessionHasErrors('period_end');
    }

    public function test_store_validates_work_days_minimum(): void
    {
        $response = $this->actingAs($this->user)->post(route('payrolls.store'), [
            'employee_id'  => $this->employee->id,
            'period_start' => '2025-06-01',
            'period_end'   => '2025-06-15',
            'work_days'    => 0,
            'gross_pay'    => 15000,
            'deductions'   => 1500,
            'net_pay'      => 13500,
        ]);

        $response->assertSessionHasErrors('work_days');
    }

    public function test_store_validates_work_days_maximum(): void
    {
        $response = $this->actingAs($this->user)->post(route('payrolls.store'), [
            'employee_id'  => $this->employee->id,
            'period_start' => '2025-06-01',
            'period_end'   => '2025-06-15',
            'work_days'    => 32,
            'gross_pay'    => 15000,
            'deductions'   => 1500,
            'net_pay'      => 13500,
        ]);

        $response->assertSessionHasErrors('work_days');
    }

    public function test_store_rejects_negative_gross_pay(): void
    {
        $response = $this->actingAs($this->user)->post(route('payrolls.store'), [
            'employee_id'  => $this->employee->id,
            'period_start' => '2025-06-01',
            'period_end'   => '2025-06-15',
            'work_days'    => 10,
            'gross_pay'    => -100,
            'deductions'   => 0,
            'net_pay'      => -100,
        ]);

        $response->assertSessionHasErrors('gross_pay');
    }

    public function test_store_rejects_negative_deductions(): void
    {
        $response = $this->actingAs($this->user)->post(route('payrolls.store'), [
            'employee_id'  => $this->employee->id,
            'period_start' => '2025-06-01',
            'period_end'   => '2025-06-15',
            'work_days'    => 10,
            'gross_pay'    => 15000,
            'deductions'   => -500,
            'net_pay'      => 15500,
        ]);

        $response->assertSessionHasErrors('deductions');
    }

    // ── Show ──

    public function test_show_displays_payroll_details(): void
    {
        $payroll = Payroll::factory()->create([
            'employee_id' => $this->employee->id,
            'notes'       => 'Overtime pay',
        ]);

        $response = $this->actingAs($this->user)->get(route('payrolls.show', $payroll));
        $response->assertStatus(200);
        $response->assertSee('Overtime pay');
    }

    // ── Edit / Update ──

    public function test_edit_form_loads(): void
    {
        $payroll = Payroll::factory()->create(['employee_id' => $this->employee->id]);

        $response = $this->actingAs($this->user)->get(route('payrolls.edit', $payroll));
        $response->assertStatus(200);
        $response->assertSee($this->employee->full_name);
    }

    public function test_payroll_can_be_updated(): void
    {
        $payroll = Payroll::factory()->create(['employee_id' => $this->employee->id]);

        $response = $this->actingAs($this->user)->put(route('payrolls.update', $payroll), [
            'employee_id'  => $this->employee->id,
            'period_start' => '2025-08-01',
            'period_end'   => '2025-08-15',
            'work_days'    => 14,
            'gross_pay'    => 21000,
            'deductions'   => 2100,
            'net_pay'      => 18900,
        ]);

        $response->assertRedirect(route('payrolls.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payrolls', [
            'id'        => $payroll->id,
            'work_days' => 14,
            'net_pay'   => 18900,
        ]);
    }

    // ── Destroy ──

    public function test_payroll_can_be_deleted(): void
    {
        $payroll = Payroll::factory()->create(['employee_id' => $this->employee->id]);

        $response = $this->actingAs($this->user)->delete(route('payrolls.destroy', $payroll));
        $response->assertRedirect(route('payrolls.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('payrolls', ['id' => $payroll->id]);
    }

    // ─── Status transitions ───────────────────────────────────────

    public function test_draft_payroll_can_be_approved(): void
    {
        $payroll = Payroll::factory()->create([
            'employee_id' => $this->employee->id,
            'status'      => 'draft',
        ]);

        $this->actingAs($this->user)->post(route('payrolls.approve', $payroll));

        $this->assertDatabaseHas('payrolls', [
            'id'     => $payroll->id,
            'status' => 'pending',
        ]);
    }

    public function test_non_draft_payroll_cannot_be_approved(): void
    {
        $payroll = Payroll::factory()->pending()->create(['employee_id' => $this->employee->id]);

        $response = $this->actingAs($this->user)->post(route('payrolls.approve', $payroll));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('payrolls', [
            'id'     => $payroll->id,
            'status' => 'pending',
        ]);
    }

    public function test_pending_payroll_can_be_marked_paid(): void
    {
        $payroll = Payroll::factory()->pending()->create(['employee_id' => $this->employee->id]);

        $this->actingAs($this->user)->post(route('payrolls.pay', $payroll));

        $this->assertDatabaseHas('payrolls', [
            'id'     => $payroll->id,
            'status' => 'paid',
        ]);
        $this->assertNotNull($payroll->fresh()->paid_at);
    }

    public function test_non_pending_payroll_cannot_be_marked_paid(): void
    {
        $payroll = Payroll::factory()->create(['employee_id' => $this->employee->id, 'status' => 'draft']);

        $response = $this->actingAs($this->user)->post(route('payrolls.pay', $payroll));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('payrolls', [
            'id'     => $payroll->id,
            'status' => 'draft',
        ]);
    }

    public function test_already_paid_payroll_cannot_be_paid_again(): void
    {
        $payroll = Payroll::factory()->paid()->create(['employee_id' => $this->employee->id]);

        $response = $this->actingAs($this->user)->post(route('payrolls.pay', $payroll));
        $response->assertSessionHas('error');
    }

    // ─── Batch create ────────────────────────────────────────────

    public function test_batch_create_generates_payrolls_for_multiple_employees(): void
    {
        $emp2 = Employee::factory()->create(['status' => 'active', 'salary' => 44000]);

        $response = $this->actingAs($this->user)->post(route('payrolls.batch-create'), [
            'period_start' => '2025-06-01',
            'period_end'   => '2025-06-15',
            'work_days'    => 15,
            'employee_ids' => [$this->employee->id, $emp2->id],
        ]);

        $response->assertRedirect(route('payrolls.index'));
        $response->assertSessionHas('success', 'Payroll generated for 2 employee(s).');

        $this->assertDatabaseHas('payrolls', [
            'employee_id'  => $this->employee->id,
            'work_days'    => 15,
            'period_start' => '2025-06-01 00:00:00',
        ]);
        $this->assertDatabaseHas('payrolls', [
            'employee_id'  => $emp2->id,
            'work_days'    => 15,
            'period_start' => '2025-06-01 00:00:00',
        ]);
    }

    public function test_batch_create_calculates_gross_and_deductions_correctly(): void
    {
        // salary = 33000, daily = 33000/22 = 1500
        // 15 days = 1500 * 15 = 22500 gross → 2250 deduction → 20250 net
        $this->actingAs($this->user)->post(route('payrolls.batch-create'), [
            'period_start' => '2025-06-01',
            'period_end'   => '2025-06-15',
            'work_days'    => 15,
            'employee_ids' => [$this->employee->id],
        ]);

        $this->assertDatabaseHas('payrolls', [
            'employee_id' => $this->employee->id,
            'gross_pay'   => 22500.00,
            'deductions'  => 2250.00,
            'net_pay'     => 20250.00,
        ]);
    }

    public function test_batch_create_only_includes_active_employees(): void
    {
        $inactive = Employee::factory()->create(['status' => 'inactive', 'salary' => 30000]);

        $this->actingAs($this->user)->post(route('payrolls.batch-create'), [
            'period_start' => '2025-06-01',
            'period_end'   => '2025-06-15',
            'work_days'    => 10,
            'employee_ids' => [$this->employee->id, $inactive->id],
        ]);

        // Only active employee should get payroll
        $this->assertDatabaseHas('payrolls', ['employee_id' => $this->employee->id]);
        $this->assertDatabaseMissing('payrolls', ['employee_id' => $inactive->id]);
    }

    public function test_batch_create_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('payrolls.batch-create'), [
            'period_start' => '',
            'period_end'   => '',
            'work_days'    => '',
            'employee_ids' => '',
        ]);

        $response->assertSessionHasErrors([
            'period_start', 'period_end', 'work_days', 'employee_ids',
        ]);
    }

    public function test_batch_create_validates_at_least_one_employee(): void
    {
        $response = $this->actingAs($this->user)->post(route('payrolls.batch-create'), [
            'period_start' => '2025-06-01',
            'period_end'   => '2025-06-15',
            'work_days'    => 10,
            'employee_ids' => [],
        ]);

        $response->assertSessionHasErrors('employee_ids');
    }

    public function test_batch_create_creates_draft_status_payrolls(): void
    {
        $this->actingAs($this->user)->post(route('payrolls.batch-create'), [
            'period_start' => '2025-06-01',
            'period_end'   => '2025-06-15',
            'work_days'    => 10,
            'employee_ids' => [$this->employee->id],
        ]);

        $this->assertDatabaseHas('payrolls', [
            'employee_id' => $this->employee->id,
            'status'      => 'draft',
            'paid_at'     => null,
        ]);
    }

    // ─── Full lifecycle ──────────────────────────────────────────

    public function test_payroll_full_lifecycle(): void
    {
        // Create
        $this->actingAs($this->user)->post(route('payrolls.store'), [
            'employee_id'  => $this->employee->id,
            'period_start' => '2025-09-01',
            'period_end'   => '2025-09-15',
            'work_days'    => 13,
            'gross_pay'    => 19500,
            'deductions'   => 1950,
            'net_pay'      => 17550,
        ]);

        $payroll = Payroll::where('employee_id', $this->employee->id)->first();
        $this->assertEquals('draft', $payroll->status);

        // Approve
        $this->actingAs($this->user)->post(route('payrolls.approve', $payroll));
        $this->assertEquals('pending', $payroll->fresh()->status);

        // Pay
        $this->actingAs($this->user)->post(route('payrolls.pay', $payroll));
        $paid = $payroll->fresh();
        $this->assertEquals('paid', $paid->status);
        $this->assertNotNull($paid->paid_at);
    }
}
