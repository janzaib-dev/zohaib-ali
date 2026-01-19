@extends('admin_panel.layout.app')

@section('content')
    @include('hr.partials.hr-styles')

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="page-title"><i class="fa fa-hand-holding-usd"></i> Loans & Advances</h4>
                        <p class="text-muted mb-0">Manage employee loans, advances, and repayment schedules</p>
                    </div>
                    @can('hr.loans.create')
                        <button class="btn btn-create" data-bs-toggle="modal" data-bs-target="#addLoanModal">
                            <i class="fa fa-plus"></i> New Loan Request
                        </button>
                    @endcan
                </div>

                <!-- Stats Row -->
                @php
                    $pendingCount = \App\Models\Hr\Loan::where('status', 'pending')->count();
                    $activeAmount =
                        \App\Models\Hr\Loan::where('status', 'approved')->sum('amount') -
                        \App\Models\Hr\Loan::where('status', 'approved')->sum('paid_amount');
                    $paidCount = \App\Models\Hr\Loan::where('status', 'paid')->count();
                @endphp
                <div class="stats-row">
                    <div class="stat-card primary">
                        <div class="stat-icon"><i class="fa fa-file-invoice-dollar"></i></div>
                        <div class="stat-value">{{ $loans->total() }}</div>
                        <div class="stat-label">Total Loans</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-icon"><i class="fa fa-clock"></i></div>
                        <div class="stat-value">{{ $pendingCount }}</div>
                        <div class="stat-label">Pending Approval</div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-icon"><i class="fa fa-money-bill-wave"></i></div>
                        <div class="stat-value">
                            {{ number_format($activeAmount) }}
                        </div>
                        <div class="stat-label">Active Amount</div>
                    </div>
                    <div class="stat-card info">
                        <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
                        <div class="stat-value">{{ $paidCount }}</div>
                        <div class="stat-label">Fully Paid</div>
                    </div>
                </div>

                <!-- Loans Card -->
                <div class="hr-card">
                    <div class="hr-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="search-box">
                                <i class="fa fa-search"></i>
                                <input type="search" id="loanSearch" placeholder="Search loans...">
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()"
                                    title="Refresh"><i class="fa fa-sync"></i></button>
                            </div>
                        </div>
                        <span class="text-muted small" id="loanCount">{{ $loans->total() }} records</span>
                    </div>

                    <div class="hr-grid" id="loanGrid">
                        @forelse($loans as $loan)
                            <div class="hr-item-card" data-id="{{ $loan->id }}"
                                data-employee="{{ strtolower($loan->employee->full_name) }}"
                                data-designation="{{ strtolower($loan->employee->designation->name ?? '') }}"
                                data-date="{{ $loan->created_at->format('d M Y') }}" data-amount="{{ $loan->amount }}"
                                data-status="{{ $loan->status }}">
                                <div class="hr-item-header">
                                    <div class="d-flex align-items-center">
                                        <div class="hr-avatar"
                                            style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                                            {{ substr($loan->employee->first_name, 0, 1) }}
                                        </div>
                                        <div class="hr-item-info">
                                            <h4 class="hr-item-name">{{ $loan->employee->full_name }}</h4>
                                            <div class="hr-item-subtitle">{{ $loan->employee->designation->name ?? 'N/A' }}
                                            </div>
                                            <div class="hr-item-meta">
                                                <i class="fa fa-calendar-alt me-1"></i>
                                                {{ $loan->created_at->format('d M, Y') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm border" type="button"
                                            data-bs-toggle="dropdown">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            @if ($loan->status == 'pending')
                                                @can('hr.loans.approve')
                                                    <li><a class="dropdown-item text-success py-2" href="javascript:void(0)"
                                                            onclick="approveLoan({{ $loan->id }})"><i
                                                                class="fa fa-check me-2"></i> Approve Request</a></li>
                                                    <li><a class="dropdown-item text-danger py-2" href="javascript:void(0)"
                                                            onclick="rejectLoan({{ $loan->id }})"><i
                                                                class="fa fa-times me-2"></i> Reject Request</a></li>
                                                @endcan
                                            @endif

                                            @if ($loan->status == 'approved')
                                                <li><a class="dropdown-item text-primary py-2" href="javascript:void(0)"
                                                        onclick="scheduleDeductionModal({{ $loan->id }}, {{ $loan->remaining_amount }})"><i
                                                            class="fa fa-calendar-plus me-2"></i> Schedule Deduction</a>
                                                </li>
                                            @endif

                                            @can('hr.loans.delete')
                                                <li>
                                                    @if ($loan->status != 'pending')
                                                        <div class="dropdown-divider"></div>
                                                    @endif
                                                    <a class="dropdown-item text-danger py-2" href="javascript:void(0)"
                                                        onclick="deleteLoan({{ $loan->id }})"><i
                                                            class="fa fa-trash me-2"></i> Delete Record</a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </div>
                                <div class="mt-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted small">Amount</span>
                                        <span class="fw-bold fs-5">{{ number_format($loan->amount) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted small">Installment</span>
                                        <span class="fw-medium">
                                            @if ($loan->installment_amount > 0)
                                                {{ number_format($loan->installment_amount) }} <small
                                                    class="text-muted">/mo</small>
                                            @else
                                                <span
                                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"
                                                    style="font-size: 0.7rem;">Manual Pay</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">Remaining</span>
                                        <span
                                            class="fw-bold text-danger">{{ number_format($loan->remaining_amount) }}</span>
                                    </div>
                                </div>

                                <div class="hr-tags pt-2 border-top">
                                    @if ($loan->status == 'pending')
                                        <span class="hr-tag warning w-100 text-center">Pending Approval</span>
                                    @elseif($loan->status == 'approved')
                                        <span class="hr-tag success w-100 text-center">Active Loan</span>
                                    @elseif($loan->status == 'rejected')
                                        <span class="hr-tag danger w-100 text-center">Rejected</span>
                                    @elseif($loan->status == 'paid')
                                        <span class="hr-tag info w-100 text-center">Fully Paid</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="empty-state" style="grid-column: 1/-1;">
                                <i class="fa fa-file-invoice-dollar"></i>
                                <p>No loan records found. Create a new loan request to get started.</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="px-4 py-3 border-top">
                        {{ $loans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Loan Modal -->
    <div class="modal fade" id="addLoanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header gradient text-white">
                    <h5 class="modal-title"><i class="fa fa-plus-circle me-2"></i> New Loan Request</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('hr.loans.store') }}" method="POST" id="addLoanForm" data-ajax-validate="true">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="form-group-modern mb-3">
                            <label class="form-label">Employee</label>
                            <select name="employee_id" class="form-select select2" required style="width: 100%;">
                                <option value="">Select Employee</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }}
                                        ({{ $emp->designation->name ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group-modern">
                                    <label class="form-label">Loan Amount</label>
                                    <input type="number" name="amount" class="form-control" required min="1"
                                        placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group-modern">
                                    <label class="form-label">Monthly Installment</label>
                                    <input type="number" name="installment_amount" class="form-control" min="0"
                                        placeholder="0 for Manual" value="0">
                                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Set 0 for Large
                                        Sum/Manual Pay.</small>
                                </div>
                            </div>
                        </div>
                        <div class="form-group-modern mb-3">
                            <label class="form-label">Reason / Notes</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason for loan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer-modern">
                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-save"><i class="fa fa-paper-plane me-1"></i> Submit
                            Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Schedule Deduction Modal -->
    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning-subtle">
                    <h5 class="modal-title text-warning-emphasis"><i class="fa fa-clock me-2"></i> Schedule Salary
                        Deduction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('hr.loans.schedule') }}" method="POST" id="scheduleForm"
                    data-ajax-validate="true">
                    @csrf
                    <input type="hidden" name="loan_id" id="schedule_loan_id">
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis mb-4">
                            <div class="d-flex">
                                <i class="fa fa-info-circle fs-4 me-3 mt-1"></i>
                                <div>
                                    <strong>One-time Deduction</strong><br>
                                    Use this to force a specific deduction amount from the employee's salary for a specific
                                    month.
                                </div>
                            </div>
                        </div>
                        <div class="form-group-modern mb-3">
                            <label class="form-label">Deduction Amount</label>
                            <input type="number" name="amount" id="schedule_amount" class="form-control" required
                                min="1">
                            <small class="text-muted">Max Amount: Rs. <span id="max_sched_amount"
                                    class="fw-bold"></span></small>
                        </div>
                        <div class="form-group-modern mb-3">
                            <label class="form-label">For Month</label>
                            <input type="month" name="month" class="form-control" required
                                value="{{ date('Y-m') }}">
                        </div>
                        <div class="form-group-modern mb-3">
                            <label class="form-label">Notes</label>
                            <input type="text" name="notes" class="form-control"
                                placeholder="Optional notes for payroll">
                        </div>
                    </div>
                    <div class="modal-footer-modern">
                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-save bg-warning border-warning text-dark"><i
                                class="fa fa-calendar-check me-1"></i> Schedule Deduction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Search Functionality
            $('#loanSearch').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#loanGrid .hr-item-card').filter(function() {
                    let text = $(this).data('employee') + ' ' +
                        $(this).data('designation') + ' ' +
                        $(this).data('date') + ' ' +
                        $(this).data('amount') + ' ' +
                        $(this).data('status');
                    $(this).toggle(text.toLowerCase().indexOf(value) > -1)
                });
                $('#loanCount').text($('#loanGrid .hr-item-card:visible').length + ' records');
            });

            // Initialize Select2 in Modal
            $('.select2').select2({
                dropdownParent: $('#addLoanModal'),
                width: '100%'
            });
        });

        function approveLoan(id) {
            Swal.fire({
                title: 'Approve Loan?',
                text: "Are you sure you want to approve this loan?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Approve',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/hr/loans/' + id + '/approve',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            Swal.fire({
                                title: 'Approved!',
                                text: res.success,
                                icon: 'success',
                                confirmButtonColor: '#3b82f6'
                            }).then(() => location.reload());
                        }
                    });
                }
            });
        }

        function rejectLoan(id) {
            Swal.fire({
                title: 'Reject Loan?',
                text: "This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Reject',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/hr/loans/' + id + '/reject',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            Swal.fire({
                                title: 'Rejected!',
                                text: res.success,
                                icon: 'success',
                                confirmButtonColor: '#3b82f6'
                            }).then(() => location.reload());
                        }
                    });
                }
            });
        }

        function deleteLoan(id) {
            Swal.fire({
                title: 'Delete Loan Record?',
                text: "This will remove all history associated with this loan.",
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/hr/loans/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: res.success,
                                icon: 'success',
                                confirmButtonColor: '#3b82f6'
                            }).then(() => location.reload());
                        }
                    });
                }
            });
        }

        function scheduleDeductionModal(id, remaining) {
            $('#schedule_loan_id').val(id);
            $('#max_sched_amount').text(remaining);
            $('#schedule_amount').attr('max', remaining);
            $('#scheduleModal').modal('show');
        }
    </script>
@endsection
