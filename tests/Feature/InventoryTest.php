<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Item;
use App\Models\Movement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email_verified_at' => now()]);
    }

    // ── Authentication gates ──

    public function test_unauthenticated_users_are_redirected_to_login()
    {
        $this->get(route('inventory.index'))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_cannot_create()
    {
        $this->get(route('inventory.create'))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_cannot_store()
    {
        $this->post(route('inventory.store'), [
            'name' => 'Test',
            'current_stock' => 0,
            'min_stock' => 0,
            'unit' => 'pieces',
            'purchase_cost' => 0,
            'selling_price' => 0,
        ])->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_cannot_show()
    {
        $item = Item::factory()->create();

        $this->get(route('inventory.show', $item))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_cannot_edit()
    {
        $item = Item::factory()->create();

        $this->get(route('inventory.edit', $item))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_cannot_update()
    {
        $item = Item::factory()->create();

        $this->put(route('inventory.update', $item), ['name' => 'Changed'])
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_cannot_destroy()
    {
        $item = Item::factory()->create();

        $this->delete(route('inventory.destroy', $item))
            ->assertRedirect(route('login'));
    }

    // ── Index ──

    public function test_index_displays_all_items_ordered_by_name()
    {
        $this->actingAs($this->user);

        $items = collect([
            Item::factory()->create(['name' => 'C Item']),
            Item::factory()->create(['name' => 'A Item']),
            Item::factory()->create(['name' => 'B Item']),
        ]);

        $response = $this->get(route('inventory.index'));

        $response->assertOk();

        $response->assertViewHas('items', function ($paginator) {
            return $paginator->count() === 3;
        });

        $names = $response->viewData('items')->pluck('name')->toArray();
        $this->assertEquals(['A Item', 'B Item', 'C Item'], $names);
    }

    public function test_index_paginates_items()
    {
        $this->actingAs($this->user);

        Item::factory()->count(25)->create();

        $response = $this->get(route('inventory.index'));

        $response->assertOk();
        $this->assertCount(20, $response->viewData('items'));
    }

    public function test_index_search_filters_by_name()
    {
        $this->actingAs($this->user);

        Item::factory()->create(['name' => 'Chlorine Tablets']);
        Item::factory()->create(['name' => 'Shampoo']);
        Item::factory()->create(['name' => 'Chlorine Granules']);

        $response = $this->get(route('inventory.index', ['search' => 'Chlorine']));

        $names = $response->viewData('items')->pluck('name')->toArray();
        $this->assertCount(2, $names);
        $this->assertContains('Chlorine Tablets', $names);
        $this->assertContains('Chlorine Granules', $names);
        $this->assertNotContains('Shampoo', $names);
    }

    public function test_index_search_filters_by_category()
    {
        $this->actingAs($this->user);

        Item::factory()->create(['name' => 'Soap Bar', 'category' => 'Toiletries']);
        Item::factory()->create(['name' => 'Shampoo', 'category' => 'Toiletries']);
        Item::factory()->create(['name' => 'Bleach', 'category' => 'Chemicals']);

        $response = $this->get(route('inventory.index', ['search' => 'Toiletries']));

        $names = $response->viewData('items')->pluck('name')->toArray();
        $this->assertCount(2, $names);
    }

    public function test_index_filters_by_department()
    {
        $this->actingAs($this->user);

        $dept1 = Department::factory()->create();
        $dept2 = Department::factory()->create();

        $item1 = Item::factory()->create(['department_id' => $dept1->id, 'name' => 'Dept A Item']);
        $item2 = Item::factory()->create(['department_id' => $dept2->id, 'name' => 'Dept B Item']);

        $response = $this->get(route('inventory.index', ['department_id' => $dept1->id]));

        $names = $response->viewData('items')->pluck('name')->toArray();
        $this->assertCount(1, $names);
        $this->assertEquals(['Dept A Item'], $names);
    }

    public function test_index_shows_empty_state_when_no_items()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('inventory.index'));

        $response->assertOk();
        $response->assertViewHas('items');
        $this->assertCount(0, $response->viewData('items'));
    }

    public function test_index_passes_departments_for_filter_dropdown()
    {
        $this->actingAs($this->user);

        Department::factory()->count(3)->create();

        $response = $this->get(route('inventory.index'));

        $response->assertOk();
        $response->assertViewHas('departments');
        $this->assertCount(3, $response->viewData('departments'));
    }

    // ── Create ──

    public function test_create_form_loads_successfully()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('inventory.create'));

        $response->assertOk();
        $response->assertViewHas('departments');
        $response->assertViewHas('suppliers');
    }

    // ── Store ──

    public function test_store_creates_item_with_inventory_value()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('inventory.store'), [
            'name' => 'New Test Item',
            'category' => 'Chemicals',
            'department_id' => null,
            'supplier_id' => null,
            'current_stock' => 50,
            'min_stock' => 10,
            'unit' => 'bottles',
            'purchase_cost' => 15.50,
            'selling_price' => 35.00,
            'expiry_date' => '2026-12-31',
        ]);

        $response->assertRedirect(route('inventory.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('items', [
            'name' => 'New Test Item',
            'current_stock' => 50,
            'min_stock' => 10,
            'unit' => 'bottles',
            'purchase_cost' => 15.50,
            'selling_price' => 35.00,
        ]);
    }

    public function test_store_requires_name()
    {
        $this->actingAs($this->user);

        $response = $this->from(route('inventory.create'))
            ->post(route('inventory.store'), [
                'name' => '',
                'current_stock' => 10,
                'min_stock' => 5,
                'unit' => 'pieces',
                'purchase_cost' => 10,
                'selling_price' => 20,
            ]);

        $response->assertRedirect(route('inventory.create'));
        $response->assertSessionHasErrors('name');
    }

    public function test_store_requires_current_stock()
    {
        $this->actingAs($this->user);

        $response = $this->from(route('inventory.create'))
            ->post(route('inventory.store'), [
                'name' => 'Test Item',
                'min_stock' => 5,
                'unit' => 'pieces',
                'purchase_cost' => 10,
                'selling_price' => 20,
            ]);

        $response->assertSessionHasErrors('current_stock');
    }

    public function test_store_rejects_negative_stock()
    {
        $this->actingAs($this->user);

        $response = $this->from(route('inventory.create'))
            ->post(route('inventory.store'), [
                'name' => 'Test Item',
                'current_stock' => -1,
                'min_stock' => 5,
                'unit' => 'pieces',
                'purchase_cost' => 10,
                'selling_price' => 20,
            ]);

        $response->assertSessionHasErrors('current_stock');
    }

    public function test_store_rejects_negative_purchase_cost()
    {
        $this->actingAs($this->user);

        $response = $this->from(route('inventory.create'))
            ->post(route('inventory.store'), [
                'name' => 'Test Item',
                'current_stock' => 10,
                'min_stock' => 5,
                'unit' => 'pieces',
                'purchase_cost' => -5,
                'selling_price' => 20,
            ]);

        $response->assertSessionHasErrors('purchase_cost');
    }

    public function test_store_accepts_nullable_fields()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('inventory.store'), [
            'name' => 'Minimal Item',
            'current_stock' => 0,
            'min_stock' => 0,
            'unit' => 'pieces',
            'purchase_cost' => 0,
            'selling_price' => 0,
        ]);

        $response->assertRedirect(route('inventory.index'));

        $this->assertDatabaseHas('items', [
            'name' => 'Minimal Item',
            'category' => null,
            'department_id' => null,
            'supplier_id' => null,
            'expiry_date' => null,
        ]);
    }

    public function test_store_validates_department_id_exists()
    {
        $this->actingAs($this->user);

        $response = $this->from(route('inventory.create'))
            ->post(route('inventory.store'), [
                'name' => 'Test Item',
                'department_id' => 99999,
                'current_stock' => 10,
                'min_stock' => 5,
                'unit' => 'pieces',
                'purchase_cost' => 10,
                'selling_price' => 20,
            ]);

        $response->assertSessionHasErrors('department_id');
    }

    public function test_store_validates_supplier_id_exists()
    {
        $this->actingAs($this->user);

        $response = $this->from(route('inventory.create'))
            ->post(route('inventory.store'), [
                'name' => 'Test Item',
                'supplier_id' => 99999,
                'current_stock' => 10,
                'min_stock' => 5,
                'unit' => 'pieces',
                'purchase_cost' => 10,
                'selling_price' => 20,
            ]);

        $response->assertSessionHasErrors('supplier_id');
    }

    // ── Show ──

    public function test_show_displays_item_details()
    {
        $this->actingAs($this->user);

        $item = Item::factory()->create();

        $response = $this->get(route('inventory.show', $item));

        $response->assertOk();
        $response->assertViewHas('item');
        $this->assertEquals($item->id, $response->viewData('item')->id);
    }

    public function test_show_returns_404_for_nonexistent_item()
    {
        $this->actingAs($this->user);

        $this->get('/inventory/99999')->assertNotFound();
    }

    public function test_show_loads_recent_movements()
    {
        $this->actingAs($this->user);

        $item = Item::factory()->create();
        Movement::factory()->count(3)->create(['item_id' => $item->id]);

        $response = $this->get(route('inventory.show', $item));

        $this->assertCount(3, $response->viewData('item')->movements);
    }

    // ── Edit ──

    public function test_edit_form_loads_with_item_data()
    {
        $this->actingAs($this->user);

        $item = Item::factory()->create();

        $response = $this->get(route('inventory.edit', $item));

        $response->assertOk();
        $response->assertViewHas('item');
        $response->assertViewHas('departments');
        $response->assertViewHas('suppliers');
        $this->assertEquals($item->id, $response->viewData('item')->id);
    }

    public function test_edit_returns_404_for_nonexistent_item()
    {
        $this->actingAs($this->user);

        $this->get('/inventory/99999/edit')->assertNotFound();
    }

    // ── Update ──

    public function test_update_modifies_item()
    {
        $this->actingAs($this->user);

        $item = Item::factory()->create(['name' => 'Old Name']);

        $response = $this->put(route('inventory.update', $item), [
            'name' => 'Updated Name',
            'category' => 'Chemicals',
            'current_stock' => 100,
            'min_stock' => 20,
            'unit' => 'boxes',
            'purchase_cost' => 25.00,
            'selling_price' => 60.00,
        ]);

        $response->assertRedirect(route('inventory.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'name' => 'Updated Name',
            'current_stock' => 100,
            'unit' => 'boxes',
        ]);
    }

    public function test_update_requires_name()
    {
        $this->actingAs($this->user);

        $item = Item::factory()->create();

        $response = $this->from(route('inventory.edit', $item))
            ->put(route('inventory.update', $item), [
                'name' => '',
                'current_stock' => 10,
                'min_stock' => 5,
                'unit' => 'pieces',
                'purchase_cost' => 10,
                'selling_price' => 20,
            ]);

        $response->assertRedirect(route('inventory.edit', $item));
        $response->assertSessionHasErrors('name');
    }

    public function test_update_returns_404_for_nonexistent_item()
    {
        $this->actingAs($this->user);

        $this->put('/inventory/99999', ['name' => 'Nope'])
            ->assertNotFound();
    }

    public function test_update_does_not_affect_other_records()
    {
        $this->actingAs($this->user);

        $itemA = Item::factory()->create(['name' => 'Item A']);
        $itemB = Item::factory()->create(['name' => 'Item B']);

        $this->put(route('inventory.update', $itemA), [
            'name' => 'Item A Updated',
            'current_stock' => 10,
            'min_stock' => 5,
            'unit' => 'pieces',
            'purchase_cost' => 10,
            'selling_price' => 20,
        ]);

        $this->assertDatabaseHas('items', ['id' => $itemB->id, 'name' => 'Item B']);
    }

    // ── Destroy ──

    public function test_destroy_deletes_item()
    {
        $this->actingAs($this->user);

        $item = Item::factory()->create();

        $response = $this->delete(route('inventory.destroy', $item));

        $response->assertRedirect(route('inventory.index'));
        $response->assertSessionHas('success');

        $this->assertModelMissing($item);
    }

    public function test_destroy_returns_404_for_nonexistent_item()
    {
        $this->actingAs($this->user);

        $this->delete('/inventory/99999')->assertNotFound();
    }

    public function test_destroy_does_not_affect_other_records()
    {
        $this->actingAs($this->user);

        $itemA = Item::factory()->create();
        $itemB = Item::factory()->create();

        $this->delete(route('inventory.destroy', $itemA));

        $this->assertModelMissing($itemA);
        $this->assertModelExists($itemB);
    }
}
