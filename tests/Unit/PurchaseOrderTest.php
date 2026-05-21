<?php

namespace Tests\Unit;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_approve_when_status_is_draft()
    {
        $po = PurchaseOrder::factory()->create(['status' => 'draft']);
        $this->assertTrue($po->canApprove());
    }

    public function test_cannot_approve_when_status_is_not_draft()
    {
        foreach (['approved', 'sent', 'partially_received', 'received', 'cancelled'] as $status) {
            $po = PurchaseOrder::factory()->create(['status' => $status]);
            $this->assertFalse($po->canApprove(), "Should not be approvable when status is $status");
        }
    }

    public function test_can_send_when_status_is_approved()
    {
        $po = PurchaseOrder::factory()->create(['status' => 'approved']);
        $this->assertTrue($po->canSend());
    }

    public function test_cannot_send_when_status_is_not_approved()
    {
        foreach (['draft', 'sent', 'partially_received', 'received', 'cancelled'] as $status) {
            $po = PurchaseOrder::factory()->create(['status' => $status]);
            $this->assertFalse($po->canSend(), "Should not be sendable when status is $status");
        }
    }

    public function test_can_receive_when_status_is_sent_or_partially_received()
    {
        $po = PurchaseOrder::factory()->create(['status' => 'sent']);
        $this->assertTrue($po->canReceive());

        $po = PurchaseOrder::factory()->create(['status' => 'partially_received']);
        $this->assertTrue($po->canReceive());
    }

    public function test_cannot_receive_when_status_is_not_sent_or_partially_received()
    {
        foreach (['draft', 'approved', 'received', 'cancelled'] as $status) {
            $po = PurchaseOrder::factory()->create(['status' => $status]);
            $this->assertFalse($po->canReceive(), "Should not be receivable when status is $status");
        }
    }

    public function test_can_cancel_when_not_received_or_cancelled()
    {
        foreach (['draft', 'approved', 'sent', 'partially_received'] as $status) {
            $po = PurchaseOrder::factory()->create(['status' => $status]);
            $this->assertTrue($po->canCancel(), "Should be cancellable when status is $status");
        }
    }

    public function test_cannot_cancel_when_received_or_cancelled()
    {
        foreach (['received', 'cancelled'] as $status) {
            $po = PurchaseOrder::factory()->create(['status' => $status]);
            $this->assertFalse($po->canCancel(), "Should not be cancellable when status is $status");
        }
    }

    public function test_purchase_order_belongs_to_supplier()
    {
        $supplier = Supplier::factory()->create();
        $po = PurchaseOrder::factory()->create(['supplier_id' => $supplier->id]);

        $this->assertInstanceOf(Supplier::class, $po->supplier);
        $this->assertEquals($supplier->id, $po->supplier->id);
    }

    public function test_purchase_order_has_creator()
    {
        $user = User::factory()->create();
        $po = PurchaseOrder::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $po->creator);
        $this->assertEquals($user->id, $po->creator->id);
    }

    public function test_purchase_order_has_approver()
    {
        $user = User::factory()->create();
        $po = PurchaseOrder::factory()->create(['approved_by' => $user->id]);

        $this->assertInstanceOf(User::class, $po->approver);
        $this->assertEquals($user->id, $po->approver->id);
    }

    public function test_purchase_order_total_is_positive()
    {
        $po = PurchaseOrder::factory()->create(['total_amount' => 15000]);

        $this->assertGreaterThan(0, $po->total_amount);
    }

    public function test_purchase_order_status_transition_draft_to_approved()
    {
        $po = PurchaseOrder::factory()->create(['status' => 'draft']);
        $this->assertTrue($po->canApprove());

        $po->status = 'approved';
        $po->save();
        $po->refresh();

        $this->assertEquals('approved', $po->status);
        $this->assertFalse($po->canApprove());
        $this->assertTrue($po->canSend());
    }

    public function test_purchase_order_status_transition_approved_to_sent()
    {
        $po = PurchaseOrder::factory()->create(['status' => 'approved']);

        $po->status = 'sent';
        $po->save();
        $po->refresh();

        $this->assertEquals('sent', $po->status);
        $this->assertTrue($po->canReceive());
    }

    public function test_purchase_order_cancellation_from_draft()
    {
        $po = PurchaseOrder::factory()->create(['status' => 'draft']);
        $this->assertTrue($po->canCancel());

        $po->status = 'cancelled';
        $po->save();
        $po->refresh();

        $this->assertEquals('cancelled', $po->status);
        $this->assertFalse($po->canCancel());
    }
}
