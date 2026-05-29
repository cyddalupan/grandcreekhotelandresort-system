<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email_verified_at' => now()]);
    }

    // ── Auth gates ──

    public function test_unauthenticated_users_cannot_view_bills()
    {
        $this->get(route('bills.index'))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_cannot_create()
    {
        $this->get(route('bills.create'))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_cannot_store()
    {
        $this->post(route('bills.store'), [
            'type' => 'Electricity',
            'provider' => 'Meralco',
            'amount' => 5000,
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'Pending',
        ])->assertRedirect(route('login'));
    }

    // ── Index ──

    public function test_index_displays_bills()
    {
        $this->actingAs($this->user);

        Bill::factory()->count(5)->create();

        $response = $this->get(route('bills.index'));

        $response->assertOk();
        $response->assertViewHas('bills');
        $this->assertCount(5, $response->viewData('bills'));
    }

    public function test_index_shows_pending_count()
    {
        $this->actingAs($this->user);

        Bill::factory()->count(3)->create(['status' => 'Pending']);
        Bill::factory()->count(2)->create(['status' => 'Paid']);

        $response = $this->get(route('bills.index'));

        $response->assertViewHas('pendingCount', 3);
        $response->assertViewHas('paidCount', 2);
    }

    public function test_index_filters_by_status()
    {
        $this->actingAs($this->user);

        Bill::factory()->create(['status' => 'Pending', 'type' => 'Water']);
        Bill::factory()->create(['status' => 'Paid', 'type' => 'Electricity']);

        $response = $this->get(route('bills.index', ['status' => 'Pending']));

        $this->assertCount(1, $response->viewData('bills'));
        $this->assertEquals('Pending', $response->viewData('bills')->first()->status);
    }

    public function test_index_searches_by_type_provider_or_account()
    {
        $this->actingAs($this->user);

        Bill::factory()->create(['type' => 'Electricity', 'provider' => 'Meralco']);
        Bill::factory()->create(['type' => 'Water', 'provider' => 'Maynilad']);

        $response = $this->get(route('bills.index', ['search' => 'Meralco']));

        $this->assertCount(1, $response->viewData('bills'));
        $this->assertEquals('Electricity', $response->viewData('bills')->first()->type);
    }

    // ── Create form ──

    public function test_create_form_loads()
    {
        $this->actingAs($this->user);

        $this->get(route('bills.create'))->assertOk();
    }

    // ── Store ──

    public function test_store_creates_bill()
    {
        $this->actingAs($this->user);

        $this->post(route('bills.store'), [
            'type' => 'Electricity',
            'provider' => 'Meralco',
            'amount' => 5000.50,
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'Pending',
            'billing_period' => 'May 2026',
            'notes' => 'Monthly bill',
        ])->assertRedirect(route('bills.index'));

        $this->assertDatabaseHas('bills', [
            'type' => 'Electricity',
            'provider' => 'Meralco',
            'amount' => 5000.50,
        ]);
    }

    public function test_store_requires_type()
    {
        $this->actingAs($this->user);

        $this->post(route('bills.store'), [
            'provider' => 'Meralco',
            'amount' => 5000,
            'due_date' => now()->format('Y-m-d'),
            'status' => 'Pending',
        ])->assertSessionHasErrors('type');
    }

    public function test_store_requires_provider()
    {
        $this->actingAs($this->user);

        $this->post(route('bills.store'), [
            'type' => 'Electricity',
            'amount' => 5000,
            'due_date' => now()->format('Y-m-d'),
            'status' => 'Pending',
        ])->assertSessionHasErrors('provider');
    }

    public function test_store_requires_amount()
    {
        $this->actingAs($this->user);

        $this->post(route('bills.store'), [
            'type' => 'Electricity',
            'provider' => 'Meralco',
            'due_date' => now()->format('Y-m-d'),
            'status' => 'Pending',
        ])->assertSessionHasErrors('amount');
    }

    public function test_store_rejects_negative_amount()
    {
        $this->actingAs($this->user);

        $this->post(route('bills.store'), [
            'type' => 'Electricity',
            'provider' => 'Meralco',
            'amount' => -100,
            'due_date' => now()->format('Y-m-d'),
            'status' => 'Pending',
        ])->assertSessionHasErrors('amount');
    }

    public function test_store_requires_due_date()
    {
        $this->actingAs($this->user);

        $this->post(route('bills.store'), [
            'type' => 'Electricity',
            'provider' => 'Meralco',
            'amount' => 5000,
            'status' => 'Pending',
        ])->assertSessionHasErrors('due_date');
    }

    public function test_store_requires_valid_status()
    {
        $this->actingAs($this->user);

        $this->post(route('bills.store'), [
            'type' => 'Electricity',
            'provider' => 'Meralco',
            'amount' => 5000,
            'due_date' => now()->format('Y-m-d'),
            'status' => 'InvalidStatus',
        ])->assertSessionHasErrors('status');
    }

    // ── Edit ──

    public function test_edit_form_loads()
    {
        $this->actingAs($this->user);

        $bill = Bill::factory()->create();

        $this->get(route('bills.edit', $bill))->assertOk();
    }

    public function test_edit_returns_404_for_nonexistent_bill()
    {
        $this->actingAs($this->user);

        $this->get('/bills/99999/edit')->assertNotFound();
    }

    // ── Update ──

    public function test_update_modifies_bill()
    {
        $this->actingAs($this->user);

        $bill = Bill::factory()->create(['type' => 'Water', 'amount' => 1000]);

        $this->put(route('bills.update', $bill), [
            'type' => 'Electricity',
            'provider' => $bill->provider,
            'amount' => 5000,
            'due_date' => $bill->due_date->format('Y-m-d'),
            'status' => 'Pending',
        ])->assertRedirect(route('bills.index'));

        $this->assertDatabaseHas('bills', [
            'id' => $bill->id,
            'type' => 'Electricity',
            'amount' => 5000,
        ]);
    }

    // ── Pay ──

    public function test_pay_marks_bill_as_paid()
    {
        $this->actingAs($this->user);

        $bill = Bill::factory()->create(['status' => 'Pending']);

        $this->post(route('bills.pay', $bill), [
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'Bank Transfer',
            'payment_reference' => 'REF123',
        ])->assertRedirect(route('bills.index'));

        $this->assertDatabaseHas('bills', [
            'id' => $bill->id,
            'status' => 'Paid',
            'payment_method' => 'Bank Transfer',
            'payment_reference' => 'REF123',
        ]);
    }

    // ── Destroy ──

    public function test_destroy_deletes_bill()
    {
        $this->actingAs($this->user);

        $bill = Bill::factory()->create();

        $this->delete(route('bills.destroy', $bill))
            ->assertRedirect(route('bills.index'));

        $this->assertDatabaseMissing('bills', ['id' => $bill->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_bill()
    {
        $this->actingAs($this->user);

        $this->delete('/bills/99999')->assertNotFound();
    }

    public function test_destroy_does_not_affect_other_bills()
    {
        $this->actingAs($this->user);

        $bill1 = Bill::factory()->create();
        $bill2 = Bill::factory()->create();
        $bill3 = Bill::factory()->create();

        $this->delete(route('bills.destroy', $bill2));

        $this->assertDatabaseHas('bills', ['id' => $bill1->id]);
        $this->assertDatabaseMissing('bills', ['id' => $bill2->id]);
        $this->assertDatabaseHas('bills', ['id' => $bill3->id]);
    }

    // ── Edge cases ──

    public function test_store_accepts_optional_fields()
    {
        $this->actingAs($this->user);

        $this->post(route('bills.store'), [
            'type' => 'Internet',
            'provider' => 'PLDT',
            'account_number' => 'ACC-001',
            'amount' => 2500,
            'due_date' => now()->format('Y-m-d'),
            'status' => 'Pending',
            'billing_period' => 'June 2026',
            'payment_date' => null,
            'payment_method' => null,
            'payment_reference' => null,
            'notes' => 'Fiber plan',
        ])->assertRedirect(route('bills.index'));

        $this->assertDatabaseHas('bills', [
            'type' => 'Internet',
            'account_number' => 'ACC-001',
            'billing_period' => 'June 2026',
            'notes' => 'Fiber plan',
        ]);
    }

    public function test_can_delete_paid_bill()
    {
        $this->actingAs($this->user);

        $bill = Bill::factory()->create(['status' => 'Paid']);

        $this->delete(route('bills.destroy', $bill))
            ->assertRedirect(route('bills.index'));

        $this->assertDatabaseMissing('bills', ['id' => $bill->id]);
    }
}
