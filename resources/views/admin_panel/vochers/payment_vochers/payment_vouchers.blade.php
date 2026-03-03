@extends('admin_panel.layout.app')
@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        .voucher-page {
            background: #f8fafc;
        }

        .voucher-header-bar {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 16px;
            padding: 20px 28px;
            color: white;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .voucher-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .voucher-card-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .voucher-card-header i {
            color: #6366f1;
            font-size: 1rem;
        }

        .voucher-card-body {
            padding: 24px;
        }

        .erp-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 5px;
            display: block;
        }

        .erp-input,
        .erp-select {
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 9px 13px;
            font-size: 0.9rem;
            color: #1e293b;
            width: 100%;
            background: #fff;
            transition: border-color 0.2s;
        }

        .erp-input:focus,
        .erp-select:focus {
            border-color: #6366f1;
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .erp-input[readonly] {
            background: #f1f5f9;
            cursor: not-allowed;
            color: #64748b;
        }

        .voucher-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .voucher-table thead tr th {
            background: linear-gradient(135deg, #1e293b, #334155);
            color: #e2e8f0;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 11px 14px;
            border: none;
            white-space: nowrap;
        }

        .voucher-table thead tr th:first-child {
            border-radius: 10px 0 0 0;
        }

        .voucher-table thead tr th:last-child {
            border-radius: 0 10px 0 0;
        }

        .voucher-table tbody tr td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .voucher-table tbody tr:hover {
            background: #fafbff;
        }

        .voucher-table tbody tr td input,
        .voucher-table tbody tr td select {
            border: 1.5px solid #e2e8f0;
            border-radius: 7px;
            padding: 7px 10px;
            font-size: 0.88rem;
            width: 100%;
            background: #fff;
            color: #1e293b;
            transition: border-color 0.2s;
        }

        .voucher-table tbody tr td input:focus,
        .voucher-table tbody tr td select:focus {
            border-color: #6366f1;
            outline: none;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
        }

        .amount-input {
            text-align: right;
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }

        .voucher-footer-row {
            background: #f8fafc;
            border-top: 2px solid #e2e8f0;
        }

        .total-box {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border-radius: 12px;
            padding: 14px 20px;
            text-align: right;
            min-width: 160px;
        }

        .total-box-label {
            font-size: 0.75rem;
            opacity: 0.85;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .total-box-value {
            font-size: 1.5rem;
            font-weight: 800;
            font-family: 'Courier New', monospace;
        }

        .btn-remove-row {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #ef4444;
            border-radius: 7px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-remove-row:hover {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
        }

        .btn-add-row {
            background: #f0fdf4;
            border: 1.5px dashed #86efac;
            color: #16a34a;
            border-radius: 9px;
            padding: 9px 18px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-add-row:hover {
            background: #16a34a;
            color: white;
            border-color: #16a34a;
        }

        .action-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
            padding: 20px 24px;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .btn-save {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 11px 32px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-save:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-cancel {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 11px 24px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
            color: #374151;
        }
    </style>

    <div class="main-content voucher-page">
        <div class="container-fluid">

            {{-- Page Header --}}
            <div class="voucher-header-bar">
                <div>
                    <div style="font-size:1.2rem;font-weight:800;letter-spacing:-0.3px;">
                        <i class="bi bi-cash-stack me-2"></i>Payment Voucher
                    </div>
                    <div style="font-size:0.85rem;opacity:0.85;margin-top:3px;">Record outgoing cash / bank payments to
                        parties</div>
                </div>
                <a href="{{ route('all_Payment_vochers') }}" class="btn-cancel"
                    style="color:#6366f1;background:rgba(255,255,255,0.18);border-color:rgba(255,255,255,0.3);">
                    <i class="bi bi-list-ul me-1"></i> All Vouchers
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('store_Pay_vochers') }}" method="POST">
                @csrf

                {{-- Header Info Card --}}
                <div class="voucher-card mb-3">
                    <div class="voucher-card-header">
                        <i class="bi bi-info-circle-fill"></i> Voucher Information
                    </div>
                    <div class="voucher-card-body">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="erp-label">Voucher No.</label>
                                <input type="text" class="erp-input" name="pvid" value="{{ $nextPVID }}" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="erp-label">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="receipt_date" class="erp-input"
                                    value="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="erp-label">Entry Date</label>
                                <input type="date" name="entry_date" class="erp-input"
                                    value="{{ now()->toDateString() }}">
                            </div>
                            <div class="col-md-3">
                                <label class="erp-label">Paid From — Account Head <span class="text-danger">*</span></label>
                                <select name="header_account_head" class="erp-select" id="payFromHead">
                                    <option value="">Select Head</option>
                                    @foreach ($AccountHeads as $head)
                                        <option value="{{ $head->id }}">{{ $head->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="erp-label">Account <span class="text-danger">*</span></label>
                                <select name="header_account_id" class="erp-select" id="payFromAccount">
                                    <option disabled selected>Select Account</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="erp-label">Global Remarks / Narration</label>
                                <input type="text" name="remarks" class="erp-input"
                                    placeholder="e.g. Payment for March supplies...">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Line Items Card --}}
                <div class="voucher-card mb-3">
                    <div class="voucher-card-header">
                        <i class="bi bi-table"></i> Payment Allocations
                    </div>
                    <div class="voucher-card-body" style="padding:0;">
                        <div class="table-responsive">
                            <table class="voucher-table" id="voucherTable">
                                <thead>
                                    <tr>
                                        <th style="width:25%">Narration</th>
                                        <th style="width:15%">Reference #</th>
                                        <th style="width:20%">Party Type</th>
                                        <th style="width:25%">Party / Account</th>
                                        <th style="width:10%" class="text-end">Amount</th>
                                        <th style="width:5%" class="text-center">Del</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select name="narration_id[]" class="narrationSelect"
                                                style="border:1.5px solid #e2e8f0;border-radius:7px;padding:7px 10px;font-size:0.88rem;width:100%;">
                                                <option value="">Select / Add</option>
                                                @foreach ($narrations as $id => $name)
                                                    <option value="{{ $id }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" class="narrationInput" name="narration_text[]"
                                                style="display:none;margin-top:4px;border:1.5px solid #e2e8f0;border-radius:7px;padding:7px 10px;font-size:0.88rem;width:100%;"
                                                placeholder="Custom Narration...">
                                        </td>
                                        <td><input type="text" name="reference_no[]" placeholder="Ref #"></td>
                                        <td>
                                            <select name="vendor_type[]" class="rowType">
                                                <option disabled selected>Select</option>
                                                <option value="vendor">Vendor</option>
                                                <option value="customer">Customer</option>
                                                <option value="walkin">Walk-in</option>
                                                @foreach ($AccountHeads as $head)
                                                    <option value="{{ $head->id }}">{{ $head->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="vendor_id[]" class="rowParty">
                                                <option disabled selected>Select Party</option>
                                            </select>
                                        </td>
                                        <td><input type="number" name="amount[]" class="amount amount-input"
                                                placeholder="0.00"></td>
                                        <td class="text-center">
                                            <button type="button" class="btn-remove-row removeRow"><i
                                                    class="bi bi-x-lg"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="voucher-footer-row">
                                    <tr>
                                        <td colspan="4" style="padding:12px 16px;">
                                            <button type="button" class="btn-add-row" id="addNewRow">
                                                <i class="bi bi-plus-circle"></i> Add Line
                                            </button>
                                        </td>
                                        <td style="padding:12px 10px;" class="text-end">
                                            <div class="total-box d-inline-block" style="min-width:130px;">
                                                <div class="total-box-label">Total</div>
                                                <div class="total-box-value" id="totalAmountDisplay">0.00</div>
                                            </div>
                                            <input type="hidden" name="total_amount" id="totalAmount" value="0.00">
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Action Bar --}}
                <div class="action-bar" style="background:transparent;border:none;padding:8px 0;">
                    <a href="{{ route('all_Payment_vochers') }}" class="btn-cancel">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn-save">
                        <i class="bi bi-save"></i> Save Payment Voucher
                    </button>
                </div>
            </form>

        </div>
    </div>
@endsection

@section('js')
    <script>
        // Header Source Account
        $('#payFromHead').on('change', function() {
            let headId = $(this).val();
            let $accSelect = $('#payFromAccount');
            $accSelect.html('<option disabled selected>Loading...</option>');
            if (headId) {
                $.get('{{ url('get-accounts-by-head') }}/' + headId, function(data) {
                    $accSelect.empty().append('<option disabled selected>Select Account</option>');
                    data.forEach(function(acc) {
                        $accSelect.append(`<option value="${acc.id}">${acc.title}</option>`);
                    });
                });
            }
        });

        function getRowTemplate() {
            let narrationOptions = `<option value="">Select / Add</option>`;
            @foreach ($narrations as $id => $name)
                narrationOptions += `<option value="{{ $id }}">{{ $name }}</option>`;
            @endforeach
            let headOptions =
                `<option disabled selected>Select</option><option value="vendor">Vendor</option><option value="customer">Customer</option><option value="walkin">Walk-in</option>`;
            @foreach ($AccountHeads as $head)
                headOptions += `<option value="{{ $head->id }}">{{ $head->name }}</option>`;
            @endforeach

            return `
        <tr>
            <td>
                <select name="narration_id[]" class="narrationSelect" style="border:1.5px solid #e2e8f0;border-radius:7px;padding:7px 10px;font-size:0.88rem;width:100%;">${narrationOptions}</select>
                <input type="text" class="narrationInput" name="narration_text[]" style="display:none;margin-top:4px;border:1.5px solid #e2e8f0;border-radius:7px;padding:7px 10px;font-size:0.88rem;width:100%;" placeholder="Custom Narration...">
            </td>
            <td><input type="text" name="reference_no[]" style="border:1.5px solid #e2e8f0;border-radius:7px;padding:7px 10px;font-size:0.88rem;width:100%;" placeholder="Ref #"></td>
            <td><select name="vendor_type[]" class="rowType" style="border:1.5px solid #e2e8f0;border-radius:7px;padding:7px 10px;font-size:0.88rem;width:100%;">${headOptions}</select></td>
            <td><select name="vendor_id[]" class="rowParty" style="border:1.5px solid #e2e8f0;border-radius:7px;padding:7px 10px;font-size:0.88rem;width:100%;"><option disabled selected>Select Party</option></select></td>
            <td><input type="number" name="amount[]" class="amount amount-input" style="border:1.5px solid #e2e8f0;border-radius:7px;padding:7px 10px;font-size:0.88rem;width:100%;text-align:right;font-weight:600;" placeholder="0.00"></td>
            <td class="text-center"><button type="button" class="btn-remove-row removeRow"><i class="bi bi-x-lg"></i></button></td>
        </tr>`;
        }

        $(document).on('change', '.rowType', function() {
            let type = $(this).val();
            let $row = $(this).closest('tr');
            let $select = $row.find('.rowParty');
            $select.html('<option disabled selected>Loading...</option>');
            if (type === 'vendor' || type === 'customer' || type === 'walkin') {
                $.get('{{ route('party.list') }}?type=' + type, function(data) {
                    $select.empty().append('<option disabled selected>Select</option>');
                    data.forEach(function(item) {
                        $select.append(`<option value="${item.id}">${item.text}</option>`);
                    });
                });
            } else if (type) {
                $.get('{{ url('get-accounts-by-head') }}/' + type, function(data) {
                    $select.empty().append('<option disabled selected>Select</option>');
                    data.forEach(function(acc) {
                        $select.append(`<option value="${acc.id}">${acc.title}</option>`);
                    });
                });
            }
        });

        $(document).on('change', '.narrationSelect', function() {
            let $input = $(this).closest('td').find('.narrationInput');
            $(this).val() === '' ? $input.show().focus() : $input.hide().val('');
        });

        function calculateTotal() {
            let total = 0;
            $('.amount').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#totalAmount').val(total.toFixed(2));
            $('#totalAmountDisplay').text(total.toLocaleString('en', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        }
        $(document).on('input', '.amount', calculateTotal);

        $('#addNewRow').on('click', function() {
            $('#voucherTable tbody').append(getRowTemplate());
        });

        $(document).on('click', '.removeRow', function() {
            if ($('#voucherTable tbody tr').length > 1) {
                $(this).closest('tr').remove();
                calculateTotal();
            }
        });
    </script>
@endsection
