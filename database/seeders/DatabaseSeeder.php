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
        // ─── Admin User ───
        User::create([
            'name' => 'Admin',
            'email' => 'admin@grandcreek.com',
            'password' => Hash::make('admin123'),
        ]);

        // ─── Departments ───
        $deptIds = [];
        foreach ([
            ['name' => 'Front Office', 'description' => 'Reception, concierge, and guest services', 'manager' => 'Maria Santos'],
            ['name' => 'Housekeeping', 'description' => 'Room cleaning, laundry, and linen management', 'manager' => 'Juan Dela Cruz'],
            ['name' => 'Food & Beverage', 'description' => 'Restaurant, bar, and room service', 'manager' => 'Ana Reyes'],
            ['name' => 'Kitchen', 'description' => 'Food preparation and cooking operations', 'manager' => 'Carlos Garcia'],
            ['name' => 'Maintenance', 'description' => 'Facility repairs, electrical, and plumbing', 'manager' => 'Roberto Cruz'],
            ['name' => 'Laundry', 'description' => 'Guest linen and uniform cleaning', 'manager' => 'Elena Torres'],
            ['name' => 'Security', 'description' => 'Property surveillance and guest safety', 'manager' => 'Miguel Ramos'],
            ['name' => 'Administration', 'description' => 'Office management and HR', 'manager' => 'Carmen Lopez'],
        ] as $d) {
            $deptIds[$d['name']] = Department::create($d)->id;
        }

        // ─── Suppliers ───
        $supplierIds = [];
        foreach ([
            ['name' => 'Premium Linens Inc.', 'contact_person' => 'John Smith', 'phone' => '+63 917 123 4567', 'email' => 'john@premiumlinens.com', 'total_purchases' => 450000],
            ['name' => 'Spa Essentials Co.', 'contact_person' => 'Mary Johnson', 'phone' => '+63 918 234 5678', 'email' => 'mary@spaessentials.com', 'total_purchases' => 280000],
            ['name' => 'Maintenance Hub', 'contact_person' => 'Robert Brown', 'phone' => '+63 919 345 6789', 'email' => 'robert@maintenancehub.com', 'total_purchases' => 320000],
            ['name' => 'Fresh Foods Distributor', 'contact_person' => 'Lisa Davis', 'phone' => '+63 920 456 7890', 'email' => 'lisa@freshfoods.com', 'total_purchases' => 850000],
            ['name' => 'Beverage Solutions', 'contact_person' => 'David Wilson', 'phone' => '+63 921 567 8901', 'email' => 'david@beveragesolutions.com', 'total_purchases' => 380000],
            ['name' => 'Hotel Furnishings Ltd.', 'contact_person' => 'Sarah Martinez', 'phone' => '+63 922 678 9012', 'email' => 'sarah@hotelfurnishings.com', 'total_purchases' => 620000],
        ] as $s) {
            $supplierIds[$s['name']] = Supplier::create($s)->id;
        }

        $hk = $deptIds['Housekeeping'];
        $kitchen = $deptIds['Kitchen'];
        $fnb = $deptIds['Food & Beverage'];
        $maint = $deptIds['Maintenance'];
        $laundry = $deptIds['Laundry'];
        $admin = $deptIds['Administration'];
        $fo = $deptIds['Front Office'];

        $pl = $supplierIds['Premium Linens Inc.'];
        $sec = $supplierIds['Spa Essentials Co.'];
        $mh = $supplierIds['Maintenance Hub'];
        $ffd = $supplierIds['Fresh Foods Distributor'];
        $bs = $supplierIds['Beverage Solutions'];
        $hfl = $supplierIds['Hotel Furnishings Ltd.'];

        // ─── Items ───
        $itemData = [
            ['Bed Sheets (Queen)', 'Linen', $hk, $pl, 120, 50, 'pcs', 450, 0, null],
            ['Bed Sheets (King)', 'Linen', $hk, $pl, 80, 30, 'pcs', 550, 0, null],
            ['Pillow Cases', 'Linen', $hk, $pl, 200, 80, 'pcs', 120, 0, null],
            ['Bath Towels', 'Linen', $hk, $pl, 180, 80, 'pcs', 180, 0, null],
            ['Hand Towels', 'Linen', $hk, $pl, 150, 60, 'pcs', 95, 0, null],
            ['Shampoo (Mini)', 'Toiletries', $hk, $sec, 500, 200, 'bottles', 35, 0, '2026-12-31'],
            ['Conditioner (Mini)', 'Toiletries', $hk, $sec, 400, 150, 'bottles', 35, 0, '2026-12-31'],
            ['Soap Bars', 'Toiletries', $hk, $sec, 600, 200, 'pcs', 20, 0, '2027-06-30'],
            ['Toilet Paper (Case)', 'Supplies', $hk, $sec, 45, 20, 'cases', 450, 0, null],
            ['All-Purpose Cleaner', 'Chemicals', $hk, $mh, 12, 20, 'liters', 280, 0, null],          // LOW STOCK
            ['Bleach', 'Chemicals', $laundry, $mh, 8, 15, 'liters', 180, 0, null],                     // LOW STOCK
            ['Rice (Premium, 50kg)', 'Food', $kitchen, $ffd, 10, 5, 'bags', 2750, 0, null],
            ['Chicken Breast', 'Food', $kitchen, $ffd, 35, 40, 'kg', 280, 0, '2026-05-25'],            // LOW + expiring
            ['Cooking Oil', 'Food', $kitchen, $ffd, 25, 15, 'liters', 220, 0, null],
            ['Coffee Beans (Arabica)', 'Beverage', $fnb, $bs, 18, 15, 'kg', 850, 0, '2026-08-31'],    // LOW STOCK
            ['Bottled Water (Case)', 'Beverage', $fnb, $bs, 60, 25, 'cases', 240, 0, null],
            ['Wine Glasses', 'Glassware', $fnb, $hfl, 95, 50, 'pcs', 95, 0, null],
            ['LED Light Bulbs', 'Electrical', $maint, $mh, 40, 30, 'pcs', 120, 0, null],
            ['White Paint (Gallon)', 'Maintenance', $maint, $mh, 8, 10, 'gal', 650, 0, null],          // LOW STOCK
            ['Printer Paper (Ream)', 'Office', $admin, $hfl, 25, 10, 'reams', 250, 0, null],
            ['Ballpen (Box)', 'Office', $admin, $hfl, 15, 10, 'boxes', 180, 0, null],
            ['USB-C Cables', 'Electronics', $fo, $hfl, 5, 10, 'pcs', 350, 0, null],                   // LOW STOCK
            ['Pens (Promo)', 'Office', $fo, $hfl, 200, 50, 'pcs', 15, 25, null],
        ];

        $itemIds = [];
        foreach ($itemData as $i => $row) {
            $item = Item::create([
                'name'          => $row[0],
                'category'      => $row[1],
                'department_id' => $row[2],
                'supplier_id'   => $row[3],
                'current_stock' => $row[4],
                'min_stock'     => $row[5],
                'unit'          => $row[6],
                'purchase_cost' => $row[7],
                'selling_price' => $row[8],
                'expiry_date'   => $row[9],
            ]);
            $itemIds[$row[0]] = $item->id;
        }

        // ─── Movements ───
        $movements = [
            // Housekeeping linen stock-up
            [1, 'IN', 60,  null, $hk, 'Monthly replenishment', 'Admin',   '2026-04-01 09:00:00'],
            [3, 'IN', 100, null, $hk, 'Monthly replenishment', 'Admin',   '2026-04-01 09:00:00'],
            [4, 'IN', 80,  null, $hk, 'Monthly replenishment', 'Admin',   '2026-04-01 09:00:00'],
            // Usage — towels used in rooms
            [4, 'OUT', 20, $hk,  null, 'Daily room service',   'Juan',    '2026-04-05 14:30:00'],
            [5, 'OUT', 15, $hk,  null, 'Daily room service',   'Juan',    '2026-04-05 14:30:00'],
            // Food deliveries
            [12,'IN', 5,  null, $kitchen, 'Weekly rice delivery',   'Carlos',  '2026-04-03 08:00:00'],
            [13,'IN', 30, null, $kitchen, 'Daily chicken delivery', 'Carlos',  '2026-04-03 08:00:00'],
            [14,'IN', 20, null, $kitchen, 'Cooking oil restock',    'Carlos',  '2026-04-03 08:00:00'],
            // Food usage
            [13,'OUT', 15, $kitchen, null, 'Used for lunch service', 'Carlos',  '2026-04-04 15:00:00'],
            // Coffee consumed
            [15,'OUT', 7,  $fnb,    null, 'Restaurant consumption',  'Ana',     '2026-04-06 11:00:00'],
            // Cross-dept transfer: coffee to kitchen
            [15,'TRANSFER', 5, $fnb, $kitchen, 'Kitchen needed extra', 'Ana',   '2026-04-08 10:00:00'],
            // Maintenance usage
            [18,'OUT', 10, $maint,  null, 'Lobby bulb replacement',  'Roberto', '2026-04-07 16:00:00'],
            [19,'OUT', 2,  $maint,  null, 'Hallway repaint',         'Roberto', '2026-04-07 16:00:00'],
            // Cleaning supplies usage
            [10,'OUT', 5,  $hk,     null, 'Daily cleaning',          'Juan',    '2026-04-09 07:00:00'],
            [11,'OUT', 4,  $laundry, null, 'Linen bleaching',        'Elena',   '2026-04-09 08:00:00'],
        ];

        foreach ($movements as $m) {
            Movement::create([
                'item_id'         => $m[0],
                'type'            => $m[1],
                'quantity'        => $m[2],
                'from_department' => $m[3],
                'to_department'   => $m[4],
                'notes'           => $m[5],
                'user'            => $m[6],
                'date'            => $m[7],
            ]);
        }

        // ─── Bills ───
        $bills = [
            ['Electricity', 'Meralco',              'MER-12345', 87500.00, '2026-05-25', 'Pending',  'April 2026',  null, null, null],
            ['Electricity', 'Meralco',              'MER-12345', 92300.00, '2026-04-25', 'Paid',     'March 2026',  '2026-04-20', 'Bank Transfer', 'BT-2026-012'],
            ['Water',      'Manila Water',           'MW-67890',  14200.00, '2026-05-15', 'Pending',  'April 2026',  null, null, null],
            ['Water',      'Manila Water',           'MW-67890',  12800.00, '2026-04-15', 'Paid',     'March 2026',  '2026-04-10', 'Bank Transfer', 'BT-2026-010'],
            ['Internet',   'PLDT Fiber',             'PLDT-54321', 8500.00, '2026-05-20', 'Pending',  'May 2026',    null, null, null],
            ['Internet',   'PLDT Fiber',             'PLDT-54321', 8500.00, '2026-04-20', 'Paid',     'April 2026',  '2026-04-15', 'Auto-Debit',    'AD-2026-005'],
            ['Telephone',  'Globe Telecom',          'GLOBE-98765',5200.00, '2026-04-30', 'Overdue',  'March 2026',  null, null, null],
            ['Telephone',  'Globe Telecom',          'GLOBE-98765',4800.00, '2026-03-31', 'Overdue',  'February 2026',null,null, null],
            ['Gas (LPG)',  'Petron Corp',            'PET-11111', 18000.00, '2026-05-28', 'Pending',  'May 2026',    null, null, null],
            ['Gas (LPG)',  'Petron Corp',            'PET-11111', 15500.00, '2026-04-28', 'Paid',     'April 2026',  '2026-04-22', 'Check',         'CHK-2026-008'],
            ['Maintenance','Elite Facility Services','EFS-22222', 45000.00, '2026-05-30', 'Pending',  'May 2026',    null, null, null],
            ['Insurance',  'Pioneer Insurance',      'PI-33333',  55000.00, '2026-05-01', 'Paid',     'Q2 2026',     '2026-04-28', 'Bank Transfer', 'BT-2026-014'],
            ['Rent',       'GCHR Properties Inc.',   'GCHR-44444',250000.00,'2026-05-01', 'Paid',     'May 2026',    '2026-04-28', 'Bank Transfer', 'BT-2026-015'],
            ['Rent',       'GCHR Properties Inc.',   'GCHR-44444',250000.00,'2026-06-01', 'Pending',  'June 2026',   null, null, null],
            ['Waste Mgmt', 'EcoWaste Solutions',     'EWS-55555',  8500.00, '2026-05-10', 'Pending',  'April 2026',  null, null, null],
        ];

        foreach ($bills as $b) {
            Bill::create([
                'type'             => $b[0],
                'provider'         => $b[1],
                'account_number'   => $b[2],
                'amount'           => $b[3],
                'due_date'         => $b[4],
                'status'           => $b[5],
                'billing_period'   => $b[6],
                'payment_date'     => $b[7],
                'payment_method'   => $b[8],
                'payment_reference'=> $b[9],
            ]);
        }

        // ─── Settings ───
        Setting::create([
            'hotel_name'        => 'Grand Creek Hotel & Resort',
            'currency'          => 'PHP',
            'low_stock_threshold' => 20,
            'bill_alert_days'    => 7,
            'notifications'      => [
                'low_stock'        => true,
                'bill_due'         => true,
                'overdue_bill'     => true,
                'purchase_approval'=> false,
            ],
        ]);
    }
}
