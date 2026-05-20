<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('department')
            ->orderBy('last_name')
            ->paginate(15);

        $departments = Department::where('active', true)->get();
        $stats = [
            'total'    => Employee::count(),
            'active'   => Employee::where('status', 'active')->count(),
            'departments' => Employee::selectRaw('department_id, count(*) as cnt')
                ->where('status', 'active')
                ->groupBy('department_id')
                ->get(),
        ];

        return view('employees.index', compact('employees', 'departments', 'stats'));
    }

    public function create()
    {
        $departments = Department::where('active', true)->get();
        $nextId = $this->nextEmployeeId();
        return view('employees.create', compact('departments', 'nextId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'       => ['required', 'string', 'max:20', Rule::unique('employees')],
            'department_id'     => 'nullable|exists:departments,id',
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'required|string|max:100',
            'position'          => 'required|string|max:100',
            'hire_date'         => 'required|date',
            'salary'            => 'required|numeric|min:0',
            'email'             => 'nullable|email|max:255',
            'phone'             => 'nullable|string|max:50',
            'address'           => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:200',
            'emergency_phone'   => 'nullable|string|max:50',
            'status'            => 'required|in:active,inactive,terminated',
        ]);

        Employee::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee added successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load('department');
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::where('active', true)->get();
        return view('employees.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_id'       => ['required', 'string', 'max:20', Rule::unique('employees')->ignore($employee->id)],
            'department_id'     => 'nullable|exists:departments,id',
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'required|string|max:100',
            'position'          => 'required|string|max:100',
            'hire_date'         => 'required|date',
            'salary'            => 'required|numeric|min:0',
            'email'             => 'nullable|email|max:255',
            'phone'             => 'nullable|string|max:50',
            'address'           => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:200',
            'emergency_phone'   => 'nullable|string|max:50',
            'status'            => 'required|in:active,inactive,terminated',
        ]);

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee removed successfully.');
    }

    private function nextEmployeeId(): string
    {
        $last = Employee::orderBy('id', 'desc')->first();
        $num = $last ? (int) substr($last->employee_id, 4) + 1 : 1;
        return 'EMP-' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
}
