<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email_verified_at' => now()]);
        $this->supplier = Supplier::factory()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'supplier_id' => $this->supplier->id,
            'items'       => json_encode([
                ['item_id' => null, 'name' => 'Test Item', 'qty' => 10, 'unit' => 'pcs', 'unit_price' => 100, 'total' => 1000],
            ]),
            'notes'       => 'Test PO',
        ], $overrides);
    }

    private function createPo(array $overrides = []): PurchaseOrder
    {
        return PurchaseOrder::factory()->create(array_merge([
            'supplier_id' => $this->supplier->id,
            'created_by'  => $this->user->id,
        ], $overrides));
    }

    // ── Auth Gate ──

    public function test_guest_cannot_access_index(): void
    {
        $this->get(route('purchase-orders.index'))->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_create(): void
    {
        $this->get(route('purchase-orders.create'))->assertRedirect(route('login'));
    }

    public function test_guest_cannot_store(): void
    {
        $this->post(route('purchase-orders.store'), $this->validPayload())
            ->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_show(): void
    {
        $po = $this->createPo();
        $this->get(route('purchase-orders.show', $po))
            ->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_edit(): void
    {
        $po = $this->createPo();
        $this->get(route('purchase-orders.edit', $po))
            ->assertRedirect(route('login'));
    }

    public function test_guest_cannot_update(): void
    {
        $po = $this->createPo();
        $this->put(route('purchase-orders.update', $po), $this->validPayload())
            ->assertRedirect(route('login'));
    }

    public function test_guest_cannot_destroy(): void
    {
        $po = $this->createPo();
        $this->delete(route('purchase-orders.destroy', $po))
            ->assertRedirect(route('login'));
    }

    public function test_guest_cannot_approve(): void
    {
        $po = $this->createPo(['status' => 'draft']);
        $this->post(route('purchase-orders.approve', $po))
            ->assertRedirect(route('login'));
    }

    public function test_guest_cannot_send(): void
    {
        $po = $this->createPo(['status' => 'approved']);
        $this->post(route('purchase-orders.send', $po))
            ->assertRedirect(route('login'));
    }

    public function test_guest_cannot_receive(): void
    {
        $po = $this->createPo(['status' => 'sent']);
        $this->post(route('purchase-orders.receive', $po))
            ->assertRedirect(route('login'));
    }

    public function test_guest_cannot_cancel(): void
    {
        $po = $this->createPo(['status' => 'draft']);
        $this->post(route('purchase-orders.cancel', $po))
            ->assertRedirect(route('login'));
    }

    // ── Index ──

    public function test_index_lists_purchase_orders(): void
    {
        $purchOrders = PurchaseOrder::factory()
            ->count(3)
            ->create(['supplier_id' => $this->supplier->id, 'created_by' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('purchase-orders.index'));
        $response->assertStatus(200);
        $response->assertSee($purchOrders[0]->supplier->name);
    }

    public function test_index_shows_stats(): void
    {
        $this->createPo(['status' => 'draft']);
        $this->createPo(['status' => 'approved']);
        $this->createPo(['status' => 'received']);

        $response = $this->actingAs($this->user)->get(route('purchase-orders.index'));
        $response->assertStatus(200);
    }

    public function test_index_shows_empty_state(): void
    {
        $response = $this->actingAs($this->user)->get(route('purchase-orders.index'));
        $response->assertStatus(200);
    }

    public function test_index_filters_by_status(): void
    {
        $po = $this->createPo(['status' => 'draft']);

        $response = $this->actingAs($this->user)->get(route('purchase-orders.index', ['status' => 'draft']));
        $response->assertStatus(200);
        $response->assertSee($po->supplier->name);
    }

    public function test_index_searches_by_po_number(): void
    {
        $po = $this->createPo();

        $response = $this->actingAs($this->user)->get(route('purchase-orders.index', ['search' => 'test']));
        $response->assertStatus(200);
    }

    // ── Create ──

    public function test_create_form_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('purchase-orders.create'));
        $response->assertStatus(200);
        $response->assertSee($this->supplier->name);
    }

    // ── Store ──

    public function test_store_creates_purchase_order(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('purchase-orders.store'), $this->validPayload());
        $response->assertRedirect();

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $this->supplier->id,
            'status'      => 'draft',
            'created_by'  => $this->user->id,
        ]);
    }

    public function test_store_returns_po_number_in_flash(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('purchase-orders.store'), $this->validPayload());
        $response->assertSessionHas('success');
    }

    public function test_store_requires_supplier_id(): void
    {
        $this->actingAs($this->user);

        $this->post(route('purchase-orders.store'), $this->validPayload(['supplier_id' => '']))
            ->assertSessionHasErrors('supplier_id');
    }

    public function test_store_requires_items_as_json(): void
    {
        $this->actingAs($this->user);

        $this->post(route('purchase-orders.store'), $this->validPayload(['items' => '']))
            ->assertSessionHasErrors('items');
    }

    public function test_store_rejects_empty_items_array(): void
    {
        $this->actingAs($this->user);

        $this->post(route('purchase-orders.store'), $this->validPayload(['items' => json_encode([])]))
            ->assertSessionHasErrors('items');
    }

    public function test_store_supplier_must_exist(): void
    {
        $this->actingAs($this->user);

        $this->post(route('purchase-orders.store'), $this->validPayload(['supplier_id' => 999]))
            ->assertSessionHasErrors('supplier_id');
    }

    // ── Show ──

    public function test_show_displays_purchase_order(): void
    {
        $po = $this->createPo();

        $response = $this->actingAs($this->user)->get(route('purchase-orders.show', $po));
        $response->assertStatus(200);
        $response->assertSee($po->po_number);
    }

    public function test_show_returns_404_for_missing(): void
    {
        $this->actingAs($this->user);

        $this->get('/purchase-orders/99999')->assertStatus(404);
    }

    // ── Edit ──

    public function test_edit_form_loads(): void
    {
        $po = $this->createPo();

        $response = $this->actingAs($this->user)->get(route('purchase-orders.edit', $po));
        $response->assertStatus(200);
        $response->assertSee($po->po_number);
    }

    public function test_edit_redirects_for_received_po(): void
    {
        $po = $this->createPo(['status' => 'received']);

        $this->actingAs($this->user)
            ->get(route('purchase-orders.edit', $po))
            ->assertRedirect();
    }

    public function test_edit_redirects_for_cancelled_po(): void
    {
        $po = $this->createPo(['status' => 'cancelled']);

        $this->actingAs($this->user)
            ->get(route('purchase-orders.edit', $po))
            ->assertRedirect();
    }

    // ── Update ──

    public function test_update_modifies_purchase_order(): void
    {
        $po = $this->createPo(['status' => 'draft']);

        $this->actingAs($this->user);

        $this->put(route('purchase-orders.update', $po), $this->validPayload([
            'notes' => 'Updated notes',
        ]))->assertRedirect(route('purchase-orders.show', $po));

        $this->assertDatabaseHas('purchase_orders', [
            'id'    => $po->id,
            'notes' => 'Updated notes',
        ]);
    }

    public function test_update_resets_approved_to_draft(): void
    {
        $po = $this->createPo(['status' => 'approved']);

        $this->actingAs($this->user);

        $this->put(route('purchase-orders.update', $po), $this->validPayload())
            ->assertRedirect();

        $po->refresh();
        $this->assertEquals('draft', $po->status);
    }

    public function test_update_resets_sent_to_draft(): void
    {
        $po = $this->createPo(['status' => 'sent']);

        $this->actingAs($this->user);

        $this->put(route('purchase-orders.update', $po), $this->validPayload())
            ->assertRedirect();

        $po->refresh();
        $this->assertEquals('draft', $po->status);
    }

    public function test_update_fails_for_received_po(): void
    {
        $po = $this->createPo(['status' => 'received']);

        $this->actingAs($this->user);

        $this->put(route('purchase-orders.update', $po), $this->validPayload())
            ->assertRedirect();
    }

    public function test_update_fails_for_cancelled_po(): void
    {
        $po = $this->createPo(['status' => 'cancelled']);

        $this->actingAs($this->user);

        $this->put(route('purchase-orders.update', $po), $this->validPayload())
            ->assertRedirect();
    }

    // ── Destroy ──

    public function test_destroy_deletes_draft(): void
    {
        $po = $this->createPo(['status' => 'draft']);

        $this->actingAs($this->user)
            ->delete(route('purchase-orders.destroy', $po))
            ->assertRedirect(route('purchase-orders.index'));

        $this->assertDatabaseMissing('purchase_orders', ['id' => $po->id]);
    }

    public function test_destroy_fails_for_received_po(): void
    {
        $po = $this->createPo(['status' => 'received']);

        $this->actingAs($this->user)
            ->delete(route('purchase-orders.destroy', $po))
            ->assertRedirect();

        $this->assertDatabaseHas('purchase_orders', ['id' => $po->id]);
    }

    public function test_destroy_fails_for_cancelled_po(): void
    {
        $po = $this->createPo(['status' => 'cancelled']);

        $this->actingAs($this->user)
            ->delete(route('purchase-orders.destroy', $po))
            ->assertRedirect();

        $this->assertDatabaseHas('purchase_orders', ['id' => $po->id]);
    }

    // ── Approve ──

    public function test_approve_changes_draft_to_approved(): void
    {
        $po = $this->createPo(['status' => 'draft']);

        $this->actingAs($this->user)
            ->post(route('purchase-orders.approve', $po))
            ->assertRedirect();

        $po->refresh();
        $this->assertEquals('approved', $po->status);
        $this->assertEquals($this->user->id, $po->approved_by);
        $this->assertNotNull($po->approved_at);
    }

    public function test_approve_fails_for_approved_po(): void
    {
        $po = $this->createPo(['status' => 'approved']);

        $this->actingAs($this->user)
            ->post(route('purchase-orders.approve', $po))
            ->assertRedirect();

        $po->refresh();
        $this->assertEquals('approved', $po->status);
    }

    public function test_approve_fails_for_cancelled_po(): void
    {
        $po = $this->createPo(['status' => 'cancelled']);

        $this->actingAs($this->user)
            ->post(route('purchase-orders.approve', $po))
            ->assertRedirect();

        $po->refresh();
        $this->assertEquals('cancelled', $po->status);
    }

    // ── Send ──

    public function test_send_changes_approved_to_sent(): void
    {
        $po = $this->createPo(['status' => 'approved']);

        $this->actingAs($this->user)
            ->post(route('purchase-orders.send', $po))
            ->assertRedirect();

        $po->refresh();
        $this->assertEquals('sent', $po->status);
    }

    public function test_send_fails_for_draft(): void
    {
        $po = $this->createPo(['status' => 'draft']);

        $this->actingAs($this->user)
            ->post(route('purchase-orders.send', $po))
            ->assertRedirect();

        $po->refresh();
        $this->assertEquals('draft', $po->status);
    }

    public function test_send_fails_for_received(): void
    {
        $po = $this->createPo(['status' => 'received']);

        $this->actingAs($this->user)
            ->post(route('purchase-orders.send', $po))
            ->assertRedirect();

        $po->refresh();
        $this->assertEquals('received', $po->status);
    }

    // ── Receive ──

    public function test_receive_changes_sent_to_received(): void
    {
        $items = [['name' => 'Item A', 'qty' => 10, 'unit' => 'pcs', 'unit_price' => 50, 'total' => 500]];
        $po = $this->createPo(['status' => 'sent', 'items' => $items]);

        $this->actingAs($this->user)
            ->post(route('purchase-orders.receive', $po), [
                'received_items' => json_encode([10]),
            ])
            ->assertRedirect();

        $po->refresh();
        $this->assertEquals('received', $po->status);
        $this->assertNotNull($po->received_at);
        $this->assertEquals(10, $po->items[0]['qty_received']);
    }

    public function test_receive_partially_received(): void
    {
        $items = [['name' => 'Item A', 'qty' => 10, 'unit' => 'pcs', 'unit_price' => 50, 'total' => 500]];
        $po = $this->createPo(['status' => 'sent', 'items' => $items]);

        $this->actingAs($this->user)
            ->post(route('purchase-orders.receive', $po), [
                'received_items' => json_encode([5]),
            ])
            ->assertRedirect();

        $po->refresh();
        $this->assertEquals('partially_received', $po->status);
    }

    public function test_receive_increments_stock(): void
    {
        $department = Department::factory()->create();
        $invItem = Item::factory()->create([
            'current_stock'  => 10,
            'supplier_id'    => $this->supplier->id,
            'department_id'  => $department->id,
        ]);
        $items = [['item_id' => $invItem->id, 'name' => 'Item A', 'qty' => 5, 'unit' => 'pcs', 'unit_price' => 50, 'total' => 250]];
        $po = $this->createPo(['status' => 'sent', 'items' => $items]);

        $this->actingAs($this->user)
            ->post(route('purchase-orders.receive', $po), [
                'received_items' => json_encode([5]),
            ])
            ->assertRedirect();

        $invItem->refresh();
        $this->assertEquals(15, $invItem->current_stock);
    }

    public function test_receive_fails_for_draft(): void
    {
        $po = $this->createPo(['status' => 'draft']);

        $this->actingAs($this->user)
            ->post(route('purchase-orders.receive', $po))
            ->assertRedirect();
    }

    // ── Cancel ──

    public function test_cancel_changes_draft_to_cancelled(): void
    {
        $po = $this->createPo(['status' => 'draft']);

        $this->actingAs($this->user)
            ->post(route('purchase-orders.cancel', $po))
            ->assertRedirect();

        $po->refresh();
        $this->assertEquals('cancelled', $po->status);
    }

    public function test_cancel_fails_for_received(): void
    {
        $po = $this->createPo(['status' => 'received']);

        $this->actingAs($this->user)
            ->post(route('purchase-orders.cancel', $po))
            ->assertRedirect();

        $po->refresh();
        $this->assertEquals('received', $po->status);
    }

    public function test_cancel_fails_for_cancelled(): void
    {
        $po = $this->createPo(['status' => 'cancelled']);

        $this->actingAs($this->user)
            ->post(route('purchase-orders.cancel', $po))
            ->assertRedirect();

        $po->refresh();
        $this->assertEquals('cancelled', $po->status);
    }

    // ── Supplier Items AJAX ──

    public function test_supplier_items_returns_items_for_supplier(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('purchase-orders.supplier-items', $this->supplier));
        $response->assertStatus(200);
        $response->assertJson([]);
    }
}
