@extends('admin_panel.layout.app')
@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <div class="main-content">
        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-primary">Expense Voucher</h2>
                <a href="{{ route('all_expense_vochers') }}" class="btn btn-outline-primary">
                    <i class="bi bi-list"></i> View All Expenses
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 text-white"><i class="bi bi-wallet2 me-2"></i>New Expense Entry</h5>
                </div>
                <div class="card-body p-4">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('store_expense_vochers') }}" method="POST">
                        @csrf

                        {{-- Header Section: Voucher Info & Source of Funds --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-2">
                                <label class="form-label fw-bold text-muted small text-uppercase">Voucher No</label>
                                <input type="text" class="form-control bg-light" name="evid"
                                    value="{{ $nextRvid }}" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold text-muted small text-uppercase">Date</label>
                                <input type="date" name="entry_date" class="form-control"
                                    value="{{ now()->toDateString() }}">
                            </div>

                            {{-- Paid From (Source) --}}
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-muted small text-uppercase">Paid From (Source)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-safe"></i></span>
                                    <select name="vendor_type" class="form-select" id="payFromHead">
                                        <option value="">Select Head</option>
                                        @foreach ($AccountHeads as $head)
                                            <option value="{{ $head->id }}">{{ $head->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-muted small text-uppercase">Account</label>
                                <select name="vendor_id" class="form-select section-account" id="payFromAccount">
                                    <option disabled selected>Select Account</option>
                                </select>
                                <div class="form-text text-end balance-display" style="display:none;">
                                    Balance: <span class="fw-bold text-dark">0.00</span>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-bold text-muted small text-uppercase">Reference / Cheque
                                    #</label>
                                <input type="text" name="ref_no_header" class="form-control" placeholder="Optional">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Global Remarks</label>
                            <input type="text" name="remarks" class="form-control"
                                placeholder="Any general notes for this voucher...">
                        </div>

                        {{-- Body Section: Expense Allocations --}}
                        <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-list-check me-2"></i>Expense Details</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle" id="voucherTable">
                                <thead class="table-light text-secondary small text-uppercase">
                                    <tr>
                                        <th style="width: 25%">Expense Head / Account</th>
                                        <th style="width: 25%">Narration</th>
                                        <th style="width: 15%;">Calculations</th>
                                        <th style="width: 15%">Amount</th>
                                        <th style="width: 5%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="row g-1">
                                                <div class="col-12 mb-1">
                                                    <select name="row_account_head[]"
                                                        class="form-select form-select-sm rowAccountHead">
                                                        <option value="">Select Head</option>
                                                        @foreach ($AccountHeads as $head)
                                                            <option value="{{ $head->id }}">{{ $head->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <select name="row_account_id[]"
                                                        class="form-select form-select-sm rowAccountSub">
                                                        <option value="">Select Expense Account</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <select name="narration_id[]" class="form-select narrationSelect">
                                                    <option value="">Select / Type</option>
                                                    @foreach ($narrations as $id => $name)
                                                        <option value="{{ $id }}">{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <input type="text" class="form-control form-control-sm mt-1 narrationInput"
                                                name="narration_text[]" placeholder="Custom Narration..."
                                                style="display:none;">
                                        </td>
                                        <td>
                                            {{-- Optional Rate/Qty if needed, or just keep it simple --}}
                                            <div class="input-group input-group-sm mb-1">
                                                <span class="input-group-text">Qty</span>
                                                <input type="number" name="kg[]" class="form-control kg"
                                                    placeholder="1">
                                            </div>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Rate</span>
                                                <input type="number" name="rate[]" class="form-control rate"
                                                    placeholder="0">
                                            </div>
                                        </td>
                                        <td>
                                            <input name="amount[]" type="number" step="0.01"
                                                class="form-control text-end fw-bold amount" placeholder="0.00">
                                            <input type="hidden" class="baseAmount" value="0">
                                            <input type="hidden" name="discount_value[]" class="discountValue"
                                                value="0">
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                class="btn btn-outline-danger btn-sm removeRow rounded-circle">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold text-uppercase">Total Amount</td>
                                        <td>
                                            <input type="text" name="total_amount"
                                                class="form-control text-end fw-bold border-0 bg-transparent"
                                                id="totalAmount" readonly value="0.00" style="font-size: 1.1rem;">
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <button type="button" class="btn btn-sm btn-outline-success mt-2" id="addNewRow">
                                <i class="bi bi-plus-lg me-1"></i> Add Line Item
                            </button>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('all_expense_vochers') }}" class="btn btn-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-5 fw-bold"><i
                                    class="bi bi-save me-2"></i>Save Voucher</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        // --- Layout Logic ---
        $(document).on('change', '.narrationSelect', function() {
            let $row = $(this).closest('td');
            let $input = $row.find('.narrationInput');
            if ($(this).val() === '') {
                $input.show().focus().attr('name', 'narration_text[]');
            } else {
                $input.hide().val('').attr('name',
                'narration_text_dummy'); // prevent submission of empty specific text if select used
            }
        });

        // --- Header Source Account Logic ---
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
                            `<option value="${acc.id}" data-code="${acc.account_code}" data-bal="${acc.opening_balance}">${acc.title}</option>`
                        );
                    });
                });
            } else {
                $accSelect.empty().append('<option disabled selected>Select Account</option>');
            }
        });

        // Show balance for header account
        $('#payFromAccount').on('change', function() {
            let $opt = $(this).find(':selected');
            let bal = $opt.data('bal');
            if (bal !== undefined) {
                $('.balance-display').show().find('span').text(parseFloat(bal).toFixed(2));
            }
        });


        // --- Row Expense Account Logic ---
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

        // --- Calculations ---
        function calculateRow(row, manual = false) {
            let kg = parseFloat(row.find('.kg').val()) || 0;
            let rate = parseFloat(row.find('.rate').val()) || 0;
            let amountInput = row.find('.amount');
            let baseAmount = 0;

            if (kg > 0 && rate > 0) {
                baseAmount = kg * rate;
                amountInput.val(baseAmount.toFixed(2));
            } else if (manual) {
                // If typing manually in amount, don't override unless kg*rate changes
            }
        }

        function calculateTotal() {
            let total = 0;
            $('.amount').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#totalAmount').val(total.toFixed(2));
        }

        $(document).on('input', '.kg, .rate', function() {
            let row = $(this).closest('tr');
            calculateRow(row, false);
            calculateTotal();
        });

        $(document).on('input', '.amount', function() {
            calculateTotal();
        });

        // --- Dynamic Rows ---
        $('#addNewRow').on('click', function() {
            let newRow = `
            <tr>
                <td>
                    <div class="row g-1">
                        <div class="col-12 mb-1">
                            <select name="row_account_head[]" class="form-select form-select-sm rowAccountHead">
                                <option value="">Select Head</option>
                                @foreach ($AccountHeads as $head)
                                    <option value="{{ $head->id }}">{{ $head->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <select name="row_account_id[]" class="form-select form-select-sm rowAccountSub">
                                <option value="">Select Expense Account</option>
                            </select>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                            <select name="narration_id[]" class="form-select narrationSelect">
                            <option value="">Select / Type</option>
                            @foreach ($narrations as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="text" class="form-control form-control-sm mt-1 narrationInput" name="narration_text[]" placeholder="Custom Narration..." style="display:none;">
                </td>
                <td>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text">Qty</span>
                        <input type="number" name="kg[]" class="form-control kg" placeholder="1">
                    </div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rate</span>
                        <input type="number" name="rate[]" class="form-control rate" placeholder="0">
                    </div>
                </td>
                <td>
                    <input name="amount[]" type="number" step="0.01" class="form-control text-end fw-bold amount" placeholder="0.00">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm removeRow rounded-circle">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </td>
            </tr>
        `;
            $('#voucherTable tbody').append(newRow);
        });

        $(document).on('click', '.removeRow', function() {
            if ($('#voucherTable tbody tr').length > 1) {
                $(this).closest('tr').remove();
                calculateTotal();
            }
        });

        // Enter key new row
        $(document).on('keypress', '.amount', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#addNewRow').click();
            }
        });
    </script>
@endsection
