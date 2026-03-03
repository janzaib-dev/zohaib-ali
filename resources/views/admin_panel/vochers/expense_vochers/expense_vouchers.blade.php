@extends('admin_panel.layout.app')
@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        .voucher-page {
            background: #f8fafc;
        }

        .voucher-header-bar {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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
            color: #ef4444;
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
            border-color: #ef4444;
            outline: none;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .erp-input[readonly] {
            background: #f1f5f9;
            cursor: not-allowed;
            color: #64748b;
        }

        .balance-chip {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 6px;
            color: #15803d;
        }

        .voucher-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .voucher-table thead tr th {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
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
            background: #fff5f5;
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
            border-color: #ef4444;
            outline: none;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.1);
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
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border-radius: 12px;
            padding: 14px 20px;
            text-align: right;
            display: inline-block;
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

        .btn-save {
            background: linear-gradient(135deg, #ef4444, #dc2626);
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
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
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
            display: inline-flex;
            align-items: center;
            gap: 6px;
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
                        <i class="bi bi-wallet2 me-2"></i>Expense Voucher
                    </div>
                    <div style="font-size:0.85rem;opacity:0.85;margin-top:3px;">Record business expenses debited from cash or
                        bank accounts</div>
                </div>
                <a href="{{ route('all_expense_vochers') }}" class="btn-cancel"
                    style="color:#ef4444;background:rgba(255,255,255,0.18);border-color:rgba(255,255,255,0.3);">
                    <i class="bi bi-list-ul me-1"></i> All Expenses
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('store_expense_vochers') }}" method="POST">
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
                                <input type="text" class="erp-input" name="evid" value="{{ $nextRvid }}" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="erp-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="entry_date" class="erp-input"
                                    value="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="erp-label">Paid From — Account Head <span class="text-danger">*</span></label>
                                <select name="vendor_type" class="erp-select" id="payFromHead">
                                    <option value="">Select Head</option>
                                    @foreach ($AccountHeads as $head)
                                        <option value="{{ $head->id }}">{{ $head->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="erp-label">Account <span class="text-danger">*</span></label>
                                <select name="vendor_id" class="erp-select section-account" id="payFromAccount">
                                    <option disabled selected>Select Account</option>
                                </select>
                                <div class="balance-chip mt-2 balance-display" style="display:none;">
                                    <i class="bi bi-wallet2"></i>
                                    Balance: <strong id="balanceVal">0.00</strong>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="erp-label">Reference / Cheque #</label>
                                <input type="text" name="ref_no_header" class="erp-input" placeholder="Optional">
                            </div>
                            <div class="col-12">
                                <label class="erp-label">Global Remarks</label>
                                <input type="text" name="remarks" class="erp-input"
                                    placeholder="Any general notes for this voucher...">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Line Items Card --}}
                <div class="voucher-card mb-3">
                    <div class="voucher-card-header">
                        <i class="bi bi-table"></i> Expense Details
                    </div>
                    <div class="voucher-card-body" style="padding:0;">
                        <div class="table-responsive">
                            <table class="voucher-table" id="voucherTable">
                                <thead>
                                    <tr>
                                        <th style="width:30%">Expense Account Head</th>
                                        <th style="width:25%">Expense Account</th>
                                        <th style="width:30%">Narration</th>
                                        <th style="width:10%" class="text-end">Amount</th>
                                        <th style="width:5%" class="text-center">Del</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select name="row_account_head[]" class="rowAccountHead">
                                                <option value="">Select Head</option>
                                                @foreach ($AccountHeads as $head)
                                                    <option value="{{ $head->id }}">{{ $head->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="row_account_id[]" class="rowAccountSub">
                                                <option value="">Select Account</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="narration_id[]" class="narrationSelect"
                                                style="margin-bottom:4px;">
                                                <option value="">Select / Add</option>
                                                @foreach ($narrations as $id => $name)
                                                    <option value="{{ $id }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" class="narrationInput" name="narration_text[]"
                                                style="display:none;" placeholder="Custom Narration...">
                                        </td>
                                        <td><input type="number" name="amount[]" class="amount amount-input"
                                                step="0.01" placeholder="0.00"></td>
                                        <td class="text-center">
                                            <button type="button" class="btn-remove-row removeRow"><i
                                                    class="bi bi-x-lg"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="voucher-footer-row">
                                    <tr>
                                        <td colspan="3" style="padding:12px 16px;">
                                            <button type="button" class="btn-add-row" id="addNewRow">
                                                <i class="bi bi-plus-circle"></i> Add Line
                                            </button>
                                        </td>
                                        <td style="padding:12px 10px;" class="text-end">
                                            <div class="total-box">
                                                <div class="total-box-label">Total Expense</div>
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
                <div style="display:flex;justify-content:flex-end;align-items:center;gap:12px;padding:8px 0;">
                    <a href="{{ route('all_expense_vochers') }}" class="btn-cancel">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn-save">
                        <i class="bi bi-save"></i> Save Expense Voucher
                    </button>
                </div>
            </form>

        </div>
    </div>
@endsection

@section('js')
    <script>
        // Paid From Head → Account
        $('#payFromHead').on('change', function() {
            let headId = $(this).val();
            let $accSelect = $('#payFromAccount');
            $accSelect.html('<option disabled selected>Loading...</option>');
            $('.balance-display').hide();
            if (headId) {
                $.get('{{ url('get-accounts-by-head') }}/' + headId, function(data) {
                    $accSelect.empty().append('<option disabled selected>Select Account</option>');
                    data.forEach(function(acc) {
                        $accSelect.append(
                            `<option value="${acc.id}" data-bal="${acc.opening_balance}">${acc.title}</option>`
                            );
                    });
                });
            } else {
                $accSelect.empty().append('<option disabled selected>Select Account</option>');
            }
        });

        $('#payFromAccount').on('change', function() {
            let $opt = $(this).find(':selected');
            let bal = $opt.data('bal');
            if (bal !== undefined) {
                $('.balance-display').show();
                $('#balanceVal').text(parseFloat(bal).toFixed(2));
            }
        });

        // Row Head → Sub-accounts
        $(document).on('change', '.rowAccountHead', function() {
            let headId = $(this).val();
            let $subSelect = $(this).closest('tr').find('.rowAccountSub');
            if (!headId) {
                $subSelect.html('<option value="">Select Account</option>');
                return;
            }
            $.get('{{ url('get-accounts-by-head') }}/' + headId, function(res) {
                let html = '<option value="">Select Account</option>';
                res.forEach(acc => {
                    html += `<option value="${acc.id}">${acc.title}</option>`;
                });
                $subSelect.html(html);
            });
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

        function getNewRow() {
            let headOpts = `<option value="">Select Head</option>`;
            @foreach ($AccountHeads as $head)
                headOpts += `<option value="{{ $head->id }}">{{ $head->name }}</option>`;
            @endforeach
            let narOpts = `<option value="">Select / Add</option>`;
            @foreach ($narrations as $id => $name)
                narOpts += `<option value="{{ $id }}">{{ $name }}</option>`;
            @endforeach
            const cellStyle =
                `style="border:1.5px solid #e2e8f0;border-radius:7px;padding:7px 10px;font-size:0.88rem;width:100%;"`;
            return `
        <tr>
            <td><select name="row_account_head[]" class="rowAccountHead" ${cellStyle}>${headOpts}</select></td>
            <td><select name="row_account_id[]" class="rowAccountSub" ${cellStyle}><option value="">Select Account</option></select></td>
            <td>
                <select name="narration_id[]" class="narrationSelect" ${cellStyle} style="border:1.5px solid #e2e8f0;border-radius:7px;padding:7px 10px;font-size:0.88rem;width:100%;margin-bottom:4px;">${narOpts}</select>
                <input type="text" class="narrationInput" name="narration_text[]" ${cellStyle} style="border:1.5px solid #e2e8f0;border-radius:7px;padding:7px 10px;font-size:0.88rem;width:100%;display:none;" placeholder="Custom Narration...">
            </td>
            <td><input type="number" name="amount[]" class="amount amount-input" ${cellStyle} style="border:1.5px solid #e2e8f0;border-radius:7px;padding:7px 10px;font-size:0.88rem;width:100%;text-align:right;font-weight:600;" step="0.01" placeholder="0.00"></td>
            <td class="text-center"><button type="button" class="btn-remove-row removeRow"><i class="bi bi-x-lg"></i></button></td>
        </tr>`;
        }

        $('#addNewRow').on('click', function() {
            $('#voucherTable tbody').append(getNewRow());
        });

        $(document).on('click', '.removeRow', function() {
            if ($('#voucherTable tbody tr').length > 1) {
                $(this).closest('tr').remove();
                calculateTotal();
            }
        });

        $(document).on('keypress', '.amount', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#addNewRow').click();
            }
        });
    </script>
@endsection
