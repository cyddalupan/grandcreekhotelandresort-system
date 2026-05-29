<?php

namespace Tests\Unit;

use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    // ── Factory / Creation ──

    public function test_sale_can_be_created_via_factory(): void
    {
        $sale = Sale::factory()->create();

        $this->assertInstanceOf(Sale::class, $sale);
        $this->assertNotNull($sale->receipt_number);
        $this->assertNotNull($sale->subtotal);
    }

    public function test_sale_belongs_to_user(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $sale = Sale::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $sale->user);
        $this->assertEquals($user->id, $sale->user->id);
    }

    // ── Receipt Number ──

    public function test_receipt_number_format(): void
    {
        $sale = Sale::factory()->create();

        $this->assertMatchesRegularExpression('/^POS-\d{6}-\d{4}$/', $sale->receipt_number);
    }

    public function test_receipt_number_auto_increments(): void
    {
        $first = Sale::factory()->create();
        $second = Sale::factory()->create();

        $firstNum = (int) explode('-', $first->receipt_number)[2];
        $secondNum = (int) explode('-', $second->receipt_number)[2];

        $this->assertEquals(1, $firstNum);
        $this->assertEquals(2, $secondNum);
    }

    public function test_receipt_number_resets_daily(): void
    {
        $first = Sale::factory()->create();
        $firstNum = (int) explode('-', $first->receipt_number)[2];

        // Simulate next day
        Carbon::setTestNow(Carbon::tomorrow());
        $second = Sale::factory()->create();
        $secondNum = (int) explode('-', $second->receipt_number)[2];

        $this->assertEquals(1, $secondNum);

        Carbon::setTestNow(); // reset
    }

    // ── Casts ──

    public function test_items_is_cast_to_array(): void
    {
        $sale = Sale::factory()->create();

        $this->assertIsArray($sale->items);
        $this->assertNotEmpty($sale->items);
    }

    public function test_items_contains_expected_keys(): void
    {
        $sale = Sale::factory()->create();

        $item = $sale->items[0];
        $this->assertArrayHasKey('item_id', $item);
        $this->assertArrayHasKey('name', $item);
        $this->assertArrayHasKey('quantity', $item);
        $this->assertArrayHasKey('price', $item);
        $this->assertArrayHasKey('total', $item);
    }

    public function test_amount_fields_are_decimal_casted(): void
    {
        $sale = Sale::factory()->create([
            'subtotal'        => 1234.56,
            'tax_percent'     => 12.00,
            'tax_amount'      => 148.15,
            'discount'        => 50.25,
            'total'           => 1332.46,
            'tendered_amount' => 1500.00,
            'change'          => 167.54,
        ]);

        $this->assertEquals(1234.56, (float) $sale->subtotal);
        $this->assertEquals(12.00, (float) $sale->tax_percent);
        $this->assertEquals(148.15, (float) $sale->tax_amount);
        $this->assertEquals(50.25, (float) $sale->discount);
        $this->assertEquals(1332.46, (float) $sale->total);
        $this->assertEquals(1500.00, (float) $sale->tendered_amount);
        $this->assertEquals(167.54, (float) $sale->change);
    }

    // ── Nullable fields ──

    public function test_notes_is_nullable(): void
    {
        $sale = Sale::factory()->withoutNotes()->create();

        $this->assertNull($sale->notes);
    }

    // ─── Factory states ──

    public function test_cash_payment_state(): void
    {
        $sale = Sale::factory()->cashPayment()->create();

        $this->assertEquals('cash', $sale->payment_method);
    }

    public function test_gcash_payment_state(): void
    {
        $sale = Sale::factory()->gcashPayment()->create();

        $this->assertEquals('gcash', $sale->payment_method);
    }

    // ── Financial consistency ──

    public function test_change_is_non_negative(): void
    {
        $sale = Sale::factory()->create();

        $this->assertGreaterThanOrEqual(0, (float) $sale->change);
    }

    public function test_total_equals_subtotal_plus_tax_minus_discount(): void
    {
        $sale = Sale::factory()->create([
            'subtotal'   => 1000.00,
            'tax_percent'=> 12.00,
            'tax_amount' => 120.00,
            'discount'   => 50.00,
            'total'      => 1070.00,
        ]);

        $this->assertEquals(1070.00, (float) $sale->total);
    }
}
