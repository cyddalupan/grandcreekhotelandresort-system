<?php

namespace Tests\Unit;

use App\Models\Supplier;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    /*
     * Basic attributes
     */

    public function test_supplier_has_name()
    {
        $supplier = Supplier::factory()->create([
            'name' => 'Acme Supplies Inc.',
        ]);

        $this->assertEquals('Acme Supplies Inc.', $supplier->name);
    }

    public function test_supplier_has_contact_info()
    {
        $supplier = Supplier::factory()->create([
            'contact_person' => 'Maria Santos',
            'email' => 'maria@acme.com',
            'phone' => '09171234567',
        ]);

        $this->assertEquals('Maria Santos', $supplier->contact_person);
        $this->assertEquals('maria@acme.com', $supplier->email);
        $this->assertEquals('09171234567', $supplier->phone);
    }

    /*
     * Business logic: contact fields are optional
     */

    public function test_supplier_contact_fields_can_be_nullable()
    {
        $supplier = Supplier::factory()->create([
            'contact_person' => null,
            'phone' => null,
            'email' => null,
        ]);

        $this->assertNull($supplier->contact_person);
        $this->assertNull($supplier->phone);
        $this->assertNull($supplier->email);
    }

    /*
     * Business logic: total_purchases tracks supplier engagement
     */

    public function test_supplier_total_purchases_defaults_to_zero()
    {
        $supplier = Supplier::factory()->create(['total_purchases' => 0]);

        $this->assertEquals(0, $supplier->total_purchases);
    }

    public function test_supplier_total_purchases_can_be_updated()
    {
        $supplier = Supplier::factory()->create(['total_purchases' => 15000]);

        $this->assertEquals(15000, $supplier->total_purchases);

        $supplier->total_purchases = 75000;
        $supplier->save();
        $supplier->refresh();

        $this->assertEquals(75000, $supplier->total_purchases);
    }

    public function test_supplier_total_purchases_is_valid_decimal()
    {
        $supplier = Supplier::factory()->create(['total_purchases' => 99999.99]);

        $this->assertIsNumeric($supplier->total_purchases);
        $this->assertEquals(99999.99, $supplier->total_purchases);
    }

    public function test_supplier_name_can_be_updated()
    {
        $supplier = Supplier::factory()->create(['name' => 'Old Supplier Name']);

        $supplier->name = 'New Supplier Name';
        $supplier->save();
        $supplier->refresh();

        $this->assertEquals('New Supplier Name', $supplier->name);
    }

    /*
     * Business logic: supplier has items
     */

    public function test_supplier_can_have_items()
    {
        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create(['supplier_id' => $supplier->id]);

        $this->assertCount(1, $supplier->items);
        $this->assertEquals($item->id, $supplier->items->first()->id);
        $this->assertInstanceOf(Item::class, $supplier->items->first());
    }

    public function test_supplier_can_have_multiple_items()
    {
        $supplier = Supplier::factory()->create();
        Item::factory()->count(3)->create(['supplier_id' => $supplier->id]);

        $supplier->load('items');
        $this->assertCount(3, $supplier->items);
    }

    public function test_supplier_with_no_items_returns_empty_collection()
    {
        $supplier = Supplier::factory()->create();

        $this->assertCount(0, $supplier->items);
    }
}
