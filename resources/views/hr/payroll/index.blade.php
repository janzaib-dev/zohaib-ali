@extends('admin_panel.layout.app')

@section('content')
    @include('hr.partials.hr-styles')

    <style>
        .payroll-card {
            background: var(--hr-card);
            border: 1px solid var(--hr-border);
            border-radius: 14px;
            padding: 20px;
            transition: all 0.2s;
        }

        .payroll-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .payroll-card.pending {
            border-left: 4px solid #f59e0b;
        }

        .payroll-card.paid {
            border-left: 4px solid #22c55e;
        }

        .salary-display {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
            margin-top: 12px;
        }

        .salary-display .amount {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .salary-display .label {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .month-badge {
            background: #f8fafc;
            padding: 6px 16px;
            border-radius: 8px;
            font-weight: 600;
            color: var(--hr-text);
            border: 1px solid var(--hr-border);
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header d-flex justify-content-between align-items-start">
                    <div>
                        <h1 class="page-title"><i class="fa fa-money-bill-wave"></i> Payroll Management</h1>
                        <p class="page-subtitle">Generate and manage employee payroll</p>
                    </div>
                    @can('hr.payroll.generate')
                        <button type="button" class="btn btn-create" id="createBtn">
                            <i class="fa fa-plus"></i> Generate Payroll
                        </button>
                    @endcan
                </div>

                <!-- Stats Row -->
                @php
                    $pendingCount = $payrolls->where('status', 'pending')->count();
                    $paidCount = $payrolls->where('status', 'paid')->count();
                    $totalNet = $payrolls->sum('net_salary');
                    $totalPaid = $payrolls->where('status', 'paid')->sum('net_salary');
                @endphp
                <div class="stats-row">
                    <div class="stat-card primary">
                        <div class="stat-icon"><i class="fa fa-file-invoice-dollar"></i></div>
                        <div class="stat-value">{{ $payrolls->count() }}</div>
                        <div class="stat-label">Total Payrolls</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-icon"><i class="fa fa-hourglass-half"></i></div>
                        <div class="stat-value">{{ $pendingCount }}</div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
                        <div class="stat-value">{{ $paidCount }}</div>
                        <div class="stat-label">Paid</div>
                    </div>
                    <div class="stat-card info">
                        <div class="stat-icon"><i class="fa fa-coins"></i></div>
                        <div class="stat-value">{{ number_format($totalNet, 0) }}</div>
                        <div class="stat-label">Total Amount</div>
                    </div>
                </div>

                <!-- Payrolls Card -->
                <div class="hr-card">
                    <div class="hr-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="search-box">
                                <i class="fa fa-search"></i>
                                <input type="search" id="payrollSearch" placeholder="Search payrolls...">
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-outline-secondary btn-sm active" data-filter="all">All</button>
                                <button class="btn btn-outline-warning btn-sm" data-filter="pending">Pending</button>
                                <button class="btn btn-outline-success btn-sm" data-filter="paid">Paid</button>
                            </div>
                        </div>
                        <span class="text-muted small" id="payrollCount">{{ $payrolls->count() }} payrolls</span>
                    </div>

                    <div class="hr-grid" id="payrollGrid">
                        @forelse($payrolls as $payroll)
                            <div class="payroll-card {{ $payroll->status }}" data-id="{{ $payroll->id }}"
                                data-name="{{ strtolower($payroll->employee->full_name ?? '') }}"
                                data-status="{{ $payroll->status }}">
                                <div class="hr-item-header">
                                    <div class="d-flex align-items-center">
                                        <div class="hr-avatar"
                                            style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                                            {{ strtoupper(substr($payroll->employee->first_name ?? 'U', 0, 1) . substr($payroll->employee->last_name ?? 'N', 0, 1)) }}
                                        </div>
                                        <div class="hr-item-info">
                                            <h4 class="hr-item-name">{{ $payroll->employee->full_name ?? 'Unknown' }}</h4>
                                            <div class="hr-item-subtitle">
                                                {{ $payroll->employee->designation->name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="month-badge">{{ $payroll->month }}</span>
                                        <span
                                            class="hr-tag {{ $payroll->status == 'paid' ? 'success' : 'warning' }}">{{ ucfirst($payroll->status) }}</span>
                                    </div>
                                </div>

                                <div class="salary-display">
                                    <div class="label">Net Salary</div>
                                    <div class="amount">Rs. {{ number_format($payroll->net_salary, 2) }}</div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted">Basic: Rs.
                                        {{ number_format($payroll->basic_salary, 2) }}</small>
                                    <div class="hr-actions">
                                        @can('hr.payroll.view')
                                            <button class="btn btn-view view-btn" title="View Details"
                                                data-id="{{ $payroll->id }}"
                                                data-employee="{{ $payroll->employee->full_name ?? 'Unknown' }}"
                                                data-month="{{ $payroll->month }}"
                                                data-basic="{{ number_format($payroll->basic_salary, 2) }}"
                                                data-net="{{ number_format($payroll->net_salary, 2) }}"
                                                data-status="{{ $payroll->status }}">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        @endcan
                                        @can('hr.payroll.edit')
                                            @if ($payroll->status != 'paid')
                                                <button class="btn btn-edit mark-paid-btn" title="Mark Paid"
                                                    data-id="{{ $payroll->id }}">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            @endif
                                        @endcan
                                        @can('hr.payroll.delete')
                                            <button class="btn btn-delete delete-btn" title="Delete"
                                                data-id="{{ $payroll->id }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state" style="grid-column: 1/-1;">
                                <i class="fa fa-money-bill-wave"></i>
                                <p>No payrolls generated yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="payrollModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header gradient"
                    style="background: linear-gradient(135deg, #22c55e, #16a34a) !important;">
                    <h5 class="modal-title" id="modalLabel">
                        <i class="fa fa-money-bill-wave"></i>
                        <span>Generate Payroll</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="payrollForm" action="{{ route('hr.payroll.generate') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group-modern">
                            <label class="form-label"><i class="fa fa-user"></i> Employee</label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">Select Employee</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group-modern">
                            <label class="form-label"><i class="fa fa-calendar"></i> Month</label>
                            <input type="month" name="month" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer-modern">
                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-save"
                            style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                            <i class="fa fa-check"></i>
                            <span>Generate</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#createBtn').click(function() {
                $('#payrollForm')[0].reset();
                $('#payrollModal').modal('show');
            });

            $('[data-filter]').click(function() {
                $(this).addClass('active').siblings().removeClass('active');
                var filter = $(this).data('filter');

                $('.payroll-card').each(function() {
                    if (filter === 'all') {
                        $(this).show();
                    } else {
                        $(this).toggle($(this).data('status') === filter);
                    }
                });
                updateCount();
            });

            function updateCount() {
                $('#payrollCount').text($('.payroll-card:visible').length + ' payrolls');
            }

            $('#payrollSearch').on('input', function() {
                var q = $(this).val().toLowerCase();
                $('.payroll-card').each(function() {
                    var name = $(this).data('name') || '';
                    $(this).toggle(name.indexOf(q) !== -1);
                });
                updateCount();
            });

            $(document).on('click', '.view-btn', function() {
                var data = $(this).data();
                Swal.fire({
                    title: 'Payroll Details',
                    html: `
                        <div class="text-start">
                            <p><strong>Employee:</strong> ${data.employee}</p>
                            <p><strong>Month:</strong> ${data.month}</p>
                            <p><strong>Basic Salary:</strong> Rs. ${data.basic}</p>
                            <p><strong>Net Salary:</strong> Rs. ${data.net}</p>
                            <p><strong>Status:</strong> <span class="badge bg-${data.status == 'paid' ? 'success' : 'warning'}">${data.status}</span></p>
                        </div>
                    `,
                    confirmButtonText: 'Close'
                });
            });

            $(document).on('click', '.mark-paid-btn', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Mark as Paid?',
                    text: 'This will mark the payroll as paid.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#22c55e',
                    confirmButtonText: 'Yes, Mark Paid'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/hr/payroll/' + id + '/mark-paid',
                            type: 'PATCH',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Success', response.success, 'success')
                                        .then(() => location.reload());
                                }
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.delete-btn', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Payroll?',
                    text: 'This cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Yes, delete!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/hr/payroll/' + id,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Deleted!', response.success, 'success')
                                        .then(() => location.reload());
                                }
                            }
                        });
                    }
                });
            });

            $('#payrollForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success', response.success, 'success').then(() =>
                                location.reload());
                        } else if (response.errors) {
                            Swal.fire('Error', response.errors.join('<br>'), 'error');
                        }
                    }
                });
            });
        });
    </script>
@endsection
