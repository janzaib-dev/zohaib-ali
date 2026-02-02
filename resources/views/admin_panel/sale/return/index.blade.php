@extends('admin_panel.layout.app')
@section('content')
    <style>
        :root {
            --primary: #4a69bd;
            --success: #10ac84;
            --danger: #ee5a6f;
            --warning: #f79f1f;
            --info: #3498db;
            --bg-light: #f5f6fa;
        }

        .returns-container {
            padding: 2rem 1rem;
        }

        .returns-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .card-header-custom {
            background: linear-gradient(135deg, var(--primary), #6a89cc);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header-custom h4 {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .returns-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .returns-table thead {
            background: var(--bg-light);
        }

        .returns-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #dee2e6;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .returns-table td {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .returns-table tbody tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
            border: 1px solid #28a745;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #dc3545;
        }

        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #17a2b8;
        }

        .action-btn {
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.875rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            margin: 0 0.25rem;
        }

        .btn-view {
            background: var(--info);
            color: white;
        }

        .btn-view:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn-approve {
            background: var(--success);
            color: white;
        }

        .btn-approve:hover {
            background: #0e9c75;
            transform: translateY(-2px);
        }

        .btn-reject {
            background: var(--danger);
            color: white;
        }

        .btn-reject:hover {
            background: #d63447;
            transform: translateY(-2px);
        }

        .return-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .return-id {
            font-weight: 700;
            color: var(--primary);
            font-size: 1rem;
        }

        .return-date {
            font-size: 0.75rem;
            color: #7f8c8d;
        }

        .customer-info {
            font-weight: 600;
            color: #2c3e50;
        }

        .amount-display {
            font-weight: 700;
            color: var(--danger);
            font-size: 1.1rem;
        }

        .filter-section {
            padding: 1.5rem 2rem;
            background: var(--bg-light);
            border-bottom: 1px solid #dee2e6;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            border: 2px solid transparent;
            background: white;
            margin: 0 0.25rem;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .filter-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .filter-btn:hover {
            border-color: var(--primary);
        }

        .stats-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stats-label {
            font-size: 0.875rem;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .quality-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .quality-good {
            background: #d4edda;
            color: #155724;
        }

        .quality-damaged {
            background: #fff3cd;
            color: #856404;
        }

        .quality-defective {
            background: #f8d7da;
            color: #721c24;
        }

        .quality-pending {
            background: #e2e3e5;
            color: #383d41;
        }
    </style>

    <div class="returns-container">
        <!-- Stats Row -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number">{{ $stats['total'] ?? 0 }}</div>
                    <div class="stats-label">Total Returns</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-warning">{{ $stats['pending'] ?? 0 }}</div>
                    <div class="stats-label">Pending</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-success">{{ $stats['approved'] ?? 0 }}</div>
                    <div class="stats-label">Approved</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-danger">{{ $stats['rejected'] ?? 0 }}</div>
                    <div class="stats-label">Rejected</div>
                </div>
            </div>
        </div>

        <div class="returns-card">
            <div class="card-header-custom">
                <h4>
                    <i class="fas fa-undo-alt"></i>
                    Sale Returns Management
                </h4>
                <a href="{{ route('sale.add') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-1"></i>New Sale
                </a>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-muted">Filter:</span>
                    <button class="filter-btn active" onclick="filterReturns('all')">
                        <i class="fas fa-list me-1"></i>All
                    </button>
                    <button class="filter-btn" onclick="filterReturns('pending')">
                        <i class="fas fa-clock me-1"></i>Pending
                    </button>
                    <button class="filter-btn" onclick="filterReturns('approved')">
                        <i class="fas fa-check me-1"></i>Approved
                    </button>
                    <button class="filter-btn" onclick="filterReturns('rejected')">
                        <i class="fas fa-times me-1"></i>Rejected
                    </button>
                    <button class="filter-btn" onclick="filterReturns('completed')">
                        <i class="fas fa-check-double me-1"></i>Completed
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="returns-table">
                        <thead>
                            <tr>
                                <th>Return Info</th>
                                <th>Invoice / Customer</th>
                                <th>Items / Amount</th>
                                <th>Quality</th>
                                <th>Status</th>
                                <th>Deadline</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesReturns as $return)
                                <tr data-status="{{ $return->return_status ?? 'pending' }}">
                                    <!-- Return Info -->
                                    <td>
                                        <div class="return-info">
                                            <span class="return-id">#{{ $return->id }}</span>
                                            <span class="return-date">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                {{ $return->created_at->format('d M, Y h:i A') }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Invoice / Customer -->
                                    <td>
                                        <div class="return-info">
                                            <span class="text-muted small">Invoice:</span>
                                            <span class="fw-bold">{{ $return->sale->invoice_no ?? 'N/A' }}</span>
                                            <span class="text-muted small mt-1">Customer:</span>
                                            @php
                                                $customer =
                                                    $return->sale->customer_relation ?? $return->customer_relation;
                                            @endphp
                                            <span class="customer-info">{{ $customer->customer_name ?? 'N/A' }}</span>
                                            <span class="text-muted small">
                                                <i class="fas fa-phone me-1"></i>{{ $customer->mobile ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Items / Amount -->
                                    <td>
                                        <div class="return-info">
                                            <span class="text-muted small">Items:</span>
                                            <span class="fw-bold">{{ $return->total_items }} pieces</span>
                                            <span class="text-muted small mt-1">Refund:</span>
                                            <span class="amount-display">PKR
                                                {{ number_format($return->total_net, 2) }}</span>
                                        </div>
                                    </td>

                                    <!-- Quality -->
                                    <td>
                                        @php
                                            $quality = $return->quality_status ?? 'pending_inspection';
                                            $qualityClass = 'quality-' . str_replace('_', '-', $quality);
                                            $qualityLabel = ucfirst(str_replace('_', ' ', $quality));
                                        @endphp
                                        <span class="quality-badge {{ $qualityClass }}">
                                            @if ($quality == 'good')
                                                <i class="fas fa-check-circle me-1"></i>
                                            @elseif($quality == 'damaged')
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                            @elseif($quality == 'defective')
                                                <i class="fas fa-times-circle me-1"></i>
                                            @else
                                                <i class="fas fa-hourglass-half me-1"></i>
                                            @endif
                                            {{ $qualityLabel }}
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        @php
                                            $status = $return->return_status ?? 'pending';
                                            $statusClass = 'status-' . $status;
                                        @endphp
                                        <span class="status-badge {{ $statusClass }}">
                                            @if ($status == 'pending')
                                                <i class="fas fa-clock me-1"></i>Pending
                                            @elseif($status == 'approved')
                                                <i class="fas fa-check me-1"></i>Approved
                                            @elseif($status == 'rejected')
                                                <i class="fas fa-times me-1"></i>Rejected
                                            @else
                                                <i class="fas fa-check-double me-1"></i>Completed
                                            @endif
                                        </span>
                                    </td>

                                    <!-- Deadline -->
                                    <td>
                                        @if ($return->is_within_deadline)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Within
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-exclamation me-1"></i>Expired
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="text-center">
                                        <a href="{{ route('sale.return.detail', $return->id) }}"
                                            class="action-btn btn-view" title="View Details">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>

                                        @if (($return->return_status ?? 'pending') == 'pending')
                                            @can('sales.edit')
                                                <button class="action-btn btn-approve"
                                                    onclick="approveReturn({{ $return->id }})" title="Approve Return">
                                                    <i class="fas fa-check me-1"></i>Approve
                                                </button>
                                                <button class="action-btn btn-reject"
                                                    onclick="rejectReturn({{ $return->id }})" title="Reject Return">
                                                    <i class="fas fa-times me-1"></i>Reject
                                                </button>
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x mb-3" style="color: #ccc;"></i>
                                        <p class="text-muted">No sale returns found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>Approve Return
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="approveForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to approve this return?</p>
                        <p class="text-muted small">This will restore stock and process the refund.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i>Approve
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle me-2"></i>Reject Return
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to reject this return?</p>
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="3" required
                                placeholder="Enter reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-1"></i>Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Filter returns by status
        function filterReturns(status) {
            const rows = document.querySelectorAll('tbody tr[data-status]');
            const buttons = document.querySelectorAll('.filter-btn');

            // Update active button
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.closest('.filter-btn').classList.add('active');

            // Filter rows
            rows.forEach(row => {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Approve return
        function approveReturn(id) {
            const form = document.getElementById('approveForm');
            form.action = `/sales/sale-return/${id}/approve`;
            const modal = new bootstrap.Modal(document.getElementById('approveModal'));
            modal.show();
        }

        // Reject return
        function rejectReturn(id) {
            const form = document.getElementById('rejectForm');
            form.action = `/sales/sale-return/${id}/reject`;
            const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
            modal.show();
        }

        // Show success/error messages
        @if (session('success'))
            showNotification('{{ session('success') }}', 'success');
        @endif

        @if (session('error'))
            showNotification('{{ session('error') }}', 'error');
        @endif

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? '#10ac84' : '#ee5a6f'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
            notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
    `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    </script>

    <style>
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>
@endsection
