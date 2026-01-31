@extends('admin_panel.layout.app')

@section('content')
    <style>
        .custom-table th {
            padding: 8px 6px !important;
            font-weight: 600;
            font-size: 14px;
            background: #212529;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 5;
        }

        .custom-table td {
            padding: 4px 6px !important;
            font-size: 13px;
            vertical-align: middle;
        }

        .custom-table .btn-sm {
            padding: 2px 6px;
            font-size: 12px;
        }

        .table-wrapper {
            height: 400px;
            overflow-y: auto;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 6px;
        }

        .custom-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .custom-table thead th {
            background-clip: padding-box;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }

        .table-responsive-fixed {
            overflow-x: auto;
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Chart Of Accounts (V2)</h4>
                    @can('chart.of.accounts.create')
                        <div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">➕ Add New
                                Account</button>
                            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addHeadModal">➕ Add
                                Category</button>
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
                                    <th style="width: 10%;">Code</th>
                                    <th style="width: 15%;">Head / Group</th>
                                    <th style="width: 20%;">Account Title</th>
                                    <th style="width: 8%;">Type</th>
                                    <th style="width: 12%;">Balance</th>
                                    <th style="width: 8%;">Status</th>
                                    <th style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($accounts as $acc)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <span
                                                class="badge bg-light text-dark border">{{ $acc->account_code ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $acc->head->name ?? '-' }}</span>
                                            @if ($acc->head && $acc->head->parent_id)
                                                <small
                                                    class="text-muted d-block">({{ $acc->head->parent->name ?? '' }})</small>
                                            @endif
                                        </td>
                                        <td>{{ $acc->title }}</td>
                                        <td>
                                            @if ($acc->type == 'Debit')
                                                <span class="badge bg-primary">Debit</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Credit</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span
                                                class="{{ $acc->current_balance < 0 ? 'text-danger' : 'text-success' }} fw-bold">
                                                {{ number_format(abs($acc->current_balance), 2) }}
                                                <small class="text-muted" style="font-size: 0.7em;">
                                                    {{ $acc->current_balance >= 0 ? 'Dr' : 'Cr' }}
                                                </small>
                                            </span>
                                        </td>
                                        <td>
                                            @if ($acc->status)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('accounts.ledger', $acc->id) }}"
                                                class="btn btn-sm btn-info text-white" title="View Ledger">
                                                <i class="bi bi-eye"></i> Ledger
                                            </a>
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
                                <input type="number" step="0.01" name="opening_balance" class="form-control"
                                    value="0">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Account</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Add Head Modal -->
            <div class="modal fade" id="addHeadModal" tabindex="-1" aria-labelledby="addHeadLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" action="{{ route('account-heads.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addHeadLabel">Add New Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Head Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Head</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
