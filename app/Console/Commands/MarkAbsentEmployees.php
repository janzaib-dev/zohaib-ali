<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Hr\Employee;
use App\Models\Hr\Attendance;
use Carbon\Carbon;

class MarkAbsentEmployees extends Command
{
    protected $signature = 'attendance:mark-absent {date?}';
    protected $description = 'Mark active employees as absent if no attendance record exists for the date';

    public function handle()
    {
        $dateStr = $this->argument('date') ?? date('Y-m-d');
        $date = Carbon::parse($dateStr);
        $dayName = strtolower($date->format('l')); // e.g., 'monday'

        $this->info("Marking absent employees for: " . $date->toDateString());

        // Get all active employees
        $employees = Employee::where('status', 'active')->get();
        $absentCount = 0;

        foreach ($employees as $employee) {
            // Check if attendance exists
            $exists = Attendance::where('employee_id', $employee->id)
                ->where('date', $date->toDateString())
                ->exists();

            if (!$exists) {
                // Determine if today is a working day (Optional: Check Shift or Holidays)
                // For now, assuming standard working days. 
                // In future: Check if $employee->shift->days contains $dayName
                
                // Create Absent Record
                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $date->toDateString(),
                    'status' => 'absent',
                    'is_late' => false,
                    'late_minutes' => 0,
                    'is_early_leave' => false,
                    'early_leave_minutes' => 0,
                    'total_hours' => 0,
                ]);

                $this->line("Marked Absent: {$employee->full_name}");
                $absentCount++;
            }
        }

        $this->info("Completed. Marked {$absentCount} employees as absent.");
    }
}
