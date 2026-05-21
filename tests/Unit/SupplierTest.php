<?php

namespace Tests\Unit;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

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
}
