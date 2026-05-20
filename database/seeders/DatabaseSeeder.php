<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Movement;
use App\Models\Bill;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@grandcreek.com',
            'password' => Hash::make('password'),
        ]);

        // Departments
        $departments = Department::insert([
            ['name' => 'Front Office', 'description' => 'Reception and guest services', 'manager' => 'Maria Santos', 'active' => true, 'item_count' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Housekeeping', 'description' => 'Room cleaning and maintenance', 'manager' => 'Juan Dela Cruz', 'active' => true, 'item_count' => 45, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Food & Beverage', 'description' => 'Restaurant and bar operations', 'manager' => 'Ana Reyes', 'active' => true, 'item_count' => 120, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kitchen', 'description' => 'Food preparation and cooking', 'manager' => 'Carlos Garcia', 'active' => true, 'item_count' => 85, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Maintenance', 'description' => 'Facility repairs and upkeep', 'manager' => 'Roberto Cruz', 'active' => true, 'item_count' => 35, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Laundry', 'description' => 'Linen and uniform cleaning', 'manager' => 'Elena Torres', 'active' => true, 'item_count' => 25, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Security', 'description' => 'Property and guest safety', 'manager' => 'Miguel Ramos', 'active' => true, 'item_count' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Spa & Wellness', 'description' => 'Spa treatments and fitness', 'manager' => 'Sofia Mendoza', 'active' => true, 'item_count' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Events & Banquet', 'description' => 'Event planning and execution', 'manager' => 'Luis Fernandez', 'active' => true, 'item_count' => 40, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Administration', 'description' => 'Office and management', 'manager' => 'Carmen Lopez', 'active' => true, 'item_count' => 18, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Suppliers
        Supplier::insert([
            ['name' => 'Premium Linens Inc.', 'contact_person' => 'John Smith', 'phone' => '+63 917 123 4567', 'email' => 'john@premiumlinens.com', 'total_purchases' => 450000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Spa Essentials Co.', 'contact_person' => 'Mary Johnson', 'phone' => '+63 918 234 5678', 'email' => 'mary@spaessentials.com', 'total_purchases' => 280000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Maintenance Supplies Hub', 'contact_person' => 'Robert Brown', 'phone' => '+63 919 345 6789', 'email' => 'robert@maintenancehub.com', 'total_purchases' => 320000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fresh Foods Distributor', 'contact_person' => 'Lisa Davis', 'phone' => '+63 920 456 7890', 'email' => 'lisa@freshfoods.com', 'total_purchases' => 850000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Beverage Solutions', 'contact_person' => 'David Wilson', 'phone' => '+63 921 567 8901', 'email' => 'david@beveragesolutions.com', 'total_purchases' => 380000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Hotel Furnishings Ltd.', 'contact_person' => 'Sarah Martinez', 'phone' => '+63 922 678 9012', 'email' => 'sarah@hotelfurnishings.com', 'total_purchases' => 620000, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Items
        Item::insert([
            ['name' => 'Bed Sheets (Queen)', 'category' => 'Linen', 'department_id' => 2, 'supplier_id' => 1, 'current_stock' => 150, 'min_stock' => 50, 'unit' => 'pieces', 'purchase_cost' => 450, 'selling_price' => 0, 'expiry_date' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Towels (Bath)', 'category' => 'Linen', 'department_id' => 2, 'supplier_id' => 1, 'current_stock' => 200, 'min_stock' => 80, 'unit' => 'pieces', 'purchase_cost' => 180, 'selling_price' => 0, 'expiry_date' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Shampoo Bottles', 'category' => 'Toiletries', 'department_id' => 2, 'supplier_id' => 2, 'current_stock' => 300, 'min_stock' => 100, 'unit' => 'bottles', 'purchase_cost' => 45, 'selling_price' => 0, 'expiry_date' => '2025-12-31', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cleaning Solution', 'category' => 'Chemicals', 'department_id' => 2, 'supplier_id' => 3, 'current_stock' => 25, 'min_stock' => 30, 'unit' => 'liters', 'purchase_cost' => 350, 'selling_price' => 0, 'expiry_date' => '2025-06-30', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rice (Premium)', 'category' => 'Food', 'department_id' => 4, 'supplier_id' => 4, 'current_stock' => 500, 'min_stock' => 200, 'unit' => 'kg', 'purchase_cost' => 55, 'selling_price' => 0, 'expiry_date' => '2024-12-31', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Chicken Breast', 'category' => 'Food', 'department_id' => 4, 'supplier_id' => 4, 'current_stock' => 80, 'min_stock' => 50, 'unit' => 'kg', 'purchase_cost' => 280, 'selling_price' => 0, 'expiry_date' => '2024-06-15', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Coffee Beans', 'category' => 'Beverage', 'department_id' => 3, 'supplier_id' => 5, 'current_stock' => 40, 'min_stock' => 20, 'unit' => 'kg', 'purchase_cost' => 850, 'selling_price' => 0, 'expiry_date' => '2025-03-31', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Wine Glasses', 'category' => 'Glassware', 'department_id' => 3, 'supplier_id' => 6, 'current_stock' => 120, 'min_stock' => 50, 'unit' => 'pieces', 'purchase_cost' => 95, 'selling_price' => 0, 'expiry_date' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Light Bulbs (LED)', 'category' => 'Electrical', 'department_id' => 5, 'supplier_id' => 3, 'current_stock' => 80, 'min_stock' => 40, 'unit' => 'pieces', 'purchase_cost' => 120, 'selling_price' => 0, 'expiry_date' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Paint (White)', 'category' => 'Maintenance', 'department_id' => 5, 'supplier_id' => 3, 'current_stock' => 15, 'min_stock' => 10, 'unit' => 'gallons', 'purchase_cost' => 650, 'selling_price' => 0, 'expiry_date' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Massage Oil', 'category' => 'Spa Supplies', 'department_id' => 8, 'supplier_id' => 2, 'current_stock' => 35, 'min_stock' => 15, 'unit' => 'bottles', 'purchase_cost' => 450, 'selling_price' => 0, 'expiry_date' => '2025-09-30', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Banquet Chairs', 'category' => 'Furniture', 'department_id' => 9, 'supplier_id' => 6, 'current_stock' => 200, 'min_stock' => 150, 'unit' => 'pieces', 'purchase_cost' => 1200, 'selling_price' => 0, 'expiry_date' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Movements
        Movement::insert([
            ['item_id' => 1, 'type' => 'IN', 'quantity' => 50, 'from_department' => null, 'to_department' => 2, 'reason' => null, 'user' => 'Admin', 'notes' => 'Monthly stock replenishment', 'date' => '2024-06-01 10:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['item_id' => 3, 'type' => 'OUT', 'quantity' => 20, 'from_department' => 2, 'to_department' => null, 'reason' => 'Usage', 'user' => 'Juan Dela Cruz', 'notes' => 'Daily room service', 'date' => '2024-06-02 14:30:00', 'created_at' => now(), 'updated_at' => now()],
            ['item_id' => 5, 'type' => 'IN', 'quantity' => 200, 'from_department' => null, 'to_department' => 4, 'reason' => null, 'user' => 'Admin', 'notes' => 'Weekly food delivery', 'date' => '2024-06-03 09:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['item_id' => 7, 'type' => 'TRANSFER', 'quantity' => 5, 'from_department' => 3, 'to_department' => 4, 'reason' => 'Kitchen requested additional coffee', 'user' => 'Ana Reyes', 'notes' => 'Cross-dept transfer', 'date' => '2024-06-04 11:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['item_id' => 9, 'type' => 'OUT', 'quantity' => 10, 'from_department' => 5, 'to_department' => null, 'reason' => 'Usage', 'user' => 'Roberto Cruz', 'notes' => 'Replaced burnt bulbs in lobby', 'date' => '2024-06-05 16:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['item_id' => 12, 'type' => 'IN', 'quantity' => 50, 'from_department' => null, 'to_department' => 9, 'reason' => null, 'user' => 'Admin', 'notes' => 'New banquet furniture delivery', 'date' => '2024-06-06 08:00:00', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Bills
        Bill::insert([
            ['type' => 'Electricity', 'provider' => 'Manila Electric Company', 'account_number' => 'MERALCO-12345', 'amount' => 85000, 'due_date' => '2024-06-15', 'status' => 'Pending', 'billing_period' => 'May 2024', 'payment_date' => null, 'payment_method' => null, 'payment_reference' => null, 'notes' => null, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'Water', 'provider' => 'Manila Water', 'account_number' => 'MW-67890', 'amount' => 12500, 'due_date' => '2024-06-10', 'status' => 'Paid', 'billing_period' => 'May 2024', 'payment_date' => '2024-06-08', 'payment_method' => 'Bank Transfer', 'payment_reference' => 'BT-2024-001', 'notes' => null, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'Internet', 'provider' => 'PLDT Fibr', 'account_number' => 'PLDT-54321', 'amount' => 8500, 'due_date' => '2024-06-20', 'status' => 'Pending', 'billing_period' => 'June 2024', 'payment_date' => null, 'payment_method' => null, 'payment_reference' => null, 'notes' => null, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'Telephone', 'provider' => 'Globe Telecom', 'account_number' => 'GLOBE-98765', 'amount' => 4200, 'due_date' => '2024-06-05', 'status' => 'Overdue', 'billing_period' => 'May 2024', 'payment_date' => null, 'payment_method' => null, 'payment_reference' => null, 'notes' => null, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'Gas', 'provider' => 'Petron LPG', 'account_number' => 'PETRON-11111', 'amount' => 15000, 'due_date' => '2024-06-25', 'status' => 'Pending', 'billing_period' => 'June 2024', 'payment_date' => null, 'payment_method' => null, 'payment_reference' => null, 'notes' => null, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'Maintenance', 'provider' => 'Elite Facility Services', 'account_number' => 'EFS-22222', 'amount' => 35000, 'due_date' => '2024-06-30', 'status' => 'Pending', 'billing_period' => 'June 2024', 'payment_date' => null, 'payment_method' => null, 'payment_reference' => null, 'notes' => null, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'Insurance', 'provider' => 'Philippine Insurance Co.', 'account_number' => 'PIC-33333', 'amount' => 45000, 'due_date' => '2024-06-01', 'status' => 'Paid', 'billing_period' => 'Q2 2024', 'payment_date' => '2024-05-28', 'payment_method' => 'Check', 'payment_reference' => 'CHK-2024-002', 'notes' => null, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'Rent', 'provider' => 'Property Holdings Inc.', 'account_number' => 'PHI-44444', 'amount' => 250000, 'due_date' => '2024-06-01', 'status' => 'Paid', 'billing_period' => 'June 2024', 'payment_date' => '2024-05-30', 'payment_method' => 'Bank Transfer', 'payment_reference' => 'BT-2024-003', 'notes' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Settings (single row)
        Setting::create([
            'hotel_name' => 'Grand Creek Hotel & Resort',
            'currency' => 'PHP',
            'low_stock_threshold' => 30,
            'bill_alert_days' => 7,
            'notifications' => [
                'low_stock' => true,
                'bill_due' => true,
                'overdue_bill' => true,
                'purchase_approval' => true,
            ],
        ]);
    }
}
