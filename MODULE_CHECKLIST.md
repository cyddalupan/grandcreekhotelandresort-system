# Grand Creek Hotel & Resort — Module Checklist

> Status: ✅ = Complete | 🔨 = In Progress | ❌ = Not Started

---

## 1️⃣ Dashboard
- **Route:** `/dashboard` — DashboardController@index
- **Backend:** ✅ Stats queries: total items, low stock count, total bills, pending bills, monthly expenses, dept spending, recent movements, upcoming bills
- **Views:** ✅ Dashboard stats cards, alerts table (low stock, overdue), dept spending progress bars, recent movements & bills list
- **Status:** ✅ **Complete**

---

## 2️⃣ Departments
- **Model:** Department.php — id, name, description, budget, created_at, updated_at
- **Backend:** ✅ Full CRUD (index, create, store, edit, update, destroy)
- **Views:** ✅ List table + create/edit form
- **Status:** ✅ **Complete**

---

## 3️⃣ Inventory (Items)
- **Model:** Item.php — id, name, sku, description, category, quantity, min_stock, unit, price, supplier_id, department_id, created_at, updated_at
- **Backend:** ✅ Full CRUD (index, create, store, show, edit, update, destroy)
- **Views:** ✅ List table with stock indicators + create/edit form with supplier/department dropdowns
- **Status:** ✅ **Complete**

---

## 4️⃣ Bills
- **Model:** Bill.php — id, bill_number, description, amount, due_date, status (pending/paid/overdue/cancelled), category, supplier_id, department_id, paid_at, created_at, updated_at
- **Backend:** ✅ Full CRUD + `pay` action (mark as paid)
- **Views:** ✅ List table with status badges + create/edit form + pay modal
- **Status:** ✅ **Complete**

---

## 5️⃣ Suppliers
- **Model:** Supplier.php — id, name, contact_person, email, phone, address, created_at, updated_at
- **Backend:** ✅ Full CRUD (index, create, store, show, edit, update, destroy)
- **Views:** ✅ List table + create/edit form + show detail
- **Status:** ✅ **Complete**

---

## 6️⃣ Stock Movements
- **Model:** Movement.php — id, item_id, type (in/out), quantity, reference, notes, user_id, created_at
- **Backend:** ✅ Full CRUD (index, create, store) — tracks item inventory changes
- **Views:** ✅ List table with in/out indicators + create form
- **Status:** ✅ **Complete**

---

## 7️⃣ Reports
- **Model:** None (aggregated queries)
- **Backend:** ✅ Index view with filtered data
- **Views:** ✅ Table view (read-only)
- **Status:** 🟡 **Basic — needs enhancement** (download CSV/PDF, chart visualizations, date range filters)

---

## 8️⃣ Settings
- **Model:** Setting.php — id, key, value, created_at, updated_at
- **Backend:** ✅ View + update (single form with all settings)
- **Views:** ✅ Settings form (hotel name, address, phone, email, tax rate, currency)
- **Status:** ✅ **Complete**

---

## 9️⃣ Auth & Users
- **Models:** User.php — id, name, email, password, role (admin/staff), created_at
- **Backend:** ✅ Login, Register, Logout, Password Reset, Profile Edit, Email Verification
- **Views:** ✅ Breeze starter kit (login, register, forgot password, reset password, profile)
- **Status:** 🟡 **Needs role-based access** (admin vs staff permissions)

---

## 🔟 HR / Employees ✅
- **Model:** Employee.php — id, employee_id, department_id, first_name, last_name, position, hire_date, salary, email, phone, address, emergency_contact, emergency_phone, status (active/inactive/terminated)
- **Backend:** ✅ Full CRUD (index, create, store, show, edit, update, destroy) + auto-generate EMP-XXX IDs
- **Views:** ✅ List table with stats cards + create/edit/show forms with profile initials
- **Migrations:** ✅ `create_employees_table`
- **Relationships:** Employee belongsTo Department
- **Seeds:** ✅ 15 employees across all 8 departments with Filipino names and realistic salaries

---

## 1️⃣1️⃣ Payroll ✅
- **Model:** Payroll.php — id, employee_id, period_start, period_end, work_days, gross_pay, deductions, net_pay, status (draft/pending/paid/cancelled), paid_at, notes
- **Backend:** ✅ Full CRUD + batch generate, approve (draft→pending), pay (pending→paid)
- **Views:** ✅ List table with period/status/employee filters + create (with auto-calc) + edit + show (pay breakdown) + batch generate modal
- **Migrations:** ✅ `create_payrolls_table`
- **Seeds:** ✅ 13 records (6 paid, 6 pending, 1 draft) across 7 employees
- **Note:** Net pay = gross pay - deductions; auto-calc uses (salary/22) × work_days, 10% standard deduction

---

## 1️⃣2️⃣ Rooms & Room Types ✅
- **Models:** RoomType.php (id, name, description, capacity, price_per_night, amenities JSON, icon, is_active) + Room.php (id, room_number, room_type_id, floor, status [available/occupied/maintenance/cleaning], notes)
- **Backend:** ✅ Full CRUD — RoomTypeController (index, create, store, show, edit, update, destroy w/ protection if rooms exist) + RoomController (index w/ filters/status stats, create, store, edit, update, destroy)
- **Views:** ✅ RoomType card layout (create w/ Alpine.js amenity tags, edit, show w/ rooms grid). Room colored card grid (status-coded borders, filters by status/type/floor, 4 stats cards, create, edit, show w/ gradient header)
- **Migrations:** ✅ `create_room_types_table` + `create_rooms_table`
- **Seeds:** ✅ 4 room types (Single Economy ₱1,200, Standard Double ₱1,800, Deluxe King ₱2,800, Suite ₱4,500) + 24 rooms across 5 floors (varied statuses)

---

## 1️⃣3️⃣ Booking / Reservations
- **Model:** ✅ Booking.php — id, guest_name, guest_email, guest_phone, room_id (FK→rooms), check_in, check_out, adults, children, status (pending/confirmed/checked_in/checked_out/cancelled), total_amount, paid_amount, payment_method, notes
- **Backend:** ✅ Full CRUD with availability check (overlapping dates blocked), status workflow actions (confirm → check_in → check_out → cancel), AJAX available-rooms endpoint, auto room status update (occupied/cleaning)
- **Views:** ✅ Index with stats + filters + table, create with AJAX room picker, edit with current room preserved, show with action buttons
- **Migrations:** ✅ `create_bookings_table` done
- **Seed Data:** ✅ 9 sample bookings (varying statuses)
- **Payments:** ❌ Need PayMongo/GCash integration

---

## 1️⃣4️⃣ Point of Sale (POS)
- **Model:** Sale.php (id, receipt_number, items JSON, subtotal, tax, discount, total, payment_method, tendered_amount, change, user_id, created_at)
- **Backend:** ❌ Need create + receipt generation
- **Views:** ❌ Need POS interface (like a cash register screen)
- **Migrations:** ❌ Need `create_sales_table`

---

## 1️⃣5️⃣ Purchase Orders
- **Model:** PurchaseOrder.php — id, po_number, supplier_id, items JSON, total_amount, status (draft/approved/sent/received/cancelled), notes, created_by, approved_by, created_at
- **Backend:** ❌ Need CRUD + approve/receive workflow
- **Views:** ❌ Need PO form with line items, status timeline
- **Migrations:** ❌ Need `create_purchase_orders_table`

---

## 1️⃣6️⃣ Housekeeping
- **Model:** Housekeeping.php — id, room_id, assigned_to, task_type (cleaning/maintenance/inspection), status (pending/in_progress/completed), notes, scheduled_date, completed_at, created_at
- **Backend:** ❌ Need CRUD + assignment
- **Views:** ❌ Need task board view
- **Migrations:** ❌ Need `create_housekeeping_table`

---

## Summary

| # | Module | Status | Backend | Views | Migrations |
|---|--------|--------|---------|-------|------------|
| 1 | Dashboard | ✅ | Done | Done | — |
| 2 | Departments | ✅ | Done | Done | Done |
| 3 | Inventory | ✅ | Done | Done | Done |
| 4 | Bills | ✅ | Done | Done | Done |
| 5 | Suppliers | ✅ | Done | Done | Done |
| 6 | Movements | ✅ | Done | Done | Done |
| 7 | Reports | 🟡 | Basic | Basic | — |
| 8 | Settings | ✅ | Done | Done | Done |
| 9 | Auth | 🟡 | Basic | Basic | Done |
| 10 | **HR/Employees** | ✅ | Done | Done | Done |
| 11 | **Payroll** | ✅ | Done | Done | Done |
| 12 | **Rooms / Types** | ✅ | Done | Done | Done | 24 rooms, 4 types
| 13 | **Bookings** | ✅ | Done | Done | Done | 9 sample bookings |
| 14 | **POS** | ❌ | — | — | — |
| 15 | **Purchase Orders** | ❌ | — | — | — |
| 16 | **Housekeeping** | ❌ | — | — | — |

---

## Data Seed Plan
- ✅ Departments: 6 departments (Front Office, Housekeeping, F&B, Maintenance, Admin, Sales)
- ✅ Items: 15-20 realistic hotel inventory items across departments
- ✅ Suppliers: 5 suppliers
- ✅ Bills: 10 bills with mixed statuses
- ✅ Movements: Stock movement history
- ✅ Settings: Hotel info (name, address, etc.)
