<?php

namespace Tests\Unit;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    // ── Factory / Creation ──

    public function test_purchase_order_can_be_created_via_factory(): void
    {
        $po = PurchaseOrder::factory()->create();

        $this->assertInstanceOf(PurchaseOrder::class, $po);
        $this->assertNotNull($po->po_number);
        $this->assertNotNull($po->total_amount);
    }

    // ── PO Number ──

    public function test_po_number_format(): void
    {
        $po = PurchaseOrder::factory()->create();

        $this->assertMatchesRegularExpression('/^PO-\d{6}-\d{3}$/', $po->po_number);
    }

    public function test_generate_po_number_creates_correct_format(): void
    {
        $poNumber = PurchaseOrder::generatePoNumber();

        $this->assertMatchesRegularExpression('/^PO-\d{6}-\d{3}$/', $poNumber);
    }

    public function test_generate_po_number_increments_sequentially(): void
    {
        $supplier = Supplier::factory()->create();
        $user = User::factory()->create();

        $first = PurchaseOrder::create([
            'po_number'    => PurchaseOrder::generatePoNumber(),
            'supplier_id'  => $supplier->id,
            'items'        => [['name' => 'Test', 'qty' => 1, 'unit' => 'pcs', 'unit_price' => 100, 'total' => 100]],
            'total_amount' => 100,
            'status'       => 'draft',
            'created_by'   => $user->id,
        ]);

        $second = PurchaseOrder::create([
            'po_number'    => PurchaseOrder::generatePoNumber(),
            'supplier_id'  => $supplier->id,
            'items'        => [['name' => 'Test2', 'qty' => 2, 'unit' => 'pcs', 'unit_price' => 50, 'total' => 100]],
            'total_amount' => 100,
            'status'       => 'draft',
            'created_by'   => $user->id,
        ]);

        $firstSeq = (int) substr($first->po_number, -3);
        $secondSeq = (int) substr($second->po_number, -3);

        $this->assertEquals($firstSeq + 1, $secondSeq);
    }

    // ── Relationships ──

    public function test_purchase_order_belongs_to_supplier(): void
    {
        $supplier = Supplier::factory()->create();
        $po = PurchaseOrder::factory()->create(['supplier_id' => $supplier->id]);

        $this->assertInstanceOf(Supplier::class, $po->supplier);
        $this->assertEquals($supplier->id, $po->supplier->id);
    }

    public function test_purchase_order_has_creator(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $po = PurchaseOrder::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $po->creator);
        $this->assertEquals($user->id, $po->creator->id);
    }

    public function test_purchase_order_has_approver(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $po = PurchaseOrder::factory()->create(['approved_by' => $user->id]);

        $this->assertInstanceOf(User::class, $po->approver);
        $this->assertEquals($user->id, $po->approver->id);
    }

    // ── Casts ──

    public function test_items_is_cast_to_array(): void
    {
        $po = PurchaseOrder::factory()->create();

        $this->assertIsArray($po->items);
        $this->assertNotEmpty($po->items);
    }

    public function test_items_contain_expected_keys(): void
    {
        $po = PurchaseOrder::factory()->create();

        $item = $po->items[0];
        $this->assertArrayHasKey('name', $item);
        $this->assertArrayHasKey('qty', $item);
        $this->assertArrayHasKey('unit', $item);
        $this->assertArrayHasKey('unit_price', $item);
        $this->assertArrayHasKey('total', $item);
    }

    public function test_total_amount_is_decimal_casted(): void
    {
        $po = PurchaseOrder::factory()->create(['total_amount' => 12345.67]);

        $this->assertEquals(12345.67, (float) $po->total_amount);
    }

    public function test_approved_at_is_datetime_casted(): void
    {
        $date = now();
        $po = PurchaseOrder::factory()->create(['approved_at' => $date]);

        $this->assertInstanceOf(Carbon::class, $po->approved_at);
    }

    public function test_received_at_is_datetime_casted(): void
    {
        $date = now();
        $po = PurchaseOrder::factory()->create(['received_at' => $date]);

        $this->assertInstanceOf(Carbon::class, $po->received_at);
    }

    // ── Nullable fields ──

    public function test_notes_is_nullable(): void
    {
        $po = PurchaseOrder::factory()->create(['notes' => null]);

        $this->assertNull($po->notes);
    }

    public function test_approved_at_is_nullable(): void
    {
        $po = PurchaseOrder::factory()->create(['approved_at' => null, 'approved_by' => null]);

        $this->assertNull($po->approved_at);
        $this->assertNull($po->approver);
    }

    public function test_received_at_is_nullable(): void
    {
        $po = PurchaseOrder::factory()->create(['received_at' => null]);

        $this->assertNull($po->received_at);
    }

    // ── Status helpers (keep existing tests and add edge cases) ──

    public function test_can_approve_when_status_is_draft(): void
    {
        $po = PurchaseOrder::factory()->create(['status' => 'draft']);
        $this->assertTrue($po->canApprove());
    }

    public function test_cannot_approve_when_status_is_not_draft(): void
    {
        foreach (['approved', 'sent', 'partially_received', 'received', 'cancelled'] as $status) {
            $po = PurchaseOrder::factory()->create(['status' => $status]);
            $this->assertFalse($po->canApprove(), "Should not be approvable when status is $status");
        }
    }

    public function test_can_send_when_status_is_approved(): void
    {
        $po = PurchaseOrder::factory()->create(['status' => 'approved']);
        $this->assertTrue($po->canSend());
    }

    public function test_cannot_send_when_status_is_not_approved(): void
    {
        foreach (['draft', 'sent', 'partially_received', 'received', 'cancelled'] as $status) {
            $po = PurchaseOrder::factory()->create(['status' => $status]);
            $this->assertFalse($po->canSend(), "Should not be sendable when status is $status");
        }
    }

    public function test_can_receive_when_status_is_sent_or_partially_received(): void
    {
        $po = PurchaseOrder::factory()->create(['status' => 'sent']);
        $this->assertTrue($po->canReceive());

        $po = PurchaseOrder::factory()->create(['status' => 'partially_received']);
        $this->assertTrue($po->canReceive());
    }

    public function test_cannot_receive_when_status_is_not_sent_or_partially_received(): void
    {
        foreach (['draft', 'approved', 'received', 'cancelled'] as $status) {
            $po = PurchaseOrder::factory()->create(['status' => $status]);
            $this->assertFalse($po->canReceive(), "Should not be receivable when status is $status");
        }
    }

    public function test_can_cancel_when_not_received_or_cancelled(): void
    {
        foreach (['draft', 'approved', 'sent', 'partially_received'] as $status) {
            $po = PurchaseOrder::factory()->create(['status' => $status]);
            $this->assertTrue($po->canCancel(), "Should be cancellable when status is $status");
        }
    }

    public function test_cannot_cancel_when_received_or_cancelled(): void
    {
        foreach (['received', 'cancelled'] as $status) {
            $po = PurchaseOrder::factory()->create(['status' => $status]);
            $this->assertFalse($po->canCancel(), "Should not be cancellable when status is $status");
        }
    }

    // ── Status transitions ──

    public function test_draft_to_approved(): void
    {
        $po = PurchaseOrder::factory()->create(['status' => 'draft']);
        $this->assertTrue($po->canApprove());
        $po->status = 'approved';
        $po->save();
        $po->refresh();
        $this->assertEquals('approved', $po->status);
        $this->assertFalse($po->canApprove());
    }

    public function test_approved_to_sent(): void
    {
        $po = PurchaseOrder::factory()->create(['status' => 'approved']);
        $po->status = 'sent';
        $po->save();
        $po->refresh();
        $this->assertEquals('sent', $po->status);
        $this->assertTrue($po->canReceive());
    }

    public function test_sent_to_received(): void
    {
        $po = PurchaseOrder::factory()->create(['status' => 'sent']);
        $po->status = 'received';
        $po->save();
        $po->refresh();
        $this->assertEquals('received', $po->status);
        $this->assertFalse($po->canReceive());
        $this->assertFalse($po->canCancel());
    }

    public function test_any_to_cancelled(): void
    {
        foreach (['draft', 'approved', 'sent', 'partially_received'] as $status) {
            $po = PurchaseOrder::factory()->create(['status' => $status]);
            $po->status = 'cancelled';
            $po->save();
            $po->refresh();
            $this->assertEquals('cancelled', $po->status);
            $this->assertFalse($po->canCancel());
        }
    }

    public function test_all_statuses_are_valid(): void
    {
        $validStatuses = ['draft', 'approved', 'sent', 'partially_received', 'received', 'cancelled'];

        foreach ($validStatuses as $status) {
            $po = PurchaseOrder::factory()->create(['status' => $status]);
            $this->assertEquals($status, $po->status);
        }
    }

    // ── Financial ──

    public function test_total_amount_is_positive(): void
    {
        $po = PurchaseOrder::factory()->create(['total_amount' => 15000]);
        $this->assertGreaterThan(0, $po->total_amount);
    }

    public function test_total_amount_can_be_zero(): void
    {
        $po = PurchaseOrder::factory()->create(['total_amount' => 0.00]);
        $this->assertEquals(0.00, (float) $po->total_amount);
    }
}
