@extends('admin_panel.layout.app')
@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        .erp-page {
            background: #f8fafc;
        }

        .erp-page-header {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            border-radius: 16px;
            padding: 22px 28px;
            color: white;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .erp-page-header .title-block {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .erp-page-header .icon-box {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .erp-page-header h4 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -0.3px;
        }

        .erp-page-header p {
            margin: 0;
            font-size: 0.82rem;
            opacity: 0.85;
        }

        .btn-add {
            background: rgba(255, 255, 255, 0.18);
            border: 1.5px solid rgba(255, 255, 255, 0.5);
            color: white;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 0.88rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.2s;
            backdrop-filter: blur(4px);
        }

        .btn-add:hover {
            background: white;
            color: #0ea5e9;
        }

        /* Summary cards */
        .summary-strip {
            display: flex;
            gap: 14px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        .summary-strip .s-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 22px;
            flex: 1;
            min-width: 160px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .summary-strip .s-card .s-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 600;
        }

        .summary-strip .s-card .s-value {
            font-size: 1.4rem;
            font-weight: 800;
            color: #0f172a;
            margin-top: 4px;
            font-family: 'Courier New', monospace;
        }

        .summary-strip .s-card .s-icon {
            font-size: 1.5rem;
            float: right;
            color: #0ea5e9;
        }

        /* Table card */
        .erp-table-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .erp-table-card .card-top {
            padding: 16px 22px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
        }

        .erp-table-card .card-top h6 {
            margin: 0;
            font-size: 0.88rem;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .erp-table-card .card-top h6 i {
            color: #0ea5e9;
        }

        .erp-dt-table {
            width: 100%;
            border-collapse: collapse;
        }

        .erp-dt-table thead th {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            color: white;
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            padding: 12px 14px;
            border: none;
            white-space: nowrap;
        }

        .erp-dt-table tbody td {
            padding: 11px 14px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.87rem;
            color: #374151;
            vertical-align: middle;
        }

        .erp-dt-table tbody tr:hover {
            background: #f0f9ff;
        }

        .erp-dt-table tbody tr:last-child td {
            border-bottom: none;
        }

        .voucher-no {
            font-weight: 700;
            color: #0284c7;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }

        .party-cell strong {
            display: block;
            color: #1e293b;
            font-size: 0.88rem;
        }

        .party-cell small {
            color: #64748b;
            font-size: 0.78rem;
        }

        .amount-cell {
            font-weight: 700;
            color: #0f172a;
            font-family: 'Courier New', monospace;
            text-align: right;
        }

        .badge-type {
            background: #e0f2fe;
            color: #0369a1;
            border-radius: 6px;
            padding: 3px 9px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-posted {
            background: #dcfce7;
            color: #15803d;
            border-radius: 6px;
            padding: 3px 9px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-draft {
            background: #f1f5f9;
            color: #64748b;
            border-radius: 6px;
            padding: 3px 9px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-other {
            background: #fef2f2;
            color: #dc2626;
            border-radius: 6px;
            padding: 3px 9px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .btn-print {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #ef4444;
            border-radius: 7px;
            padding: 5px 10px;
            font-size: 0.82rem;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-print:hover {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
        }
    </style>

    <div class="main-content erp-page">
        <div class="container-fluid">

            {{-- Page Header --}}
            <div class="erp-page-header">
                <div class="title-block">
                    <div class="icon-box"><i class="bi bi-receipt"></i></div>
                    <div>
                        <h4>Receipt Vouchers</h4>
                        <p>All incoming cash & bank receipts from customers / parties</p>
                    </div>
                </div>
                @can('receipts.voucher.create')
                    <a href="{{ route('recepit_vochers') }}" class="btn-add">
                        <i class="bi bi-plus-circle"></i> New Receipt Voucher
                    </a>
                @endcan
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Summary Strip --}}
            @php
                $totalAmt = $receipts->sum('total_amount');
                $totalCount = $receipts->count();
                $todayCount = $receipts->filter(fn($r) => optional($r->date)->isToday())->count();
            @endphp
            <div class="summary-strip">
                <div class="s-card">
                    <i class="bi bi-collection s-icon"></i>
                    <div class="s-label">Total Vouchers</div>
                    <div class="s-value">{{ $totalCount }}</div>
                </div>
                <div class="s-card">
                    <i class="bi bi-cash-stack s-icon"></i>
                    <div class="s-label">Total Received</div>
                    <div class="s-value">{{ number_format($totalAmt, 0) }}</div>
                </div>
                <div class="s-card">
                    <i class="bi bi-calendar-day s-icon"></i>
                    <div class="s-label">Today's Entries</div>
                    <div class="s-value">{{ $todayCount }}</div>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="erp-table-card">
                <div class="card-top">
                    <h6><i class="bi bi-table"></i> Voucher List</h6>
                    <span class="text-muted" style="font-size:0.8rem;">{{ $totalCount }} records</span>
                </div>
                <div class="table-responsive p-3">
                    <table id="example" class="erp-dt-table w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Voucher No</th>
                                <th>Date</th>
                                <th>Type / Party</th>
                                <th>Remarks</th>
                                <th>Status</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($receipts as $item)
                                <tr>
                                    <td class="text-muted" style="font-size:0.8rem;">{{ $loop->iteration }}</td>
                                    <td><span class="voucher-no">{{ $item->voucher_no }}</span></td>
                                    <td>
                                        <span
                                            style="font-size:0.85rem;color:#374151;">{{ $item->date ? $item->date->format('d M Y') : '-' }}</span>
                                    </td>
                                    <td>
                                        <div class="party-cell">
                                            <strong>{{ $item->party_name }}</strong>
                                            <small><span
                                                    class="badge-type">{{ ucfirst($item->payment_from ?? 'Receipt') }}</span></small>
                                        </div>
                                    </td>
                                    <td style="max-width:200px;color:#64748b;font-size:0.83rem;">
                                        {{ Str::limit($item->remarks, 55) }}</td>
                                    <td>
                                        @if ($item->status == 'posted')
                                            <span class="badge-posted"><i class="bi bi-check2-circle me-1"></i>Posted</span>
                                        @elseif($item->status == 'draft')
                                            <span class="badge-draft"><i class="bi bi-pencil me-1"></i>Draft</span>
                                        @else
                                            <span class="badge-other">{{ ucfirst($item->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="amount-cell">Rs {{ number_format($item->total_amount, 2) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('print', $item->id) }}" target="_blank" class="btn-print"
                                            title="Print Voucher">
                                            <i class="bi bi-printer"></i> Print
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#example').DataTable({
                order: [
                    [0, 'desc']
                ],
                pageLength: 25,
                language: {
                    search: '<i class="bi bi-search"></i> Search:',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ vouchers',
                    emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-inbox fs-3 d-block mb-2"></i>No vouchers found</div>'
                },
                dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rtip',
            });
        });
    </script>
@endsection
