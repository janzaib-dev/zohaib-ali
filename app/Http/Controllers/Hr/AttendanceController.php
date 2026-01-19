<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Attendance;
use App\Models\Hr\Department;
use App\Models\Hr\Designation;
use App\Models\Hr\Employee;
use App\Models\Hr\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        if (! auth()->user()->can('hr.attendance.view')) {
            abort(403, 'Unauthorized action.');
        }

        // Get filter values
        $selectedDate = $request->get('date', Carbon::today()->format('Y-m-d'));
        $selectedDepartment = $request->get('department_id');
        $selectedDesignation = $request->get('designation_id');
        $selectedStatus = $request->get('status');

        $today = Carbon::parse($selectedDate);

        // Build query with filters
        $query = Employee::with(['department', 'designation', 'shift',
            'attendances' => function ($q) use ($selectedDate) {
                $q->whereDate('date', $selectedDate);
            },
        ])->where('status', 'active');

        if ($selectedDepartment) {
            $query->where('department_id', $selectedDepartment);
        }

        if ($selectedDesignation) {
            $query->where('designation_id', $selectedDesignation);
        }

        if ($selectedStatus) {
            $query->whereHas('attendances', function ($q) use ($selectedDate, $selectedStatus) {
                $q->whereDate('date', $selectedDate)->where('status', $selectedStatus);
            });
        }

        $employees = $query->orderBy('first_name')->paginate(12)->withQueryString();

        // Calculate summary
        $allAttendances = Attendance::whereDate('date', $selectedDate)->get();
        $summary = [
            'present' => $allAttendances->where('status', 'present')->count(),
            'absent' => $allAttendances->where('status', 'absent')->count(),
            'late' => $allAttendances->where('status', 'late')->count(),
            'leave' => $allAttendances->where('status', 'leave')->count(),
        ];

        $isHoliday = Holiday::isHoliday($today);
        $holiday = Holiday::getHoliday($today);

        // Get departments and designations for filter dropdowns
        $departments = Department::orderBy('name')->get();
        $designations = Designation::orderBy('name')->get();

        return view('hr.attendance.index', compact(
            'employees', 'today', 'isHoliday', 'holiday',
            'departments', 'designations', 'summary',
            'selectedDate', 'selectedDepartment', 'selectedDesignation', 'selectedStatus'
        ));
    }

    public function store(Request $request)
    {
        if (! auth()->user()->can('hr.attendance.create')) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'attendance' => 'required|array',
            'attendance.*.status' => 'nullable|in:present,absent,late,leave',
            'attendance.*.clock_in' => 'nullable|date_format:H:i',
            'attendance.*.clock_out' => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        foreach ($request->attendance as $empId => $data) {
            if (isset($data['status'])) {
                Attendance::updateOrCreate(
                    ['employee_id' => $empId, 'date' => Carbon::today()],
                    [
                        'status' => $data['status'],
                        'clock_in' => $data['clock_in'] ?? null,
                        'clock_out' => $data['clock_out'] ?? null,
                    ]
                );
            }
        }

        return response()->json([
            'success' => 'Attendance marked successfully.',
            'reload' => true,
        ]);
    }

    /**
     * Show the attendance kiosk page
     */
    public function kiosk()
    {
        if (! auth()->user()->can('hr.attendance.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('hr.attendance.kiosk');
    }

    /**
     * Mark attendance via kiosk (with photo)
     */
    public function markAttendance(Request $request)
    {
        if (! auth()->user()->can('hr.attendance.create')) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'type' => 'required|in:check_in,check_out',
            'photo' => 'nullable|string',
            'employee_id' => 'nullable|exists:hr_employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $type = $request->input('type'); // 'check_in' or 'check_out'
        $photo = $request->input('photo');

        // For now, we'll use a simple employee selection approach
        // In phase 2, we'll integrate face recognition to identify the employee

        // Get employee from session or use a demo employee
        $employeeId = $request->input('employee_id');

        if (! $employeeId) {
            // For demo purposes, get the first active employee
            // In production, this would be determined by face recognition
            $employee = Employee::where('status', 'active')->first();
            if (! $employee) {
                return response()->json(['error' => 'No employees found in system']);
            }
            $employeeId = $employee->id;
        }

        $employee = Employee::with(['department', 'shift'])->findOrFail($employeeId);
        $today = Carbon::today();
        $now = Carbon::now();

        // Check if today is a holiday
        if (Holiday::isHoliday($today)) {
            $holiday = Holiday::getHoliday($today);

            return response()->json([
                'error' => 'Today is a holiday: '.$holiday->name,
            ]);
        }

        // Get or create today's attendance
        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'date' => $today,
        ]);

        // Save photo
        $photoPath = null;
        if ($photo) {
            $photoData = explode(',', $photo);
            if (count($photoData) > 1) {
                $imageData = base64_decode($photoData[1]);
                $fileName = 'attendance_'.$employee->id.'_'.$type.'_'.time().'.jpg';
                $path = 'uploads/attendance/'.date('Y/m/');

                if (! file_exists(public_path($path))) {
                    mkdir(public_path($path), 0755, true);
                }
                file_put_contents(public_path($path.$fileName), $imageData);
                $photoPath = $path.$fileName;
            }
        }

        $isLate = false;
        $lateMinutes = 0;
        $isEarlyLeave = false;
        $earlyLeaveMinutes = 0;

        if ($type === 'check_in') {
            // Check if already checked in
            if ($attendance->check_in_time) {
                return response()->json([
                    'error' => 'Already checked in today at '.Carbon::parse($attendance->check_in_time)->format('h:i A'),
                ]);
            }

            $attendance->check_in_time = $now->format('H:i:s');
            $attendance->check_in_photo = $photoPath;
            $attendance->status = 'present';

            // Check if late
            $shiftStart = $employee->getStartTime();
            $graceMinutes = $employee->getGraceMinutes();
            $shiftStartTime = Carbon::parse($shiftStart);
            $graceEndTime = $shiftStartTime->copy()->addMinutes($graceMinutes);

            if ($now->gt($graceEndTime)) {
                $isLate = true;
                $lateMinutes = $now->diffInMinutes($shiftStartTime);
                $attendance->is_late = true;
                $attendance->late_minutes = $lateMinutes;
                $attendance->status = 'late';
            }
        } else {
            // Check out
            if (! $attendance->check_in_time) {
                return response()->json([
                    'error' => 'Please check in first before checking out',
                ]);
            }

            if ($attendance->check_out_time) {
                return response()->json([
                    'error' => 'Already checked out today at '.Carbon::parse($attendance->check_out_time)->format('h:i A'),
                ]);
            }

            $attendance->check_out_time = $now->format('H:i:s');
            $attendance->check_out_photo = $photoPath;

            // Calculate total hours
            $checkIn = Carbon::parse($attendance->check_in_time);
            $checkOut = Carbon::parse($attendance->check_out_time);
            $attendance->total_hours = round($checkOut->diffInMinutes($checkIn) / 60, 2);

            // Check if early leave
            $shiftEnd = $employee->getEndTime();
            $shiftEndTime = Carbon::parse($shiftEnd);

            if ($now->lt($shiftEndTime)) {
                $isEarlyLeave = true;
                $earlyLeaveMinutes = $now->diffInMinutes($shiftEndTime);
                $attendance->is_early_leave = true;
                $attendance->early_leave_minutes = $earlyLeaveMinutes;
            }
        }

        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => $type === 'check_in' ?
                'Check-in recorded at '.$now->format('h:i A') :
                'Check-out recorded at '.$now->format('h:i A'),
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes,
            'is_early_leave' => $isEarlyLeave,
            'early_leave_minutes' => $earlyLeaveMinutes,
            'total_hours' => $attendance->total_hours,
            'employee' => [
                'name' => $employee->full_name,
                'department' => $employee->department->name ?? 'N/A',
                'photo' => $employee->face_photo ? asset($employee->face_photo) : null,
            ],
        ]);
    }

    /**
     * Show my attendance page (for logged-in users)
     */
    public function myAttendance()
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->with(['department', 'designation', 'shift'])->first();

        $attendance = null;
        $requiresLocation = false;
        
        if ($employee) {
            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', Carbon::today())
                ->first();
            
            // Check if employee's designation requires location
            $requiresLocation = $employee->designation && $employee->designation->requires_location;
        }

        return view('hr.attendance.my-attendance', compact('employee', 'attendance', 'requiresLocation'));
    }

    /**
     * Mark my own attendance
     */
    public function markMyAttendance(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'type' => 'required|in:check_in,check_out',
            'photo' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->with(['shift'])->first();

        if (! $employee) {
            return response()->json(['error' => 'No employee profile found for your account']);
        }

        $type = $request->input('type');
        $today = Carbon::today();
        $now = Carbon::now();

        // Check if today is a holiday
        if (Holiday::isHoliday($today)) {
            $holiday = Holiday::getHoliday($today);

            return response()->json([
                'error' => 'Today is a holiday: '.$holiday->name,
            ]);
        }

        // Get or create today's attendance
        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'date' => $today,
        ]);

        // Save photo if provided
        $photoPath = null;
        $photo = $request->input('photo');
        if ($photo) {
            $photoData = explode(',', $photo);
            if (count($photoData) > 1) {
                $imageData = base64_decode($photoData[1]);
                $fileName = 'my_attendance_'.$employee->id.'_'.$type.'_'.time().'.jpg';
                $path = 'uploads/attendance/'.date('Y/m/');

                if (! file_exists(public_path($path))) {
                    mkdir(public_path($path), 0755, true);
                }
                file_put_contents(public_path($path.$fileName), $imageData);
                $photoPath = $path.$fileName;
            }
        }
        // Get location data
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $locationName = null;

        // Check if employee's designation requires location
        $requiresLocation = $employee->designation && $employee->designation->requires_location;

        if ($requiresLocation) {
            // Location is mandatory for this designation
            if (!$latitude || !$longitude) {
                return response()->json([
                    'error' => 'Location is required for your designation. Please enable GPS and try again.',
                ]);
            }
            $locationName = $this->getLocationName($latitude, $longitude);
        } else {
            // Location is optional, default to "On-Site" if not provided
            if ($latitude && $longitude) {
                $locationName = $this->getLocationName($latitude, $longitude);
            } else {
                $locationName = 'On-Site';
                $latitude = null;
                $longitude = null;
            }
        }

        if ($type === 'check_in') {
            if ($attendance->check_in_time) {
                return response()->json([
                    'error' => 'Already checked in today at '.Carbon::parse($attendance->check_in_time)->format('h:i A'),
                ]);
            }

            $attendance->check_in_time = $now->format('H:i:s');
            $attendance->check_in_photo = $photoPath;
            $attendance->check_in_latitude = $latitude;
            $attendance->check_in_longitude = $longitude;
            $attendance->check_in_location = $locationName;
            $attendance->status = 'present';

            // Check if late
            $shiftStart = $employee->getStartTime();
            $graceMinutes = $employee->getGraceMinutes();
            $shiftStartTime = Carbon::parse($shiftStart);
            $graceEndTime = $shiftStartTime->copy()->addMinutes($graceMinutes);

            if ($now->gt($graceEndTime)) {
                $attendance->is_late = true;
                $attendance->late_minutes = $now->diffInMinutes($shiftStartTime);
                $attendance->status = 'late';
            }
        } else {
            if (! $attendance->check_in_time) {
                return response()->json([
                    'error' => 'Please check in first before checking out',
                ]);
            }

            if ($attendance->check_out_time) {
                return response()->json([
                    'error' => 'Already checked out today at '.Carbon::parse($attendance->check_out_time)->format('h:i A'),
                ]);
            }

            $attendance->check_out_time = $now->format('H:i:s');
            $attendance->check_out_photo = $photoPath;
            $attendance->check_out_latitude = $latitude;
            $attendance->check_out_longitude = $longitude;
            $attendance->check_out_location = $locationName;

            // Calculate total hours
            $checkIn = Carbon::parse($attendance->check_in_time);
            $checkOut = Carbon::parse($attendance->check_out_time);
            $attendance->total_hours = round($checkOut->diffInMinutes($checkIn) / 60, 2);

            // Check if early leave
            $shiftEnd = $employee->getEndTime();
            if ($shiftEnd) {
                $shiftEndTime = Carbon::parse($shiftEnd);
                if ($now->lt($shiftEndTime)) {
                    $attendance->is_early_leave = true;
                    $attendance->early_leave_minutes = $now->diffInMinutes($shiftEndTime);
                }
            }
        }

        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => $type === 'check_in' ?
                'Checked in at '.$now->format('h:i A') :
                'Checked out at '.$now->format('h:i A').'. Total: '.$attendance->total_hours.' hrs',
            'location' => $locationName,
        ]);
    }

    /**
     * Get location name from coordinates using OpenStreetMap
     */
    private function getLocationName($latitude, $longitude)
    {
        try {
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$latitude}&lon={$longitude}&zoom=10";

            $opts = [
                'http' => [
                    'method' => 'GET',
                    'header' => 'User-Agent: AttendanceApp/1.0',
                ],
            ];
            $context = stream_context_create($opts);
            $response = @file_get_contents($url, false, $context);

            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['address'])) {
                    $address = $data['address'];
                    $parts = [];

                    if (isset($address['suburb'])) {
                        $parts[] = $address['suburb'];
                    } elseif (isset($address['neighbourhood'])) {
                        $parts[] = $address['neighbourhood'];
                    }

                    if (isset($address['city'])) {
                        $parts[] = $address['city'];
                    } elseif (isset($address['town'])) {
                        $parts[] = $address['town'];
                    } elseif (isset($address['county'])) {
                        $parts[] = $address['county'];
                    }

                    return implode(', ', $parts) ?: ($data['display_name'] ?? null);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Geocoding error: '.$e->getMessage());
        }

        return null;
    }
}
