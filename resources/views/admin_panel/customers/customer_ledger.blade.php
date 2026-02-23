@extends('admin_panel.layout.app')

@section('content')
    <style>
        .ledger-card {
            border-top: 4px solid #0d6efd;
        }

        .table-ledger thead th {
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            color: #fff;
            font-size: .8rem;
            letter-spacing: .05em;
        }

        .debit-val {
            color: #166534;
            font-weight: 700;
        }

        .credit-val {
            color: #991b1b;
            font-weight: 700;
        }

        .balance-dr {
            color: #1d4ed8;
        }

        .balance-cr {
            color: #dc2626;
        }

        .summary-pill {
            border-radius: 12px;
            padding: 14px 20px;
        }

        .source-badge {
            font-size: .7rem;
            padding: 2px 8px;
            border-radius: 20px;
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container-fluid mt-4">

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark"><i class="fas fa-book-open text-primary me-2"></i>Customer Ledger
                        </h4>
                        <p class="text-muted mb-0 small">Full double-entry journal view per customer — showing all Debit
                            &amp; Credit movements.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Customers
                        </a>
                        @if (request('customer_id'))
                            <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-print"></i> Print
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Filters -->
                <div class="card shadow-sm mb-4 border-0 rounded-3">
                    <div class="card-body p-3">
                        <form method="GET" action="{{ route('customers.ledger') }}" class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-secondary small mb-1">Customer</label>
                                <select name="customer_id" class="form-control select2">
                                    <option value="">-- Select Customer --</option>
                                    @foreach ($customers as $cust)
                                        <option value="{{ $cust->id }}"
                                            {{ request('customer_id') == $cust->id ? 'selected' : '' }}>
                                            {{ $cust->customer_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-secondary small mb-1">From Date</label>
                                <input type="date" name="from_date" value="{{ request('from_date') }}"
                                    class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-secondary small mb-1">To Date</label>
                                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('customers.ledger') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                @if (request('customer_id') && $CustomerLedgers->count() > 0)
                    @php
                        $totalDebit = $CustomerLedgers->sum('debit');
                        $totalCredit = $CustomerLedgers->sum('credit');
                        $closingBal = $CustomerLedgers->last()->closing_balance ?? 0;
                        $customer = $CustomerLedgers->first()->customer ?? null;
                    @endphp

                    <!-- Summary KPIs -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="summary-pill bg-primary-subtle border border-primary-subtle h-100">
                                <small class="text-muted d-block">Customer</small>
                                <span class="fw-bold text-primary fs-6">{{ $customer->customer_name ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-pill bg-success-subtle border border-success-subtle h-100">
                                <small class="text-muted d-block">Total Debit (Dr)</small>
                                <span class="fw-bold text-success fs-5">{{ number_format($totalDebit, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-pill bg-danger-subtle border border-danger-subtle h-100">
                                <small class="text-muted d-block">Total Credit (Cr)</small>
                                <span class="fw-bold text-danger fs-5">{{ number_format($totalCredit, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div
                                class="summary-pill {{ $closingBal >= 0 ? 'bg-info-subtle border-info-subtle' : 'bg-warning-subtle border-warning-subtle' }} border h-100">
                                <small class="text-muted d-block">Closing Balance</small>
                                <span class="fw-bold {{ $closingBal >= 0 ? 'text-info' : 'text-warning' }} fs-5">
                                    {{ number_format(abs($closingBal), 2) }}
                                    <small class="fs-6">{{ $closingBal >= 0 ? 'Dr' : 'Cr' }}</small>
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Ledger Table -->
                <div class="card shadow-sm ledger-card border-0 rounded-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 table-ledger" id="ledger-table">
                                <thead>
                                    <tr>
                                        <th class="ps-3" style="width:4%">#</th>
                                        <th style="width:10%">Date</th>
                                        <th style="width:15%">Customer</th>
                                        <th style="width:35%">Description / Particulars</th>
                                        <th style="width:12%" class="text-end">Debit (Dr)</th>
                                        <th style="width:12%" class="text-end">Credit (Cr)</th>
                                        <th style="width:12%" class="text-end pe-3">Running Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($CustomerLedgers as $key => $ledger)
                                        @php
                                            $debit = $ledger->debit ?? 0;
                                            $credit = $ledger->credit ?? 0;
                                            $balance = $ledger->closing_balance;
                                            $suffix = $balance >= 0 ? 'Dr' : 'Cr';
                                        @endphp
                                        <tr>
                                            <td class="ps-3 text-muted fw-bold">{{ $loop->iteration }}</td>
                                            <td>
                                                <span
                                                    class="fw-medium">{{ \Carbon\Carbon::parse($ledger->created_at)->format('d M Y') }}</span>
                                            </td>
                                            <td class="fw-semibold">
                                                {{ $ledger->customer->customer_name ?? 'N/A' }}
                                            </td>
                                            <td>
                                                <span class="text-dark">{{ $ledger->description }}</span>
                                            </td>
                                            <td class="text-end">
                                                @if ($debit > 0)
                                                    <span class="debit-val">{{ number_format($debit, 2) }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if ($credit > 0)
                                                    <span class="credit-val">{{ number_format($credit, 2) }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-3">
                                                <span class="fw-bold {{ $balance >= 0 ? 'balance-dr' : 'balance-cr' }}">
                                                    {{ number_format(abs($balance), 2) }}
                                                </span>
                                                <small
                                                    class="{{ $balance >= 0 ? 'text-primary' : 'text-danger' }}">{{ $suffix }}</small>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                @if (request('customer_id'))
                                                    <i class="fas fa-inbox fa-2x mb-2 d-block text-muted opacity-50"></i>
                                                    No journal entries found for this customer in the selected date range.
                                                @else
                                                    <i
                                                        class="fas fa-user-friends fa-2x mb-2 d-block text-muted opacity-50"></i>
                                                    Please select a customer to view their ledger.
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if ($CustomerLedgers->count() > 0)
                                    <tfoot>
                                        <tr class="table-light fw-bold">
                                            <td colspan="4" class="ps-3 text-end text-secondary">Totals:</td>
                                            <td class="text-end text-success">
                                                {{ number_format($CustomerLedgers->sum('debit'), 2) }}</td>
                                            <td class="text-end text-danger">
                                                {{ number_format($CustomerLedgers->sum('credit'), 2) }}</td>
                                            <td class="text-end pe-3">
                                                @php $cb = $CustomerLedgers->last()->closing_balance ?? 0; @endphp
                                                <span class="{{ $cb >= 0 ? 'text-primary' : 'text-danger' }}">
                                                    {{ number_format(abs($cb), 2) }} {{ $cb >= 0 ? 'Dr' : 'Cr' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            if ($('.select2').length > 0) {
                $('.select2').select2({
                    width: '100%'
                });
            }
        });
    </script>
@endpush
