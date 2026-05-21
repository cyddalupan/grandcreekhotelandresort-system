<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_item_belongs_to_department()
    {
        $department = Department::factory()->create();
        $item = Item::factory()->create(['department_id' => $department->id]);

        $this->assertInstanceOf(Department::class, $item->department);
        $this->assertEquals($department->id, $item->department->id);
    }

    public function test_item_belongs_to_supplier()
    {
        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create(['supplier_id' => $supplier->id]);

        $this->assertInstanceOf(Supplier::class, $item->supplier);
        $this->assertEquals($supplier->id, $item->supplier->id);
    }

    public function test_item_stock_can_be_updated()
    {
        $item = Item::factory()->create(['current_stock' => 10]);
        $item->current_stock = 25;
        $item->save();
        $item->refresh();

        $this->assertEquals(25, $item->current_stock);
    }

    public function test_item_default_values()
    {
        $item = Item::factory()->create();

        $this->assertIsNumeric($item->purchase_cost);
        $this->assertIsNumeric($item->selling_price);
        $this->assertIsNumeric($item->current_stock);
        $this->assertIsNumeric($item->min_stock);
        $this->assertNotNull($item->name);
    }
}
