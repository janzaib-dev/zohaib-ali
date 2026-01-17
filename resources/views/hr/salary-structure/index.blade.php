@extends('admin_panel.layout.app')

@section('content')
    @include('hr.partials.hr-styles')

    <style>
        .salary-card {
            background: var(--hr-card);
            border: 1px solid var(--hr-border);
            border-radius: 14px;
            padding: 20px;
            transition: all 0.2s;
        }

        .salary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .salary-card.has-salary {
            border-left: 4px solid #22c55e;
        }

        .salary-card.no-salary {
            border-left: 4px solid #ef4444;
        }

        .salary-breakdown {
            background: #f8fafc;
            border-radius: 10px;
            padding: 16px;
            margin-top: 12px;
        }

        .salary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed var(--hr-border);
        }

        .salary-row:last-child {
            border-bottom: none;
            font-weight: 700;
            color: var(--hr-primary);
        }

        .salary-row .label {
            color: var(--hr-muted);
        }

        .salary-type-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .salary-type-badge.salary {
            background: #eef2ff;
            color: #6366f1;
        }

        .salary-type-badge.commission {
            background: #fef3c7;
            color: #d97706;
        }

        .salary-type-badge.hybrid {
            background: #dcfce7;
            color: #16a34a;
        }

        .salary-type-badge.not-set {
            background: #f1f5f9;
            color: #64748b;
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header d-flex justify-content-between align-items-start">
                    <div>
                        <h1 class="page-title"><i class="fa fa-chart-line"></i> Salary Structure</h1>
                        <p class="page-subtitle">Manage employee salary components and structures</p>
                    </div>
                </div>

                <!-- Stats Row -->
                @php
                    $withSalary = $employees->filter(fn($e) => $e->salaryStructure !== null)->count();
                    $withoutSalary = $employees->count() - $withSalary;
                    $totalBase = $employees->sum(fn($e) => $e->salaryStructure?->base_salary ?? 0);
                @endphp
                <div class="stats-row">
                    <div class="stat-card primary">
                        <div class="stat-icon"><i class="fa fa-users"></i></div>
                        <div class="stat-value">{{ $employees->count() }}</div>
                        <div class="stat-label">Total Employees</div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
                        <div class="stat-value">{{ $withSalary }}</div>
                        <div class="stat-label">With Salary</div>
                    </div>
                    <div class="stat-card danger">
                        <div class="stat-icon"><i class="fa fa-exclamation-triangle"></i></div>
                        <div class="stat-value">{{ $withoutSalary }}</div>
                        <div class="stat-label">Without Salary</div>
                    </div>
                    <div class="stat-card info">
                        <div class="stat-icon"><i class="fa fa-coins"></i></div>
                        <div class="stat-value">{{ number_format($totalBase, 0) }}</div>
                        <div class="stat-label">Total Base</div>
                    </div>
                </div>

                <!-- Salary Structure Card -->
                <div class="hr-card">
                    <div class="hr-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="search-box">
                                <i class="fa fa-search"></i>
                                <input type="search" id="salarySearch" placeholder="Search employees...">
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-outline-secondary btn-sm active" data-filter="all">All</button>
                                <button class="btn btn-outline-success btn-sm" data-filter="has">Has Salary</button>
                                <button class="btn btn-outline-danger btn-sm" data-filter="no">No Salary</button>
                            </div>
                        </div>
                        <span class="text-muted small" id="empCount">{{ $employees->count() }} employees</span>
                    </div>

                    <div class="hr-grid" id="salaryGrid">
                        @forelse($employees as $emp)
                            @php
                                $salary = $emp->salaryStructure;
                                $hasSalary = $salary !== null;
                                $net = $hasSalary
                                    ? $salary->base_salary + $salary->total_allowances - $salary->total_deductions
                                    : 0;
                            @endphp
                            <div class="salary-card {{ $hasSalary ? 'has-salary' : 'no-salary' }}"
                                data-id="{{ $emp->id }}" data-name="{{ strtolower($emp->full_name) }}"
                                data-has="{{ $hasSalary ? 'has' : 'no' }}">
                                <div class="hr-item-header">
                                    <div class="d-flex align-items-center">
                                        <div class="hr-avatar">
                                            {{ strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)) }}
                                        </div>
                                        <div class="hr-item-info">
                                            <h4 class="hr-item-name">{{ $emp->full_name }}</h4>
                                            <div class="hr-item-subtitle">{{ $emp->department->name ?? 'N/A' }} •
                                                {{ $emp->designation->name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    @if ($hasSalary)
                                        <span
                                            class="salary-type-badge {{ $salary->salary_type }}">{{ ucfirst($salary->salary_type) }}</span>
                                    @else
                                        <span class="salary-type-badge not-set">Not Set</span>
                                    @endif
                                </div>

                                @if ($hasSalary)
                                    <div class="salary-breakdown">
                                        <div class="salary-row">
                                            <span class="label">Base Salary</span>
                                            <span>Rs. {{ number_format($salary->base_salary, 2) }}</span>
                                        </div>
                                        <div class="salary-row">
                                            <span class="label">Allowances</span>
                                            <span class="text-success">+ Rs.
                                                {{ number_format($salary->total_allowances, 2) }}</span>
                                        </div>
                                        <div class="salary-row">
                                            <span class="label">Deductions</span>
                                            <span class="text-danger">- Rs.
                                                {{ number_format($salary->total_deductions, 2) }}</span>
                                        </div>
                                        <div class="salary-row">
                                            <span class="label">Net Salary</span>
                                            <span>Rs. {{ number_format($net, 2) }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="salary-breakdown text-center">
                                        <i class="fa fa-exclamation-triangle text-warning mb-2"></i>
                                        <p class="mb-0 text-muted">No salary structure assigned</p>
                                    </div>
                                @endif

                                <div class="d-flex justify-content-end mt-3">
                                    @if ($hasSalary)
                                        @if ($canEdit)
                                            <a href="{{ route('hr.salary-structure.edit', $emp->id) }}"
                                                class="btn btn-create btn-sm">
                                                <i class="fa fa-edit me-1"></i> Edit
                                            </a>
                                        @elseif($canView)
                                            <a href="{{ route('hr.salary-structure.edit', $emp->id) }}"
                                                class="btn btn-outline-primary btn-sm">
                                                <i class="fa fa-eye me-1"></i> View
                                            </a>
                                        @endif
                                    @else
                                        @if ($canCreate || $canEdit)
                                            <a href="{{ route('hr.salary-structure.edit', $emp->id) }}"
                                                class="btn btn-create btn-sm">
                                                <i class="fa fa-plus me-1"></i> Assign Salary
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="empty-state" style="grid-column: 1/-1;">
                                <i class="fa fa-chart-line"></i>
                                <p>No employees found.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $('[data-filter]').click(function() {
                $(this).addClass('active').siblings().removeClass('active');
                var filter = $(this).data('filter');

                $('.salary-card').each(function() {
                    if (filter === 'all') {
                        $(this).show();
                    } else {
                        $(this).toggle($(this).data('has') === filter);
                    }
                });
                updateCount();
            });

            function updateCount() {
                $('#empCount').text($('.salary-card:visible').length + ' employees');
            }

            $('#salarySearch').on('input', function() {
                var q = $(this).val().toLowerCase();
                $('.salary-card').each(function() {
                    var name = $(this).data('name') || '';
                    $(this).toggle(name.indexOf(q) !== -1);
                });
                updateCount();
            });
        });
    </script>
@endsection
