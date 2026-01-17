@extends('admin_panel.layout.app')

@section('content')
    @include('hr.partials.hr-styles')

    <style>
        .attendance-card {
            background: var(--hr-card);
            border: 1px solid var(--hr-border);
            border-radius: 14px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            transition: all 0.2s;
        }

        .attendance-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        .attendance-card.present {
            border-left: 4px solid #22c55e;
        }

        .attendance-card.absent {
            border-left: 4px solid #ef4444;
        }

        .attendance-card.late {
            border-left: 4px solid #f59e0b;
        }

        .attendance-card.leave {
            border-left: 4px solid #3b82f6;
        }

        .time-input-group {
            background: #f8fafc;
            border: 1px solid var(--hr-border);
            border-radius: 10px;
            padding: 12px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .time-label {
            font-size: 0.75rem;
            color: var(--hr-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
            display: block;
        }

        .time-field {
            border: 1px solid var(--hr-border);
            border-radius: 6px;
            padding: 6px;
            width: 100%;
            font-size: 0.9rem;
        }

        .location-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.75rem;
            color: var(--hr-muted);
            background: #f1f5f9;
            padding: 2px 8px;
            border-radius: 4px;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .status-select {
            width: 100%;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid var(--hr-border);
            background: white;
            font-weight: 500;
        }

        .shift-info {
            font-size: 0.8rem;
            color: var(--hr-muted);
            background: #f8fafc;
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 4px;
        }

        .save-bar {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--hr-bg);
            padding: 12px 24px;
            border-radius: 50px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            z-index: 100;
            display: none;
            border: 1px solid var(--hr-border);
            align-items: center;
            gap: 16px;
        }

        .save-bar.visible {
            display: flex;
            animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                transform: translate(-50%, 100%);
                opacity: 0;
            }

            to {
                transform: translate(-50%, 0);
                opacity: 1;
            }
        }

        .holiday-banner {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header d-flex justify-content-between align-items-start">
                    <div>
                        <h1 class="page-title"><i class="fa fa-clock"></i> Daily Attendance</h1>
                        <p class="page-subtitle">{{ \Carbon\Carbon::parse($selectedDate)->format('l, F d, Y') }}</p>
                    </div>
                    <div class="d-flex gap-3">
                        @can('hr.attendance.create')
                            <a href="{{ route('hr.attendance.kiosk') }}" class="btn btn-outline-primary">
                                <i class="fa fa-desktop me-2"></i> Kiosk Mode
                            </a>
                        @endcan
                    </div>
                </div>

                <!-- Holiday Alert -->
                @if ($isHoliday)
                    <div class="holiday-banner">
                        <i class="fa fa-calendar-star fa-lg"></i>
                        <div>
                            <div style="font-size: 0.9rem; opacity: 0.9;">Today is a Holiday</div>
                            <div style="font-size: 1.1rem; font-weight: 700;">{{ $holiday->name }}</div>
                        </div>
                    </div>
                @endif

                <!-- Stats Row -->
                <div class="stats-row">
                    <div class="stat-card success">
                        <div class="stat-icon"><i class="fa fa-check"></i></div>
                        <div class="stat-value">{{ $summary['present'] }}</div>
                        <div class="stat-label">Present</div>
                    </div>
                    <div class="stat-card danger">
                        <div class="stat-icon"><i class="fa fa-times"></i></div>
                        <div class="stat-value">{{ $summary['absent'] }}</div>
                        <div class="stat-label">Absent</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-icon"><i class="fa fa-exclamation-triangle"></i></div>
                        <div class="stat-value">{{ $summary['late'] }}</div>
                        <div class="stat-label">Late</div>
                    </div>
                    <div class="stat-card info">
                        <div class="stat-icon"><i class="fa fa-umbrella-beach"></i></div>
                        <div class="stat-value">{{ $summary['leave'] }}</div>
                        <div class="stat-label">On Leave</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 p-3 bg-white">
                    <form id="filterForm" method="GET" action="{{ route('hr.attendance.index') }}"
                        class="d-flex flex-wrap gap-3 align-items-end">
                        <div style="flex: 1; min-width: 200px;">
                            <label class="form-label text-muted small fw-bold">DATE</label>
                            <input type="date" name="date" class="form-control" value="{{ $selectedDate }}">
                        </div>
                        <div style="flex: 1; min-width: 200px;">
                            <label class="form-label text-muted small fw-bold">DEPARTMENT</label>
                            <select name="department_id" class="form-select">
                                <option value="">All Departments</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}"
                                        {{ $selectedDepartment == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div style="flex: 1; min-width: 200px;">
                            <label class="form-label text-muted small fw-bold">DESIGNATION</label>
                            <select name="designation_id" class="form-select">
                                <option value="">All Designations</option>
                                @foreach ($designations as $desig)
                                    <option value="{{ $desig->id }}"
                                        {{ $selectedDesignation == $desig->id ? 'selected' : '' }}>
                                        {{ $desig->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div style="flex: 1; min-width: 150px;">
                            <label class="form-label text-muted small fw-bold">STATUS</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="present" {{ $selectedStatus == 'present' ? 'selected' : '' }}>Present
                                </option>
                                <option value="absent" {{ $selectedStatus == 'absent' ? 'selected' : '' }}>Absent</option>
                                <option value="late" {{ $selectedStatus == 'late' ? 'selected' : '' }}>Late</option>
                                <option value="leave" {{ $selectedStatus == 'leave' ? 'selected' : '' }}>Leave</option>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-filter me-1"></i> Apply</button>
                            <a href="{{ route('hr.attendance.index') }}" class="btn btn-light border"><i
                                    class="fa fa-sync"></i></a>
                        </div>
                    </form>
                </div>

                <!-- Attendance Grid -->
                <form id="attendanceForm" action="{{ route('hr.attendance.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="date" value="{{ $selectedDate }}">

                    <div class="hr-grid">
                        @forelse ($employees as $emp)
                            @php
                                $attendance = $emp->attendances->first();
                                $status = $attendance->status ?? 'absent';
                                if (!$attendance && $isHoliday) {
                                    $status = 'holiday';
                                }

                                // Default or marked shift
                                $shiftName = $emp->shift->name ?? 'Default';
                                $shiftTime = $emp->shift
                                    ? \Carbon\Carbon::parse($emp->shift->start_time)->format('H:i') .
                                        ' - ' .
                                        \Carbon\Carbon::parse($emp->shift->end_time)->format('H:i')
                                    : '9:00 - 17:00';
                            @endphp

                            <div class="attendance-card {{ $status }}">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="hr-avatar" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                        {{ strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)) }}
                                    </div>
                                    <div class="hr-item-info ms-3">
                                        <h4 class="hr-item-name">{{ $emp->full_name }}</h4>
                                        <div class="shift-info"><i class="fa fa-clock me-1"></i> {{ $shiftTime }}</div>
                                    </div>
                                </div>

                                <div class="time-input-group">
                                    <div>
                                        <label class="time-label">Check In</label>
                                        @if ($attendance && $attendance->check_in_time)
                                            <div class="fw-bold text-success">
                                                {{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') }}
                                            </div>
                                            @if ($attendance->is_late)
                                                <small class="text-warning"><i class="fa fa-exclamation-circle"></i> Late
                                                    {{ $attendance->late_minutes }}m</small>
                                            @endif
                                            @if ($attendance->check_in_location)
                                                <div class="location-badge" title="{{ $attendance->check_in_location }}">
                                                    <i class="fa fa-map-marker-alt"></i>
                                                    {{ Str::limit($attendance->check_in_location, 12) }}
                                                </div>
                                            @endif
                                        @else
                                            <input type="time" name="attendance[{{ $emp->id }}][clock_in]"
                                                class="time-field" onchange="showSaveBar()">
                                        @endif
                                    </div>

                                    <div>
                                        <label class="time-label">Check Out</label>
                                        @if ($attendance && $attendance->check_out_time)
                                            <div class="fw-bold text-danger">
                                                {{ \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') }}
                                            </div>
                                            @if ($attendance->is_early_leave)
                                                <small class="text-info"><i class="fa fa-run"></i> Early
                                                    {{ $attendance->early_leave_minutes }}m</small>
                                            @endif
                                            @if ($attendance->check_out_location)
                                                <div class="location-badge"
                                                    title="{{ $attendance->check_out_location }}">
                                                    <i class="fa fa-map-marker-alt"></i>
                                                    {{ Str::limit($attendance->check_out_location, 12) }}
                                                </div>
                                            @endif
                                        @else
                                            <input type="time" name="attendance[{{ $emp->id }}][clock_out]"
                                                class="time-field" onchange="showSaveBar()">
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-auto">
                                    <label class="time-label">Status</label>
                                    @if ($attendance)
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span
                                                class="hr-tag {{ $attendance->status == 'present' ? 'success' : ($attendance->status == 'absent' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($attendance->status) }}
                                            </span>
                                            @if ($attendance->total_hours)
                                                <small class="fw-bold">{{ number_format($attendance->total_hours, 1) }}
                                                    hrs</small>
                                            @endif
                                        </div>
                                    @else
                                        <select name="attendance[{{ $emp->id }}][status]" class="status-select"
                                            onchange="showSaveBar()">
                                            <option value="">-- Mark Status --</option>
                                            <option value="present">Present</option>
                                            <option value="absent">Absent</option>
                                            <option value="late">Late</option>
                                            <option value="leave">Leave</option>
                                        </select>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-12 py-5 text-center">
                                <i class="fa fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No employees found matching the filters.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Floating Save Bar -->
                    @if ($employees->count() > 0 && $selectedDate == date('Y-m-d'))
                        @can('hr.attendance.create')
                            <div class="save-bar" id="saveBar">
                                <span class="fw-bold text-dark">Unsaved changes detected</span>
                                <button type="submit" class="btn btn-save shadow-sm">
                                    <i class="fa fa-check me-2"></i> Save Now
                                </button>
                            </div>
                        @endcan
                    @endif
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showSaveBar() {
            document.getElementById('saveBar').classList.add('visible');
        }

        $(document).ready(function() {
            $('#attendanceForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Saved!',
                                text: response.success,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => location.reload());
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Something went wrong', 'error');
                    }
                });
            });
        });
    </script>
@endsection
