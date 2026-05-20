<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $query = Payroll::with('employee.department');

        // Filter by period
        if ($request->filled('month') && $request->filled('year')) {
            $month = $request->month;
            $year = $request->year;
            $query->whereYear('period_start', $year)->whereMonth('period_start', $month);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $payrolls = $query->orderByDesc('period_start')->orderByDesc('created_at')->paginate(15);

        $employees = Employee::where('status', 'active')->orderBy('last_name')->get();
        $stats = [
            'total_paid'  => Payroll::where('status', 'paid')->sum('net_pay'),
            'total_pending' => Payroll::where('status', 'pending')->sum('net_pay'),
            'total_draft' => Payroll::where('status', 'draft')->sum('net_pay'),
            'employee_count' => Payroll::whereIn('status', ['draft', 'pending', 'paid'])
                ->distinct('employee_id')->count('employee_id'),
        ];

        return view('payrolls.index', compact('payrolls', 'employees', 'stats'));
    }

    public function create()
    {
        $employees = Employee::where('status', 'active')->orderBy('last_name')->get();
        $lastPeriod = Payroll::orderByDesc('period_end')->first();
        $defaultStart = $lastPeriod ? $lastPeriod->period_end->addDay() : now()->startOfMonth();
        $defaultEnd = (clone $defaultStart)->addDays(14);

        return view('payrolls.create', compact('employees', 'defaultStart', 'defaultEnd'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'  => 'required|exists:employees,id',
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
            'work_days'    => 'required|integer|min:1|max:31',
            'gross_pay'    => 'required|numeric|min:0',
            'deductions'   => 'required|numeric|min:0',
            'net_pay'      => 'required|numeric|min:0',
            'notes'        => 'nullable|string',
        ]);

        $validated['status'] = 'draft';

        Payroll::create($validated);

        return redirect()->route('payrolls.index')
            ->with('success', 'Payroll record created successfully.');
    }

    public function show(Payroll $payroll)
    {
        $payroll->load('employee.department');
        return view('payrolls.show', compact('payroll'));
    }

    public function edit(Payroll $payroll)
    {
        $employees = Employee::where('status', 'active')->orderBy('last_name')->get();
        return view('payrolls.edit', compact('payroll', 'employees'));
    }

    public function update(Request $request, Payroll $payroll)
    {
        $validated = $request->validate([
            'employee_id'  => 'required|exists:employees,id',
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
            'work_days'    => 'required|integer|min:1|max:31',
            'gross_pay'    => 'required|numeric|min:0',
            'deductions'   => 'required|numeric|min:0',
            'net_pay'      => 'required|numeric|min:0',
            'notes'        => 'nullable|string',
        ]);

        $payroll->update($validated);

        return redirect()->route('payrolls.index')
            ->with('success', 'Payroll record updated successfully.');
    }

    public function destroy(Payroll $payroll)
    {
        $payroll->delete();
        return redirect()->route('payrolls.index')
            ->with('success', 'Payroll record deleted.');
    }

    public function approve(Payroll $payroll)
    {
        if ($payroll->status !== 'draft') {
            return back()->with('error', 'Only draft payrolls can be approved.');
        }

        $payroll->update(['status' => 'pending']);
        return back()->with('success', 'Payroll approved and moved to pending payment.');
    }

    public function pay(Payroll $payroll)
    {
        if ($payroll->status !== 'pending') {
            return back()->with('error', 'Only pending payrolls can be marked as paid.');
        }

        $payroll->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);
        return back()->with('success', 'Payroll marked as paid.');
    }

    public function batchCreate(Request $request)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
            'work_days'    => 'required|integer|min:1|max:31',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $employees = Employee::whereIn('id', $request->employee_ids)->where('status', 'active')->get();
        $count = 0;

        DB::transaction(function () use ($employees, $request, &$count) {
            foreach ($employees as $emp) {
                $dailyRate = $emp->salary / 22; // ~22 working days/month
                $gross = round($dailyRate * $request->work_days, 2);
                $deductions = round($gross * 0.10, 2); // 10% standard deduction
                $net = $gross - $deductions;

                Payroll::create([
                    'employee_id'  => $emp->id,
                    'period_start' => $request->period_start,
                    'period_end'   => $request->period_end,
                    'work_days'    => $request->work_days,
                    'gross_pay'    => $gross,
                    'deductions'   => $deductions,
                    'net_pay'      => $net,
                    'status'       => 'draft',
                ]);
                $count++;
            }
        });

        return redirect()->route('payrolls.index')
            ->with('success', "Payroll generated for {$count} employee(s).");
    }
}
