<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Hr\DepartmentController;
use App\Http\Controllers\Hr\EmployeeController;
use App\Http\Controllers\Hr\AttendanceController;
use App\Http\Controllers\Hr\PayrollController;
use App\Http\Controllers\Hr\LeaveController;
use App\Http\Controllers\Hr\ShiftController;
use App\Http\Controllers\Hr\HolidayController;

Route::middleware(['auth'])->prefix('hr')->name('hr.')->group(function () {
    
    // Departments
    Route::middleware(['permission:hr.departments.view'])->group(function () {
        Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
    });
    Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store')->middleware('permission:hr.departments.create|hr.departments.edit');
    Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy')->middleware('permission:hr.departments.delete');

    // Designations
    Route::middleware(['permission:hr.designations.view'])->group(function () {
        Route::get('designations', [\App\Http\Controllers\Hr\DesignationController::class, 'index'])->name('designations.index');
    });
    Route::post('designations', [\App\Http\Controllers\Hr\DesignationController::class, 'store'])->name('designations.store')->middleware('permission:hr.designations.create|hr.designations.edit');
    Route::delete('designations/{designation}', [\App\Http\Controllers\Hr\DesignationController::class, 'destroy'])->name('designations.destroy')->middleware('permission:hr.designations.delete');

    // Loans
    Route::get('/loans', [\App\Http\Controllers\Hr\LoanController::class, 'index'])->name('loans.index')->middleware('permission:hr.loans.view');
    Route::post('/loans', [\App\Http\Controllers\Hr\LoanController::class, 'store'])->name('loans.store')->middleware('permission:hr.loans.create');
    Route::post('/loans/{id}/approve', [\App\Http\Controllers\Hr\LoanController::class, 'approve'])->name('loans.approve')->middleware('permission:hr.loans.approve');
    Route::post('/loans/{id}/reject', [\App\Http\Controllers\Hr\LoanController::class, 'reject'])->name('loans.reject')->middleware('permission:hr.loans.approve');
    Route::delete('/loans/{id}', [\App\Http\Controllers\Hr\LoanController::class, 'destroy'])->name('loans.destroy')->middleware('permission:hr.loans.delete');
    Route::post('/loans/schedule', [\App\Http\Controllers\Hr\LoanController::class, 'scheduleDeduction'])->name('loans.schedule')->middleware('permission:hr.loans.schedule');

    // Employees
    Route::middleware(['permission:hr.employees.view'])->group(function () {
        Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    });
    Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store')->middleware('permission:hr.employees.create|hr.employees.edit');
    Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy')->middleware('permission:hr.employees.delete');
    Route::post('employees/{employee}/register-face', [EmployeeController::class, 'registerFace'])->name('employees.register-face')->middleware('permission:hr.employees.edit');

    // Shifts
    Route::get('shifts', [ShiftController::class, 'index'])->name('shifts.index')->middleware('permission:hr.shifts.view');
    Route::post('shifts', [ShiftController::class, 'store'])->name('shifts.store')->middleware('permission:hr.shifts.create|hr.shifts.edit');
    Route::delete('shifts/{shift}', [ShiftController::class, 'destroy'])->name('shifts.destroy')->middleware('permission:hr.shifts.delete');

    // Holidays
    Route::get('holidays', [HolidayController::class, 'index'])->name('holidays.index')->middleware('permission:hr.holidays.view');
    Route::post('holidays', [HolidayController::class, 'store'])->name('holidays.store')->middleware('permission:hr.holidays.create|hr.holidays.edit');
    Route::delete('holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy')->middleware('permission:hr.holidays.delete');
    Route::get('holidays/list', [HolidayController::class, 'getHolidays'])->name('holidays.list')->middleware('permission:hr.holidays.view');

    // Attendance
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index')->middleware('permission:hr.attendance.view');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store')->middleware('permission:hr.attendance.create');
    Route::get('attendance/kiosk', [AttendanceController::class, 'kiosk'])->name('attendance.kiosk')->middleware('permission:hr.attendance.create');
    Route::post('attendance/mark', [AttendanceController::class, 'markAttendance'])->name('attendance.mark')->middleware('permission:hr.attendance.create');

    // Payroll
    Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index')->middleware('permission:hr.payroll.view');
    Route::post('payroll/generate', [PayrollController::class, 'generate'])->name('payroll.generate')->middleware('permission:hr.payroll.create');
    Route::patch('payroll/{payroll}/mark-paid', [PayrollController::class, 'markPaid'])->name('payroll.mark-paid')->middleware('permission:hr.payroll.edit');
    Route::delete('payroll/{payroll}', [PayrollController::class, 'destroy'])->name('payroll.destroy')->middleware('permission:hr.payroll.delete');

    // Leaves
    Route::get('leaves', [LeaveController::class, 'index'])->name('leaves.index')->middleware('permission:hr.leaves.view');
    Route::post('leaves', [LeaveController::class, 'store'])->name('leaves.store')->middleware('permission:hr.leaves.create');
    Route::patch('leaves/{leave}/status', [LeaveController::class, 'updateStatus'])->name('leaves.update-status')->middleware('permission:hr.leaves.approve');

    // Salary Structure
    // Index - requires any of view/create/edit (controller handles specifics)
    Route::get('salary-structure', [\App\Http\Controllers\Hr\SalaryStructureController::class, 'index'])->name('salary-structure.index')->middleware('permission:hr.salary.structure.view|hr.salary.structure.create|hr.salary.structure.edit');
    // Edit page - requires view (for read-only), create (for new), or edit (for existing)
    Route::get('salary-structure/{employee}/edit', [\App\Http\Controllers\Hr\SalaryStructureController::class, 'edit'])->name('salary-structure.edit')->middleware('permission:hr.salary.structure.view|hr.salary.structure.create|hr.salary.structure.edit');
    // Update - requires create (for new) or edit (for existing) - controller handles logic
    Route::put('salary-structure/{employee}', [\App\Http\Controllers\Hr\SalaryStructureController::class, 'update'])->name('salary-structure.update')->middleware('permission:hr.salary.structure.create|hr.salary.structure.edit');

});

// My Attendance - Available to all authenticated users (no HR permission required)
Route::middleware(['auth'])->group(function () {
    Route::get('my-attendance', [AttendanceController::class, 'myAttendance'])->name('my-attendance');
    Route::post('my-attendance/mark', [AttendanceController::class, 'markMyAttendance'])->name('my-attendance.mark');
});
