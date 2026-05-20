<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Movement;
use App\Models\Bill;
use App\Models\Setting;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\RoomType;
use App\Models\Room;
use App\Models\Booking;
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
            'email_verified_at' => now(),
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

        // ─── Employees ───
        $employees = [
            ['EMP-001', $deptIds['Front Office'], 'Maria', 'Santos',      'Front Desk Manager',  '2024-01-15', 35000,  'maria@grandcreek.com',     '+63 912 111 0001'],
            ['EMP-002', $deptIds['Front Office'], 'Jose',  'Reyes',       'Concierge',           '2024-02-01', 22000,  'jose@grandcreek.com',      '+63 912 111 0002'],
            ['EMP-003', $deptIds['Housekeeping'], 'Juan',  'Dela Cruz',   'Head Housekeeper',    '2023-06-01', 28000,  'juan@grandcreek.com',      '+63 912 111 0003'],
            ['EMP-004', $deptIds['Housekeeping'], 'Elena', 'Torres',      'Room Attendant',      '2024-03-01', 18000,  'elena@grandcreek.com',     '+63 912 111 0004'],
            ['EMP-005', $deptIds['Kitchen'],      'Carlos','Garcia',      'Head Chef',           '2023-08-15', 45000,  'carlos@grandcreek.com',    '+63 912 111 0005'],
            ['EMP-006', $deptIds['Kitchen'],      'Ana',   'Mendoza',     'Sous Chef',           '2024-01-01', 30000,  'ana@grandcreek.com',       '+63 912 111 0006'],
            ['EMP-007', $deptIds['Food & Beverage'], 'Rosa', 'Villanueva','Restaurant Manager',  '2023-11-01', 32000,  'rosa@grandcreek.com',      '+63 912 111 0007'],
            ['EMP-008', $deptIds['Food & Beverage'], 'Ben',  'Lim',       'Bartender',           '2024-04-01', 20000,  'ben@grandcreek.com',       '+63 912 111 0008'],
            ['EMP-009', $deptIds['Maintenance'],  'Roberto','Cruz',       'Maintenance Head',    '2023-05-01', 30000,  'roberto@grandcreek.com',   '+63 912 111 0009'],
            ['EMP-010', $deptIds['Security'],     'Miguel','Ramos',       'Security Chief',      '2023-09-01', 27000,  'miguel@grandcreek.com',    '+63 912 111 0010'],
            ['EMP-011', $deptIds['Administration'],'Carmen','Lopez',       'Admin Manager',       '2023-04-01', 38000,  'carmen@grandcreek.com',    '+63 912 111 0011'],
            ['EMP-012', $deptIds['Laundry'],      'Pedro', 'Gonzales',    'Laundry Supervisor',  '2024-02-15', 22000,  'pedro@grandcreek.com',     '+63 912 111 0012'],
            ['EMP-013', $deptIds['Front Office'],  'Liza',  'Marcos',      'Receptionist',        '2024-05-01', 18000,  'liza@grandcreek.com',      '+63 912 111 0013'],
            ['EMP-014', $deptIds['Housekeeping'], 'Tonyo', 'Santiago',    'Room Attendant',      '2024-05-15', 18000,  'tonyo@grandcreek.com',     '+63 912 111 0014'],
            ['EMP-015', $deptIds['Administration'],'Diana', 'Fernandez',   'HR Coordinator',      '2024-03-15', 25000,  'diana@grandcreek.com',     '+63 912 111 0015'],
        ];
        foreach ($employees as $e) {
            Employee::create([
                'employee_id'       => $e[0],
                'department_id'     => $e[1],
                'first_name'        => $e[2],
                'last_name'         => $e[3],
                'position'          => $e[4],
                'hire_date'         => $e[5],
                'salary'            => $e[6],
                'email'             => $e[7],
                'phone'             => $e[8],
                'status'            => 'active',
            ]);
        }

        // ─── Payroll Records ───
        $payrollEmployees = Employee::whereIn('employee_id', ['EMP-001','EMP-002','EMP-005','EMP-007','EMP-009','EMP-011'])->get();
        $periods = [
            ['2026-04-01', '2026-04-15', 12],
            ['2026-04-16', '2026-04-30', 13],
        ];
        foreach ($periods as $period) {
            foreach ($payrollEmployees as $emp) {
                $daily = $emp->salary / 22;
                $gross = round($daily * $period[2], 2);
                $deductions = round($gross * 0.10, 2);
                $net = $gross - $deductions;
                Payroll::create([
                    'employee_id'  => $emp->id,
                    'period_start' => $period[0],
                    'period_end'   => $period[1],
                    'work_days'    => $period[2],
                    'gross_pay'    => $gross,
                    'deductions'   => $deductions,
                    'net_pay'      => $net,
                    'status'       => $period[1] === '2026-04-15' ? 'paid' : 'pending',
                    'paid_at'      => $period[1] === '2026-04-15' ? '2026-04-14 17:00:00' : null,
                ]);
            }
        }
        // Add one draft record
        Payroll::create([
            'employee_id'  => Employee::where('employee_id', 'EMP-003')->first()->id,
            'period_start' => '2026-05-01',
            'period_end'   => '2026-05-15',
            'work_days'    => 11,
            'gross_pay'    => 14000,
            'deductions'   => 1400,
            'net_pay'      => 12600,
            'status'       => 'draft',
        ]);

        // ─── Room Types ───
        $roomTypesData = [
            [
                'name' => 'Standard Double',
                'description' => 'Comfortable room with two double beds, perfect for families or groups.',
                'capacity' => 4, 'price_per_night' => 1800,
                'amenities' => ['Air Conditioning', 'Flat-screen TV', 'Mini Bar', 'Wi-Fi', 'In-room Safe'],
                'icon' => '🛌',
            ],
            [
                'name' => 'Deluxe King',
                'description' => 'Spacious room with a king-sized bed, premium amenities, and city view.',
                'capacity' => 2, 'price_per_night' => 2800,
                'amenities' => ['Air Conditioning', 'Flat-screen TV', 'Mini Bar', 'Wi-Fi', 'In-room Safe', 'Bathtub', 'City View'],
                'icon' => '👑',
            ],
            [
                'name' => 'Suite',
                'description' => 'Luxurious suite with separate living area, premium furnishings, and panoramic views.',
                'capacity' => 4, 'price_per_night' => 4500,
                'amenities' => ['Air Conditioning', 'Flat-screen TV', 'Mini Bar', 'Wi-Fi', 'In-room Safe', 'Bathtub', 'Living Area', 'Ocean View', 'Kitchenette'],
                'icon' => '🏖️',
            ],
            [
                'name' => 'Single Economy',
                'description' => 'Budget-friendly room with a single bed, ideal for solo travelers.',
                'capacity' => 1, 'price_per_night' => 1200,
                'amenities' => ['Air Conditioning', 'Flat-screen TV', 'Wi-Fi'],
                'icon' => '🧳',
            ],
        ];
        foreach ($roomTypesData as $rt) {
            RoomType::create($rt);
        }

        // ─── Rooms ───
        $rtIds = RoomType::pluck('id', 'name');
        // Floor 1: Front desk area, Standard & Single rooms
        $roomsData = [
            ['101', 1, $rtIds['Single Economy'], 'available'],
            ['102', 1, $rtIds['Single Economy'], 'available'],
            ['103', 1, $rtIds['Standard Double'], 'available'],
            ['104', 1, $rtIds['Standard Double'], 'occupied'],
            // Floor 2: Standard rooms
            ['201', 2, $rtIds['Standard Double'], 'available'],
            ['202', 2, $rtIds['Standard Double'], 'occupied'],
            ['203', 2, $rtIds['Standard Double'], 'maintenance'],
            ['204', 2, $rtIds['Standard Double'], 'available'],
            ['205', 2, $rtIds['Standard Double'], 'available'],
            // Floor 3: Deluxe King
            ['301', 3, $rtIds['Deluxe King'], 'available'],
            ['302', 3, $rtIds['Deluxe King'], 'occupied'],
            ['303', 3, $rtIds['Deluxe King'], 'available'],
            ['304', 3, $rtIds['Deluxe King'], 'occupied'],
            ['305', 3, $rtIds['Deluxe King'], 'cleaning'],
            // Floor 4: Suites
            ['401', 4, $rtIds['Suite'], 'available'],
            ['402', 4, $rtIds['Suite'], 'occupied'],
            ['403', 4, $rtIds['Suite'], 'available'],
            ['404', 4, $rtIds['Suite'], 'occupied'],
            ['405', 4, $rtIds['Suite'], 'available'],
            // Floor 5: Mix
            ['501', 5, $rtIds['Deluxe King'], 'available'],
            ['502', 5, $rtIds['Deluxe King'], 'available'],
            ['503', 5, $rtIds['Suite'], 'available'],
            ['504', 5, $rtIds['Suite'], 'available'],
        ];
        foreach ($roomsData as $r) {
            Room::create([
                'room_number'  => $r[0],
                'floor'        => $r[1],
                'room_type_id' => $r[2],
                'status'       => $r[3],
            ]);
        }

        // ─── Bookings ───
        $bookingRooms = Room::pluck('id', 'room_number');
        $bookingsData = [
            ['Marites Santos',   'marites@email.com',   '09171234567', '101', '2026-05-21', '2026-05-23', 2, 1, 'confirmed', 3600,  3600,  'cash'],
            ['John Doe',         'john@email.com',      '09182345678', '103', '2026-05-22', '2026-05-25', 2, 0, 'pending',   5400,  2000,  'gcash'],
            ['Emily Tan',        'emily@email.com',     '09193456789', '301', '2026-05-22', '2026-05-24', 2, 0, 'confirmed', 5600,  5600,  'card'],
            ['Robert Lim',       'robert@email.com',    '09204567890', '401', '2026-05-21', '2026-05-21', 1, 0, 'checked_in',4500,  4500,  'cash'],
            ['Sarah Gonzales',   'sarah@email.com',     '09215678901', '501', '2026-05-20', '2026-05-22', 2, 2, 'checked_in',9000,  5000,  'gcash'],
            ['Mike Reyes',       'mike@email.com',      '09226789012', '504', '2026-05-23', '2026-05-26', 3, 0, 'pending',  13500, 10000,  'bank'],
            ['Ana Cruz',         'ana@email.com',       '09237890123', '203', '2026-05-25', '2026-05-27', 2, 0, 'pending',   3600,     0,  null],
            ['Carlos Villanueva','carlos@email.com',    '09248901234', '304', '2026-05-19', '2026-05-21', 2, 0, 'checked_out',5600,  5600, 'cash'],
            ['Jean Francisco',   'jean@email.com',      '09259012345', '102', '2026-05-18', '2026-05-20', 1, 0, 'checked_out',2400,  2400, 'cash'],
        ];
        foreach ($bookingsData as $b) {
            Booking::create([
                'guest_name'    => $b[0],
                'guest_email'   => $b[1],
                'guest_phone'   => $b[2],
                'room_id'       => $bookingRooms[$b[3]],
                'check_in'      => $b[4],
                'check_out'     => $b[5],
                'adults'        => $b[6],
                'children'      => $b[7],
                'status'        => $b[8],
                'total_amount'  => $b[9],
                'paid_amount'   => $b[10],
                'payment_method'=> $b[11],
            ]);
        }
    }
}
