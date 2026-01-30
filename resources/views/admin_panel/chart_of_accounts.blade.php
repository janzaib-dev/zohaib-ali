@extends('admin_panel.layout.app')
@section('content')
    <style>
        .custom-table th {
            padding: 8px 6px !important;
            font-weight: 600;
            font-size: 14px;
            background: #212529;
            /* dark header (bootstrap table-dark) */
            color: #fff;
            /* sticky header */
            position: sticky;
            top: 0;
            z-index: 5;
        }

        .custom-table td {
            padding: 4px 6px !important;
            font-size: 13px;
            vertical-align: middle;
        }

        /* Optional: Reduce font size for action buttons */
        .custom-table .btn-sm {
            padding: 2px 6px;
            font-size: 12px;
        }

        /* Wrapper that controls scroll inside table only */
        .table-wrapper {
            height: 400px;
            /* set jo height chahte ho (changeable) */
            overflow-y: auto;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 6px;
        }

        /* Keep header and body columns aligned */
        .custom-table {
            width: 100%;
            table-layout: fixed;
            /* helps alignment when header is sticky */
            border-collapse: collapse;
        }

        /* Make th background solid (sticky header can show transparent without this) */
        .custom-table thead th {
            background-clip: padding-box;
        }

        /* Optional: add subtle shadow to sticky header so it looks separated */
        .custom-table thead th {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }

        /* Responsive: allow horizontal scroll on small screens */
        .table-responsive-fixed {
            overflow-x: auto;
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Chart Of Accounts</h4>
                    @can('chart.of.accounts.create')
                        <div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">➕ Add New
                                Account</button>
                            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addHeadModal">➕ Add
                                Chart
                                Of Accounts</button>
                        </div>
                    @endcan
                </div>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="table-responsive-fixed">
                    <div class="table-wrapper">
                        <table class="table table-bordered custom-table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 4%;">#</th>
                                    <th style="width: 12%;">Account Code</th>
                                    <th style="width: 14%;">Expense Head</th>
                                    <th style="width: 22%;">Account Title</th>
                                    <th style="width: 10%;">Type</th>
                                    <th style="width: 12%;">Opening Balance</th>
                                    <th style="width: 14%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($accounts as $acc)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $acc->account_code }}</td>
                                        <td>{{ $acc->head->name ?? '-' }}</td>
                                        <td>{{ $acc->title }}</td>
                                        <td>{{ $acc->type }}</td>
                                        <td>{{ number_format($acc->opening_balance, 2) }}</td>
                                        <td>
                                            @if ($acc->status)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Add New Account Modal -->
            <div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" action="{{ route('accounts.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addAccountModalLabel">Add New Account</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Select Head (Category)</label>
                                <select class="form-control" name="head_id" required>
                                    <option value="">Select Head</option>
                                    @foreach ($heads as $head)
                                        <option value="{{ $head->id }}">{{ $head->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Account Title</label>
                                <input type="text" name="title" class="form-control" placeholder="e.g., UBL Current"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label>Type</label>
                                <select class="form-control" name="type">
                                    <option value="Debit">Debit</option>
                                    <option value="Credit">Credit</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Opening Balance</label>
                                <input type="number" name="opening_balance" step="0.01" value="0"
                                    class="form-control" required>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="status" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Add Account</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Add Head of Expense Modal -->
            <div class="modal fade" id="addHeadModal" tabindex="-1" aria-labelledby="addHeadModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" action="{{ route('accounts_head.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addHeadModalLabel">Add Head of Expense/Income</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>New Head Name</label>
                                <input type="text" name="name" class="form-control"
                                    placeholder="e.g., Expense, Bank, Assets" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-secondary">Add Head</button>
                        </div>
                    </form>
                </div>
            </div>
        @endsection
