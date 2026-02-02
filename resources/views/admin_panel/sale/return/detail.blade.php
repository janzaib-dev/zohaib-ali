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

        .detail-container {
            max-width: 1200px;
            margin: 2rem auto;
        }

        .detail-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card-header-custom {
            background: linear-gradient(135deg, var(--primary), #6a89cc);
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header-custom h5 {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-body-custom {
            padding: 1.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .info-label {
            font-size: 0.875rem;
            color: #7f8c8d;
            font-weight: 500;
        }

        .info-value {
            font-size: 1.125rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
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

        .table-custom {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-custom thead {
            background: var(--bg-light);
        }

        .table-custom th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #dee2e6;
        }

        .table-custom td {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
        }

        .table-custom tbody tr:hover {
            background: #f8f9fa;
        }

        .amount-highlight {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .section-divider {
            border-top: 2px solid #e1e8ed;
            margin: 2rem 0;
        }

        .btn-back {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: var(--primary);
            color: white;
        }

        .timeline-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-left: 3px solid var(--primary);
            padding-left: 1.5rem;
            margin-left: 0.5rem;
        }

        .timeline-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .timeline-content {
            flex: 1;
        }

        .timeline-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }

        .timeline-meta {
            font-size: 0.875rem;
            color: #7f8c8d;
        }
    </style>

    <div class="detail-container">
        <!-- Header Card -->
        <div class="detail-card">
            <div class="card-header-custom">
                <h5>
                    <i class="fas fa-undo-alt"></i>
                    Sale Return Details #{{ $saleReturn->id }}
                </h5>
                <a href="{{ route('sale.returns.index') }}" class="btn-back">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>
            <div class="card-body-custom">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Return ID</span>
                        <span class="info-value">#{{ $saleReturn->id }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Original Invoice</span>
                        <span class="info-value">#{{ $sale->invoice_no }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Return Status</span>
                        <span class="status-badge status-{{ $saleReturn->return_status }}">
                            {{ ucfirst($saleReturn->return_status) }}
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Return Date</span>
                        <span class="info-value">{{ $saleReturn->created_at->format('d M, Y h:i A') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Sale Info -->
        <div class="detail-card">
            <div class="card-header-custom">
                <h5><i class="fas fa-user"></i> Customer & Sale Information</h5>
            </div>
            <div class="card-body-custom">
                @php
                    $customer = $sale->customer_relation ?? $saleReturn->customer_relation;
                @endphp
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Customer Name</span>
                        <span class="info-value">{{ $customer->customer_name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Customer Phone</span>
                        <span class="info-value">{{ $customer->mobile ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Original Sale Date</span>
                        <span class="info-value">{{ $sale->created_at->format('d M, Y') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Return Deadline</span>
                        <span class="info-value">
                            {{ $saleReturn->return_deadline ? \Carbon\Carbon::parse($saleReturn->return_deadline)->format('d M, Y') : 'N/A' }}
                            @if ($saleReturn->is_within_deadline)
                                <span class="badge bg-success ms-2">Within Deadline</span>
                            @else
                                <span class="badge bg-danger ms-2">Past Deadline</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Return Items -->
        <div class="detail-card">
            <div class="card-header-custom">
                <h5><i class="fas fa-box"></i> Returned Items</h5>
            </div>
            <div class="card-body-custom">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Code</th>
                            <th>Brand</th>
                            <th>Unit</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($returnItems as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $item['product_name'] }}</strong></td>
                                <td>{{ $item['product_code'] }}</td>
                                <td>{{ $item['brand'] }}</td>
                                <td>{{ $item['unit'] }}</td>
                                <td class="text-end">{{ number_format($item['price'], 2) }}</td>
                                <td class="text-end"><strong>{{ $item['quantity'] }}</strong></td>
                                <td class="text-end">{{ number_format($item['discount'], 2) }}</td>
                                <td class="text-end"><strong>PKR {{ number_format($item['total'], 2) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="detail-card">
            <div class="card-header-custom">
                <h5><i class="fas fa-calculator"></i> Financial Summary</h5>
            </div>
            <div class="card-body-custom">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Subtotal</span>
                        <span class="info-value">PKR {{ number_format($saleReturn->total_bill_amount, 2) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Extra Discount</span>
                        <span class="info-value">PKR {{ number_format($saleReturn->total_extradiscount, 2) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Items</span>
                        <span class="info-value">{{ $saleReturn->total_items }} pieces</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Net Refund Amount</span>
                        <span class="amount-highlight">PKR {{ number_format($saleReturn->total_net, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quality Check -->
        <div class="detail-card">
            <div class="card-header-custom">
                <h5><i class="fas fa-check-circle"></i> Quality Inspection</h5>
            </div>
            <div class="card-body-custom">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Quality Status</span>
                        <span class="status-badge quality-{{ $saleReturn->quality_status }}">
                            {{ ucfirst(str_replace('_', ' ', $saleReturn->quality_status)) }}
                        </span>
                    </div>
                    @if ($inspector)
                        <div class="info-item">
                            <span class="info-label">Inspected By</span>
                            <span class="info-value">{{ $inspector->name }}</span>
                        </div>
                    @endif
                    @if ($saleReturn->inspection_notes)
                        <div class="info-item" style="grid-column: 1 / -1;">
                            <span class="info-label">Inspection Notes</span>
                            <span class="info-value">{{ $saleReturn->inspection_notes }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Approval Info -->
        @if ($saleReturn->return_status !== 'pending')
            <div class="detail-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-user-check"></i> Approval Information</h5>
                </div>
                <div class="card-body-custom">
                    <div class="info-grid">
                        @if ($approver)
                            <div class="info-item">
                                <span class="info-label">Approved/Rejected By</span>
                                <span class="info-value">{{ $approver->name }}</span>
                            </div>
                        @endif
                        @if ($saleReturn->approved_at)
                            <div class="info-item">
                                <span class="info-label">Action Date</span>
                                <span class="info-value">{{ $saleReturn->approved_at->format('d M, Y h:i A') }}</span>
                            </div>
                        @endif
                        @if ($saleReturn->rejection_reason)
                            <div class="info-item" style="grid-column: 1 / -1;">
                                <span class="info-label">Rejection Reason</span>
                                <span class="info-value text-danger">{{ $saleReturn->rejection_reason }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Return Notes -->
        @if ($saleReturn->return_note)
            <div class="detail-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-sticky-note"></i> Return Notes</h5>
                </div>
                <div class="card-body-custom">
                    <p style="margin: 0; line-height: 1.6;">{{ $saleReturn->return_note }}</p>
                </div>
            </div>
        @endif

        <!-- Payment Details -->
        @if ($payments->count() > 0)
            <div class="detail-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-money-bill-wave"></i> Refund Payments</h5>
                </div>
                <div class="card-body-custom">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date }}</td>
                                    <td>{{ $payment->payment_method }}</td>
                                    <td><strong>PKR {{ number_format($payment->amount, 2) }}</strong></td>
                                    <td>{{ $payment->note }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Journal Entries -->
        @if ($journalEntries->count() > 0)
            <div class="detail-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-book"></i> Accounting Entries</h5>
                </div>
                <div class="card-body-custom">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Account</th>
                                <th>Description</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($journalEntries as $entry)
                                <tr>
                                    <td>{{ $entry->entry_date }}</td>
                                    <td>{{ $entry->account->title ?? 'N/A' }}</td>
                                    <td>{{ $entry->description }}</td>
                                    <td class="text-end">{{ $entry->debit > 0 ? number_format($entry->debit, 2) : '-' }}
                                    </td>
                                    <td class="text-end">{{ $entry->credit > 0 ? number_format($entry->credit, 2) : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
