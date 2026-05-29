<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Item;
use App\Models\Movement;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    // ── isLowStock() business logic ──

    public function test_item_has_low_stock_when_stock_at_or_below_reorder_point()
    {
        $item = Item::factory()->create([
            'current_stock' => 5,
            'min_stock' => 10,
        ]);

        $this->assertTrue($item->isLowStock());
    }

    public function test_item_does_not_have_low_stock_when_stock_above_reorder_point()
    {
        $item = Item::factory()->create([
            'current_stock' => 15,
            'min_stock' => 10,
        ]);

        $this->assertFalse($item->isLowStock());
    }

    public function test_item_has_low_stock_when_stock_equals_reorder_point()
    {
        $item = Item::factory()->create([
            'current_stock' => 10,
            'min_stock' => 10,
        ]);

        $this->assertTrue($item->isLowStock());
    }

    public function test_item_has_low_stock_when_stock_is_zero()
    {
        $item = Item::factory()->create([
            'current_stock' => 0,
            'min_stock' => 1,
        ]);

        $this->assertTrue($item->isLowStock());
    }

    // ── Factory states ──

    public function test_low_stock_factory_state()
    {
        $item = Item::factory()->lowStock()->create();

        $this->assertTrue($item->current_stock <= $item->min_stock);
        $this->assertTrue($item->isLowStock());
    }

    public function test_out_of_stock_factory_state()
    {
        $item = Item::factory()->outOfStock()->create();

        $this->assertEquals(0, $item->current_stock);
        $this->assertTrue($item->isLowStock());
    }

    public function test_with_department_factory_state()
    {
        $department = Department::factory()->create();
        $item = Item::factory()->withDepartment($department)->create();

        $this->assertEquals($department->id, $item->department_id);
        $this->assertInstanceOf(Department::class, $item->department);
    }

    // ── Casts ──

    public function test_expiry_date_is_cast_to_carbon()
    {
        $item = Item::factory()->create(['expiry_date' => '2026-12-31']);

        $this->assertInstanceOf(\Carbon\Carbon::class, $item->expiry_date);
        $this->assertEquals('2026-12-31', $item->expiry_date->format('Y-m-d'));
    }

    public function test_expiry_date_can_be_null()
    {
        $item = Item::factory()->create(['expiry_date' => null]);

        $this->assertNull($item->expiry_date);
    }

    // ── Numeric fields ──

    public function test_purchase_cost_is_decimal()
    {
        $item = Item::factory()->create(['purchase_cost' => 123.45]);

        $this->assertEquals(123.45, $item->purchase_cost);
    }

    public function test_selling_price_is_decimal()
    {
        $item = Item::factory()->create(['selling_price' => 299.99]);

        $this->assertEquals(299.99, $item->selling_price);
    }

    // ── Relationships ──

    public function test_item_belongs_to_department()
    {
        $department = Department::factory()->create();
        $item = Item::factory()->create(['department_id' => $department->id]);

        $this->assertInstanceOf(Department::class, $item->department);
        $this->assertEquals($department->id, $item->department->id);
    }

    public function test_item_department_can_be_null()
    {
        $item = Item::factory()->create(['department_id' => null]);

        $this->assertNull($item->department_id);
    }

    public function test_item_belongs_to_supplier()
    {
        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create(['supplier_id' => $supplier->id]);

        $this->assertInstanceOf(Supplier::class, $item->supplier);
        $this->assertEquals($supplier->id, $item->supplier->id);
    }

    public function test_item_supplier_can_be_null()
    {
        $item = Item::factory()->create(['supplier_id' => null]);

        $this->assertNull($item->supplier_id);
    }

    public function test_item_has_many_movements()
    {
        $item = Item::factory()->create();
        $movement = Movement::factory()->create(['item_id' => $item->id]);

        $this->assertInstanceOf(Movement::class, $item->movements->first());
        $this->assertEquals($movement->id, $item->movements->first()->id);
    }

    public function test_item_has_movements_accessible_through_recent_scope()
    {
        $item = Item::factory()->create();
        Movement::factory()->count(3)->create(['item_id' => $item->id]);

        $this->assertCount(3, $item->movements()->recent()->get());
    }

    // ── CRUD ──

    public function test_item_stock_can_be_updated()
    {
        $item = Item::factory()->create(['current_stock' => 10]);
        $item->current_stock = 25;
        $item->save();
        $item->refresh();

        $this->assertEquals(25, $item->current_stock);
    }

    public function test_item_can_be_created_via_mass_assignment()
    {
        $item = Item::create([
            'name' => 'Test Item',
            'current_stock' => 50,
            'min_stock' => 10,
            'unit' => 'pieces',
            'purchase_cost' => 10.00,
            'selling_price' => 25.00,
        ]);

        $this->assertDatabaseHas('items', ['name' => 'Test Item', 'current_stock' => 50]);
    }

    public function test_item_can_be_deleted()
    {
        $item = Item::factory()->create();
        $item->delete();

        $this->assertModelMissing($item);
    }

    // ── Fillable attributes ──

    public function test_item_default_values()
    {
        $item = Item::factory()->create();

        $this->assertIsNumeric($item->purchase_cost);
        $this->assertIsNumeric($item->selling_price);
        $this->assertIsNumeric($item->current_stock);
        $this->assertIsNumeric($item->min_stock);
        $this->assertNotNull($item->name);
        $this->assertNotNull($item->unit);
    }
}
