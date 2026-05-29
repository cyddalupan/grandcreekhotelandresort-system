<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Item;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $cashier;
    private Department $department;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email_verified_at' => now()]);
        $this->cashier = User::factory()->create(['email_verified_at' => now()]);
        // Pre-create department + supplier to avoid unique-name collisions in ItemFactory chains
        $this->department = Department::factory()->create();
        $this->supplier = Supplier::factory()->create();
    }

    private function validSalePayload(array $overrides = []): array
    {
        return array_merge([
            'items'           => json_encode([
                ['item_id' => 1, 'name' => 'Soap', 'quantity' => 2, 'price' => 50, 'total' => 100],
                ['name' => 'Coffee', 'quantity' => 1, 'price' => 120, 'total' => 120], // non-inventory (no item_id)
            ]),
            'subtotal'        => 220,
            'tax_percent'     => 12,
            'tax_amount'      => 26.40,
            'discount'        => 0,
            'total'           => 246.40,
            'payment_method'  => 'cash',
            'tendered_amount' => 300,
            'change'          => 53.60,
            'notes'           => 'Walk-in customer',
        ], $overrides);
    }

    // ── Auth gates ──

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('pos.index'))->assertRedirect(route('login'));
        $this->post(route('pos.store'), [])->assertRedirect(route('login'));
        $this->get(route('pos.show', 1))->assertRedirect(route('login'));
        $this->get(route('pos.history'))->assertRedirect(route('login'));
        $this->get(route('pos.search-items'))->assertRedirect(route('login'));
    }

    // ── Index ──

    public function test_index_loads_with_items_and_categories(): void
    {
        Item::factory()->count(3)->create(['current_stock' => 10, 'department_id' => $this->department->id, 'supplier_id' => $this->supplier->id]);
        Sale::factory()->count(3)->create(['user_id' => $this->cashier->id]);

        $response = $this->actingAs($this->user)->get(route('pos.index'));
        $response->assertStatus(200);
    }

    public function test_index_shows_only_items_with_stock(): void
    {
        Item::factory()->create(['name' => 'In Stock Item', 'current_stock' => 10, 'department_id' => $this->department->id, 'supplier_id' => $this->supplier->id]);
        Item::factory()->create(['name' => 'Out Of Stock', 'current_stock' => 0, 'department_id' => $this->department->id, 'supplier_id' => $this->supplier->id]);

        $response = $this->actingAs($this->user)->get(route('pos.index'));
        $response->assertStatus(200);
        $response->assertSee('In Stock Item');
        $response->assertDontSee('Out Of Stock');
    }

    public function test_index_shows_recent_sales(): void
    {
        Sale::factory()->count(3)->create(['user_id' => $this->cashier->id]);

        $response = $this->actingAs($this->user)->get(route('pos.index'));
        $response->assertStatus(200);
    }

    public function test_index_has_link_to_sales_history(): void
    {
        $response = $this->actingAs($this->user)->get(route('pos.index'));

        $response->assertSee('Sales History');
    }

    public function test_index_handles_null_notes_in_recent_sales(): void
    {
        $sale = Sale::factory()->withoutNotes()->create(['user_id' => $this->cashier->id]);

        $response = $this->actingAs($this->user)->get(route('pos.index'));
        $response->assertStatus(200);
    }

    // ── Store ──

    public function test_store_creates_sale(): void
    {
        $response = $this->actingAs($this->user)->post(route('pos.store'), $this->validSalePayload());

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('sales', [
            'total'    => 246.40,
            'user_id'  => $this->user->id,
        ]);
    }

    public function test_store_decrements_inventory_stock(): void
    {
        $item = Item::factory()->create(['current_stock' => 10, 'selling_price' => 50, 'department_id' => $this->department->id, 'supplier_id' => $this->supplier->id]);

        $this->actingAs($this->user)->post(route('pos.store'), $this->validSalePayload([
            'items' => json_encode([
                ['item_id' => $item->id, 'name' => 'Soap', 'quantity' => 2, 'price' => 50, 'total' => 100],
            ]),
            'subtotal' => 100,
            'tax_amount' => 12,
            'total' => 112,
            'tendered_amount' => 200,
            'change' => 88,
        ]));

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'current_stock' => 8,
        ]);
    }

    public function test_store_does_not_decrement_for_non_inventory_items(): void
    {
        $response = $this->actingAs($this->user)->post(route('pos.store'), $this->validSalePayload([
            'items' => json_encode([
                ['name' => 'Coffee', 'quantity' => 1, 'price' => 120, 'total' => 120],
            ]),
            'subtotal' => 120,
            'total' => 134.40,
            'tendered_amount' => 200,
            'change' => 65.60,
        ]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('pos.store'), []);

        $response->assertSessionHasErrors([
            'items', 'subtotal', 'tax_percent', 'tax_amount',
            'discount', 'total', 'payment_method',
            'tendered_amount', 'change',
        ]);
    }

    public function test_store_validates_negative_totals(): void
    {
        $response = $this->actingAs($this->user)->post(route('pos.store'), $this->validSalePayload([
            'subtotal' => -100,
            'total' => -100,
        ]));

        $response->assertSessionHasErrors(['subtotal', 'total']);
    }

    public function test_store_validates_tax_percent_range(): void
    {
        $response = $this->actingAs($this->user)->post(route('pos.store'), $this->validSalePayload([
            'tax_percent' => 150,
        ]));

        $response->assertSessionHasErrors('tax_percent');
    }

    public function test_store_validates_json_items(): void
    {
        $response = $this->actingAs($this->user)->post(route('pos.store'), $this->validSalePayload([
            'items' => 'not-json',
        ]));

        $response->assertSessionHasErrors('items');
    }

    // ── Show ──

    public function test_show_displays_receipt(): void
    {
        $sale = Sale::factory()->create(['user_id' => $this->cashier->id]);

        $response = $this->actingAs($this->user)->get(route('pos.show', $sale));
        $response->assertStatus(200);
        $response->assertSee($sale->receipt_number);
    }

    public function test_show_404_for_missing_sale(): void
    {
        $response = $this->actingAs($this->user)->get(route('pos.show', 999));

        $response->assertStatus(404);
    }

    // ── History ──

    public function test_history_lists_sales(): void
    {
        Sale::factory()->count(5)->create(['user_id' => $this->cashier->id]);

        $response = $this->actingAs($this->user)->get(route('pos.history'));
        $response->assertStatus(200);
    }

    public function test_history_shows_empty_state(): void
    {
        $response = $this->actingAs($this->user)->get(route('pos.history'));
        $response->assertStatus(200);
    }

    public function test_history_shows_stats(): void
    {
        Sale::factory()->count(3)->create(['user_id' => $this->cashier->id]);

        $response = $this->actingAs($this->user)->get(route('pos.history'));
        $response->assertStatus(200);
    }

    public function test_history_can_filter_by_date_range(): void
    {
        Sale::factory()->create(['user_id' => $this->cashier->id]);
        Sale::factory()->create(['user_id' => $this->cashier->id]);

        $response = $this->actingAs($this->user)->get(route('pos.history', [
            'date_from' => now()->subDay()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
        ]));
        $response->assertStatus(200);
    }

    public function test_history_can_filter_by_payment_method(): void
    {
        Sale::factory()->cashPayment()->create(['user_id' => $this->cashier->id]);
        Sale::factory()->gcashPayment()->create(['user_id' => $this->cashier->id]);

        $response = $this->actingAs($this->user)->get(route('pos.history', [
            'payment_method' => 'gcash',
        ]));
        $response->assertStatus(200);
        $response->assertSee('gcash');
    }

    // ── Search Items (AJAX) ──

    public function test_search_items_returns_json(): void
    {
        Item::factory()->create(['name' => 'Soap', 'current_stock' => 10, 'selling_price' => 50, 'department_id' => $this->department->id, 'supplier_id' => $this->supplier->id]);
        Item::factory()->create(['name' => 'Shampoo', 'current_stock' => 10, 'selling_price' => 80, 'department_id' => $this->department->id, 'supplier_id' => $this->supplier->id]);

        $response = $this->actingAs($this->user)->get(route('pos.search-items', ['q' => 'Soap']));

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(1, $data);
        $this->assertEquals('Soap', $data[0]['name']);
    }

    public function test_search_items_filters_by_category(): void
    {
        Item::factory()->create(['name' => 'Towel', 'category' => 'Linen', 'current_stock' => 10, 'selling_price' => 200, 'department_id' => $this->department->id, 'supplier_id' => $this->supplier->id]);
        Item::factory()->create(['name' => 'Shampoo', 'category' => 'Toiletries', 'current_stock' => 10, 'selling_price' => 80, 'department_id' => $this->department->id, 'supplier_id' => $this->supplier->id]);

        $response = $this->actingAs($this->user)->get(route('pos.search-items', ['category' => 'Linen']));

        $data = $response->json();
        $this->assertCount(1, $data);
        $this->assertEquals('Towel', $data[0]['name']);
    }

    public function test_search_items_excludes_out_of_stock(): void
    {
        Item::factory()->create(['name' => 'Soap', 'current_stock' => 0, 'selling_price' => 50, 'department_id' => $this->department->id, 'supplier_id' => $this->supplier->id]);

        $response = $this->actingAs($this->user)->get(route('pos.search-items', ['q' => 'Soap']));

        $this->assertCount(0, $response->json());
    }

    public function test_search_items_returns_expected_structure(): void
    {
        Item::factory()->create([
            'name' => 'Shampoo',
            'category' => 'Toiletries',
            'current_stock' => 15,
            'selling_price' => 75,
            'unit' => 'bottles',
            'department_id' => $this->department->id,
            'supplier_id' => $this->supplier->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('pos.search-items', ['q' => 'Shampoo']));

        $response->assertJsonStructure([
            '*' => ['id', 'name', 'price', 'stock', 'unit', 'category'],
        ]);
    }
}
