<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email_verified_at' => now()]);
    }

    // ── Auth gates ──

    public function test_unauthenticated_users_cannot_view_suppliers()
    {
        $this->get(route('suppliers.index'))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_cannot_create()
    {
        $this->get(route('suppliers.create'))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_cannot_store()
    {
        $this->post(route('suppliers.store'), ['name' => 'Test'])
            ->assertRedirect(route('login'));
    }

    // ── Index ──

    public function test_index_displays_suppliers()
    {
        $this->actingAs($this->user);

        Supplier::factory()->count(5)->create();

        $response = $this->get(route('suppliers.index'));

        $response->assertOk();
        $response->assertViewHas('suppliers');
        $this->assertCount(5, $response->viewData('suppliers'));
    }

    public function test_index_shows_empty_state()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('suppliers.index'));

        $response->assertOk();
        $response->assertSee('No suppliers found');
    }

    // ── Create ──

    public function test_create_form_loads()
    {
        $this->actingAs($this->user);

        $this->get(route('suppliers.create'))->assertOk();
    }

    // ── Store ──

    public function test_store_creates_supplier()
    {
        $this->actingAs($this->user);

        $this->post(route('suppliers.store'), [
            'name' => 'ACME Corp',
            'contact_person' => 'John Doe',
            'phone' => '09171234567',
            'email' => 'john@acme.com',
            'total_purchases' => 15000,
        ])->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', [
            'name' => 'ACME Corp',
            'email' => 'john@acme.com',
        ]);
    }

    public function test_store_requires_name()
    {
        $this->actingAs($this->user);

        $this->post(route('suppliers.store'), [
            'contact_person' => 'John',
            'total_purchases' => 1000,
        ])->assertSessionHasErrors('name');
    }

    public function test_store_rejects_negative_purchases()
    {
        $this->actingAs($this->user);

        $this->post(route('suppliers.store'), [
            'name' => 'Test',
            'total_purchases' => -500,
        ])->assertSessionHasErrors('total_purchases');
    }

    public function test_store_accepts_optional_fields()
    {
        $this->actingAs($this->user);

        $this->post(route('suppliers.store'), [
            'name' => 'Minimal Supplier',
            'total_purchases' => 0,
        ])->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', ['name' => 'Minimal Supplier']);
    }

    // ── Edit ──

    public function test_edit_form_loads()
    {
        $this->actingAs($this->user);

        $supplier = Supplier::factory()->create();

        $this->get(route('suppliers.edit', $supplier))->assertOk();
    }

    public function test_edit_returns_404_for_nonexistent()
    {
        $this->actingAs($this->user);

        $this->get('/suppliers/99999/edit')->assertNotFound();
    }

    // ── Update ──

    public function test_update_modifies_supplier()
    {
        $this->actingAs($this->user);

        $supplier = Supplier::factory()->create(['name' => 'Old Name']);

        $this->put(route('suppliers.update', $supplier), [
            'name' => 'New Name',
            'contact_person' => $supplier->contact_person,
            'phone' => $supplier->phone,
            'email' => $supplier->email,
            'total_purchases' => $supplier->total_purchases,
        ])->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'New Name',
        ]);
    }

    // ── Destroy ──

    public function test_destroy_deletes_supplier()
    {
        $this->actingAs($this->user);

        $supplier = Supplier::factory()->create();

        $this->delete(route('suppliers.destroy', $supplier))
            ->assertRedirect(route('suppliers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }

    public function test_destroy_returns_404_for_nonexistent()
    {
        $this->actingAs($this->user);

        $this->delete('/suppliers/99999')->assertNotFound();
    }

    public function test_destroy_does_not_affect_other_suppliers()
    {
        $this->actingAs($this->user);

        $s1 = Supplier::factory()->create();
        $s2 = Supplier::factory()->create();
        $s3 = Supplier::factory()->create();

        $this->delete(route('suppliers.destroy', $s2));

        $this->assertDatabaseHas('suppliers', ['id' => $s1->id]);
        $this->assertDatabaseMissing('suppliers', ['id' => $s2->id]);
        $this->assertDatabaseHas('suppliers', ['id' => $s3->id]);
    }

    public function test_destroy_handles_supplier_with_items()
    {
        $this->actingAs($this->user);

        $supplier = Supplier::factory()->create();
        // Attach items (foreign key nullOnDelete)
        \App\Models\Item::factory()->count(3)->create([
            'supplier_id' => $supplier->id,
        ]);

        $this->delete(route('suppliers.destroy', $supplier));

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
        // Items should still exist with null supplier_id
        $this->assertDatabaseHas('items', ['supplier_id' => null]);
    }
}
