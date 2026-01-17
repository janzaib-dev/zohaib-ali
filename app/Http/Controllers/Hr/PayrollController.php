<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Employee;
use App\Models\Hr\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PayrollController extends Controller
{
    public function index()
    {
        if (! auth()->user()->can('hr.payroll.view')) {
            abort(403, 'Unauthorized action.');
        }
        $payrolls = Payroll::with('employee')->latest()->get();
        $employees = Employee::all();

        return view('hr.payroll.index', compact('payrolls', 'employees'));
    }

    public function generate(Request $request)
    {
        if (! auth()->user()->can('hr.payroll.create')) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:hr_employees,id',
            'month' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $employee = Employee::findOrFail($request->employee_id);

        // Check if payroll already exists for this month
        $exists = Payroll::where('employee_id', $employee->id)->where('month', $request->month)->exists();
        if ($exists) {
            return response()->json(['errors' => ['Payroll already generated for this month.']]);
        }

        Payroll::create([
            'employee_id' => $employee->id,
            'month' => $request->month,
            'basic_salary' => $employee->basic_salary,
            'net_salary' => $employee->basic_salary, // Logic for deductions/bonuses can be added
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => 'Payroll generated successfully.',
            'reload' => true,
        ]);
    }

    public function markPaid($id)
    {
        if (! auth()->user()->can('hr.payroll.edit')) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $payroll = Payroll::findOrFail($id);
        $payroll->update(['status' => 'paid']);

        return response()->json([
            'success' => 'Payroll marked as paid successfully.',
        ]);
    }

    public function destroy($id)
    {
        if (! auth()->user()->can('hr.payroll.delete')) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $payroll = Payroll::findOrFail($id);
        $payroll->delete();

        return response()->json([
            'success' => 'Payroll deleted successfully.',
        ]);
    }
}
