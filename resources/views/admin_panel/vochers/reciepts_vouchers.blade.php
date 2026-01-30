@extends('admin_panel.layout.app')
@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <div class="main-content">
        <div class="container-fluid">
            <h2 class="fw-bold mb-4">Receipts Voucher</h2>

            <div class="card shadow-sm">
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('store_rec_vochers') }}" method="POST">
                        @csrf

                        {{-- Top Header Inputs (Source Party) --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">RVID</label>
                                <input type="text" class="form-control bg-light" name="rvid"
                                    value="{{ $nextRvid }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Receipt Date</label>
                                <input type="date" name="receipt_date" class="form-control"
                                    value="{{ now()->toDateString() }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Entry Date</label>
                                <input type="date" name="entry_date" class="form-control"
                                    value="{{ now()->toDateString() }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Remarks</label>
                                <input type="text" name="remarks" class="form-control" id="remarks">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Type</label>
                                <select name="vendor_type" class="form-select" id="partyType">
                                    <option value="">Select</option>
                                    <option value="customer">Customer</option>
                                    <option value="walkin">Walkin</option>
                                    <option value="vendor">Vendor</option>
                                    @foreach ($AccountHeads as $head)
                                        <option value="{{ $head->id }}">{{ $head->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Party</label>
                                <select name="vendor_id" class="form-select" id="partyId">
                                    <option disabled selected>Select</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tel / Account Code</label>
                                <input type="text" name="tel" id="tel" class="form-control bg-light" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Opening Balance</label>
                                <input type="text" id="openingBal" class="form-control bg-light" readonly>
                            </div>
                        </div>

                        {{-- Table (Destination Accounts) --}}
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center" id="voucherTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Narration</th>
                                        <th>Reference#</th>
                                        <th>Account Head</th>
                                        <th>Account</th>
                                        <th>Discount</th>
                                        <th>Rate</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="input-group">
                                                <select name="narration_id[]" class="form-select narrationSelect"
                                                    style="min-width: 120px;">
                                                    <option value="">Select / Add</option>
                                                    @foreach ($narrations as $id => $name)
                                                        <option value="{{ $id }}">{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" class="form-control narrationInput"
                                                    name="narration_text[]" placeholder="Text...">
                                            </div>
                                        </td>
                                        <td><input type="text" name="reference_no[]" class="form-control"></td>
                                        <td>
                                            <select name="row_account_head[]" class="form-select rowAccountHead"
                                                style="min-width: 100px;">
                                                <option value="">Select</option>
                                                @foreach ($AccountHeads as $head)
                                                    <option value="{{ $head->id }}">{{ $head->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="row_account_id[]" class="form-select rowAccountSub"
                                                style="min-width: 150px;">
                                                <option disabled selected>Select Account</option>
                                            </select>
                                        </td>
                                        <td><input type="number" name="discount_value[]" class="form-control"
                                                value="0"></td>
                                        <td><input type="number" name="rate[]" class="form-control rate" value="0">
                                        </td>
                                        <td><input type="number" name="amount[]"
                                                class="form-control text-end fw-bold amount" placeholder="0.00"></td>
                                        <td><button type="button" class="btn btn-danger btn-sm removeRow"><i
                                                    class="bi bi-trash"></i></button></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Total:</td>
                                        <td>
                                            <input type="text" name="total_amount"
                                                class="form-control text-end fw-bold bg-light" id="totalAmount" readonly
                                                value="0.00">
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <button type="button" class="btn btn-success btn-sm mt-2" id="addNewRow">Add Row</button>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary px-4">Save</button>
                            <a href="{{ route('all_recepit_vochers') }}" class="btn btn-outline-secondary px-4">Exit</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        // Header Party Logic
        $('#partyType').on('change', function() {
            let type = $(this).val();
            let $select = $('#partyId');
            $select.html('<option disabled selected>Loading...</option>');
            $('#tel').val('');
            $('#openingBal').val('');

            if (type === 'vendor' || type === 'customer' || type === 'walkin') {
                $.get('{{ route('party.list') }}?type=' + type, function(data) {
                    $select.empty().append('<option disabled selected>Select</option>');
                    data.forEach(function(item) {
                        $select.append(
                            `<option value="${item.id}" data-phone="${item.mobile || ''}" data-bal="${item.closing_balance}">${item.text}</option>`
                        );
                    });
                });
            } else if (type) {
                $.get('{{ url('get-accounts-by-head') }}/' + type, function(data) {
                    $select.empty().append('<option disabled selected>Select</option>');
                    data.forEach(function(acc) {
                        $select.append(
                            `<option value="${acc.id}" data-phone="${acc.account_code}" data-bal="${acc.opening_balance}">${acc.title}</option>`
                        );
                    });
                });
            }
        });

        $('#partyId').on('change', function() {
            let $opt = $(this).find(':selected');
            $('#tel').val($opt.data('phone'));
            $('#openingBal').val($opt.data('bal'));

            // Also fetch detailed remarks/info if needed like before
            let id = $(this).val();
            let type = $('#partyType').val();
            $.get('{{ route('salecustomers.show', ['id' => '__ID__']) }}'.replace('__ID__', id) + '?type=' + type,
                function(d) {
                    if (d.remarks && !$('#remarks').val()) $('#remarks').val(d.remarks);
                });
        });

        // Row Logic (Destination Accounts)
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

        // Narration
        $(document).on('change', '.narrationSelect', function() {
            let $row = $(this).closest('td');
            let $input = $row.find('.narrationInput');
            if ($(this).val() === '') {
                $input.show().focus();
            } else {
                $input.hide().val('');
            }
        });

        // Totals
        function calculateTotal() {
            let total = 0;
            $('.amount').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#totalAmount').val(total.toFixed(2));
        }
        $(document).on('input', '.amount', function() {
            calculateTotal();
        });

        // Add Row
        $('#addNewRow').on('click', function() {
            let newRow = `
            <tr>
                <td>
                    <div class="input-group">
                        <select name="narration_id[]" class="form-select narrationSelect" style="min-width: 120px;">
                            <option value="">Select / Add</option>
                            @foreach ($narrations as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <input type="text" class="form-control narrationInput" name="narration_text[]" placeholder="Text...">
                    </div>
                </td>
                <td><input type="text" name="reference_no[]" class="form-control"></td>
                <td>
                    <select name="row_account_head[]" class="form-select rowAccountHead" style="min-width: 100px;">
                        <option value="">Select</option>
                        @foreach ($AccountHeads as $head)
                            <option value="{{ $head->id }}">{{ $head->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="row_account_id[]" class="form-select rowAccountSub" style="min-width: 150px;">
                        <option disabled selected>Select Account</option>
                    </select>
                </td>
                <td><input type="number" name="discount_value[]" class="form-control" value="0"></td>
                <td><input type="number" name="rate[]" class="form-control rate" value="0"></td>
                <td><input type="number" name="amount[]" class="form-control text-end fw-bold amount" placeholder="0.00"></td>
                <td><button type="button" class="btn btn-danger btn-sm removeRow"><i class="bi bi-trash"></i></button></td>
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
    </script>
@endsection
