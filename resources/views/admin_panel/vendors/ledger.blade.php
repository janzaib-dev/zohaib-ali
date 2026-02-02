@extends('admin_panel.layout.app')

@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="container-fluid">
                <div class="page-header row mb-3">
                    <div class="page-title col-lg-8">
                        <h4>Vendor Ledger: {{ $vendor->name }}</h4>
                        <h6>Journal-based ledger statement</h6>
                    </div>
                    <div class="col-lg-4 text-end">
                        <a href="{{ route('vendors.index') }}" class="btn btn-secondary">Back to Vendors</a>
                    </div>
                </div>

                <!-- Date Filter -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control"
                                    value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control"
                                    value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="{{ route('vendor.ledger', $vendor->id) }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">Opening Balance</h6>
                                <h3 class="mb-0">Rs. {{ number_format($opening_balance, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card {{ $closing_balance > 0 ? 'bg-danger' : 'bg-success' }} text-white">
                            <div class="card-body">
                                <h6 class="mb-2">Closing Balance</h6>
                                <h3 class="mb-0">
                                    Rs. {{ number_format(abs($closing_balance), 2) }}
                                    {{ $closing_balance > 0 ? '(Payable)' : '(Receivable)' }}
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">Total Transactions</h6>
                                <h3 class="mb-0">{{ $transactions->count() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ledger Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th class="text-end">Debit (Dr)</th>
                                        <th class="text-end">Credit (Cr)</th>
                                        <th class="text-end">Balance</th>
                                        <th>Source</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($transactions->isEmpty())
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No transactions in this period
                                            </td>
                                        </tr>
                                    @else
                                        @foreach ($transactions as $txn)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($txn['date'])->format('d-M-Y') }}</td>
                                                <td>{{ $txn['description'] }}</td>
                                                <td class="text-end">
                                                    {{ $txn['debit'] > 0 ? number_format($txn['debit'], 2) : '-' }}
                                                </td>
                                                <td class="text-end">
                                                    {{ $txn['credit'] > 0 ? number_format($txn['credit'], 2) : '-' }}
                                                </td>
                                                <td
                                                    class="text-end fw-bold {{ $txn['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format(abs($txn['balance']), 2) }}
                                                </td>
                                                <td>
                                                    @if ($txn['source_type'] === 'App\\Models\\Purchase')
                                                        <span class="badge bg-primary">Purchase
                                                            #{{ $txn['source_id'] }}</span>
                                                    @elseif($txn['source_type'] === 'App\\Models\\VoucherMaster')
                                                        <span class="badge bg-success">Payment Voucher</span>
                                                    @else
                                                        <span
                                                            class="text-muted">{{ class_basename($txn['source_type']) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <th colspan="4" class="text-end">Closing Balance:</th>
                                        <th class="text-end {{ $closing_balance > 0 ? 'text-danger' : 'text-success' }}">
                                            Rs. {{ number_format(abs($closing_balance), 2) }}
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Export Buttons -->
                        <div class="mt-3">
                            <button onclick="window.print()" class="btn btn-secondary">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {

            .btn,
            .page-header .col-lg-4,
            .card-body form {
                display: none !important;
            }
        }
    </style>
@endsection
