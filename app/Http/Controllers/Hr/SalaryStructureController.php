<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Employee;
use App\Models\Hr\SalaryStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SalaryStructureController extends Controller
{
    public function index()
    {
        // Need at least view permission to see the list
        if (! auth()->user()->can('hr.salary.structure.view') && 
            ! auth()->user()->can('hr.salary.structure.create') && 
            ! auth()->user()->can('hr.salary.structure.edit')) {
            abort(403, 'Unauthorized action.');
        }
        $employees = Employee::with(['department', 'designation', 'salaryStructure'])->get();

        // Pass permission flags to view
        $canView = auth()->user()->can('hr.salary.structure.view');
        $canCreate = auth()->user()->can('hr.salary.structure.create');
        $canEdit = auth()->user()->can('hr.salary.structure.edit');

        return view('hr.salary-structure.index', compact('employees', 'canView', 'canCreate', 'canEdit'));
    }

    public function edit(Employee $employee)
    {
        $hasSalaryStructure = $employee->salaryStructure !== null;
        $canView = auth()->user()->can('hr.salary.structure.view');
        $canCreate = auth()->user()->can('hr.salary.structure.create');
        $canEdit = auth()->user()->can('hr.salary.structure.edit');

        // Permission logic:
        // - If employee HAS salary structure: need view or edit permission
        // - If employee DOESN'T have salary structure: need create or edit permission
        if ($hasSalaryStructure) {
            if (! $canView && ! $canEdit) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            if (! $canCreate && ! $canEdit) {
                abort(403, 'Unauthorized action.');
            }
        }

        $salaryStructure = $employee->salaryStructure ?? new SalaryStructure;

        // Determine if form should be read-only
        // Read-only if: has salary structure AND only has view permission (not edit)
        // OR: doesn't have salary structure AND only has view permission (not create or edit)
        $readOnly = false;
        if ($hasSalaryStructure && $canView && !$canEdit) {
            $readOnly = true;
        }

        return view('hr.salary-structure.edit', compact('employee', 'salaryStructure', 'readOnly', 'hasSalaryStructure', 'canCreate', 'canEdit'));
    }

    public function update(Request $request, Employee $employee)
    {
        $hasSalaryStructure = $employee->salaryStructure !== null;
        $canCreate = auth()->user()->can('hr.salary.structure.create');
        $canEdit = auth()->user()->can('hr.salary.structure.edit');

        // Permission logic for update:
        // - If employee HAS salary structure: need edit permission
        // - If employee DOESN'T have salary structure: need create or edit permission
        if ($hasSalaryStructure) {
            if (! $canEdit) {
                return response()->json(['error' => 'Unauthorized action. You need edit permission to modify existing salary structure.'], 403);
            }
        } else {
            if (! $canCreate && ! $canEdit) {
                return response()->json(['error' => 'Unauthorized action. You need create permission to assign salary structure.'], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'salary_type' => 'required|in:salary,commission,both',
            'base_salary' => 'nullable|numeric|min:0',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'sales_target' => 'nullable|numeric|min:0',
            'leave_salary_per_day' => 'nullable|numeric|min:0',
            'commission_tiers' => 'nullable|array',
            'commission_tiers.*.percentage' => 'nullable|numeric|min:0|max:100',
            'commission_tiers.*.upto_amount' => 'nullable|numeric|min:0',
            'allowances' => 'nullable|array',
            'allowances.*.name' => 'required_with:allowances|string',
            'allowances.*.amount' => 'required_with:allowances|numeric|min:0',
            'deductions' => 'nullable|array',
            'deductions.*.name' => 'required_with:deductions|string',
            'deductions.*.amount' => 'required_with:deductions|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        // Filter out empty allowances/deductions
        $allowances = collect($request->allowances ?? [])->filter(function ($item) {
            return ! empty($item['name']) && isset($item['amount']);
        })->values()->toArray();

        $deductions = collect($request->deductions ?? [])->filter(function ($item) {
            return ! empty($item['name']) && isset($item['amount']);
        })->values()->toArray();

        // Filter out empty commission tiers
        $commissionTiers = collect($request->commission_tiers ?? [])->filter(function ($item) {
            return isset($item['percentage']) && isset($item['upto_amount'])
                   && $item['percentage'] > 0 && $item['upto_amount'] > 0;
        })->values()->toArray();

        SalaryStructure::updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'salary_type' => $request->salary_type,
                'base_salary' => $request->base_salary ?? 0,
                'commission_percentage' => $request->commission_percentage,
                'sales_target' => $request->sales_target,
                'commission_tiers' => $commissionTiers ?: null,
                'leave_salary_per_day' => $request->leave_salary_per_day,
                'allowances' => $allowances ?: null,
                'deductions' => $deductions ?: null,
            ]
        );

        return response()->json([
            'success' => 'Salary Structure ' . ($hasSalaryStructure ? 'Updated' : 'Created') . ' Successfully',
            'redirect' => route('hr.salary-structure.index'),
        ]);
    }
}

