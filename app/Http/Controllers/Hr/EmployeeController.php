<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Department;
use App\Models\Hr\Designation;
use App\Models\Hr\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    public function index()
    {
        if (! auth()->user()->can('hr.employees.view')) {
            abort(403, 'Unauthorized action.');
        }
        $employees = Employee::with(['department', 'designation'])->latest()->paginate(12);
        $departments = Department::all();
        $designations = Designation::all();

        return view('hr.employees.index', compact('employees', 'departments', 'designations'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|min:3',
            'last_name' => 'required|string|min:3',
            'phone' => 'required|string|min:11|max:11',
            'email' => 'required|email|max:255|unique:hr_employees,email,'.$request->edit_id,
            'department_id' => 'required|exists:hr_departments,id',
            'designation_id' => 'required|exists:hr_designations,id',
            'joining_date' => 'required|date',
            'basic_salary' => 'required|numeric',
            'password' => $request->filled('edit_id') ? 'nullable|min:6' : 'required|min:6',
            'document_degree' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'document_certificate' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'document_hsc_marksheet' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'document_ssc_marksheet' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'document_cv' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['document_degree', 'document_certificate', 'document_hsc_marksheet', 'document_ssc_marksheet', 'document_cv', 'password']);
        $data['is_docs_submitted'] = $request->has('is_docs_submitted') ? 1 : 0;

        if ($request->filled('edit_id')) {
            if (! auth()->user()->can('hr.employees.edit')) {
                return response()->json(['error' => 'Unauthorized action.'], 403);
            }
            $employee = Employee::findOrFail($request->edit_id);

            // Update User email if changed
            if ($employee->user_id) {
                $user = \App\Models\User::find($employee->user_id);
                if ($user) {
                    $user->email = $request->email;
                    $user->name = $request->first_name.' '.$request->last_name;
                    if ($request->filled('password')) {
                        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
                    }
                    $user->save();
                }
            }

            $employee->update($data);
        } else {
            if (! auth()->user()->can('hr.employees.create')) {
                return response()->json(['error' => 'Unauthorized action.'], 403);
            }
            // Create User Account
            $user = \App\Models\User::create([
                'name' => $request->first_name.' '.$request->last_name,
                'email' => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            ]);

            $data['user_id'] = $user->id;
            $employee = Employee::create($data);
        }

        // Handle File Uploads (Create/Update in hr_employee_documents)
        $fileFields = ['document_degree', 'document_certificate', 'document_hsc_marksheet', 'document_ssc_marksheet', 'document_cv'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = time().'_'.$field.'.'.$file->getClientOriginalExtension();
                $file->move(public_path('uploads/human_resource/documents'), $filename);
                $filePath = 'uploads/human_resource/documents/'.$filename;

                // Update or Create the document record
                // Since $field matches the form name (e.g. document_degree), we use it as key or map if needed.
                // We'll remove 'document_' prefix to get pure type
                $type = str_replace('document_', '', $field);

                \App\Models\Hr\EmployeeDocument::updateOrCreate(
                    ['employee_id' => $employee->id, 'type' => $type],
                    ['file_path' => $filePath]
                );
            }
        }

        return response()->json([
            'success' => $request->filled('edit_id') ? 'Employee Updated Successfully' : 'Employee Created Successfully',
            'reload' => true,
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        // Not used in this pattern, handled in store with edit_id
    }

    public function destroy(Employee $employee)
    {
        if (! auth()->user()->can('hr.employees.delete')) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }
        $employee->delete();

        return response()->json([
            'success' => 'Employee Deleted Successfully',
            'reload' => true,
        ]);
    }
}
