@extends('admin_panel.layout.app')

@section('content')
    <style>
        /* Professional ERP Styling */
        :root {
            --erp-primary: #4a69bd;
            /* Professional Blue */
            --erp-bg: #f5f6fa;
            --erp-border: #dcdde1;
            --erp-text: #2f3640;
            --erp-muted: #7f8fa6;
        }

        body {
            background-color: var(--erp-bg);
            color: var(--erp-text);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .erp-card {
            background: white;
            border: 1px solid var(--erp-border);
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .erp-header {
            background: white;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--erp-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px 8px 0 0;
        }

        .erp-header h5 {
            margin: 0;
            font-weight: 600;
            color: var(--erp-primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--erp-muted);
            text-transform: uppercase;
            margin-bottom: 0.3rem;
        }

        .form-control,
        .form-select {
            border-radius: 4px;
            border: 1px solid var(--erp-border);
            padding: 0.4rem 0.75rem;
            font-size: 0.9rem;
        }

        .form-control:focus {
            border-color: var(--erp-primary);
            box-shadow: 0 0 0 2px rgba(74, 105, 189, 0.2);
        }

        .form-control[readonly] {
            background-color: #f8f9fa;
            /* Lighter grey */
            color: var(--erp-text);
            font-weight: 500;
        }

        /* Table Styles */
        .erp-table-container {
            border: 1px solid var(--erp-border);
            border-radius: 6px;
            overflow: hidden;
        }

        .table-erp {
            width: 100%;
            margin-bottom: 0;
        }

        .table-erp thead th {
            background-color: #f1f2f6;
            color: var(--erp-text);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            padding: 0.75rem;
            border-bottom: 2px solid var(--erp-border);
            white-space: nowrap;
        }

        .table-erp tbody td {
            vertical-align: middle;
            padding: 0.5rem;
            border-bottom: 1px solid #f1f2f6;
        }

        .table-erp input.form-control {
            border: 1px solid transparent;
            background: transparent;
            padding: 0.25rem 0.5rem;
            height: auto;
        }

        .table-erp input.form-control:focus,
        .table-erp input.form-control:hover {
            border-color: var(--erp-border);
            background: white;
        }

        .summary-card {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 1.5rem;
            border: 1px solid var(--erp-border);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .summary-row.total {
            border-top: 1px solid var(--erp-border);
            padding-top: 0.5rem;
            margin-top: 0.5rem;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--erp-primary);
        }

        .btn-erp-primary {
            background-color: var(--erp-primary);
            color: white;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            border: none;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .btn-erp-primary:hover {
            background-color: #3c5aa6;
            color: white;
            transform: translateY(-1px);
        }

        /* Select2 Tweaks */
        .select2-container .select2-selection--single {
            height: 36px;
            border-color: var(--erp-border);
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .select2-container--default .select2-selection--multiple {
            border-color: var(--erp-border);
        }
    </style>


    <!-- Structure Wrapper -->
    <div class="container-fluid py-4">
        <div class="erp-card">
            <div class="erp-header">
                <h5><i class="fas fa-undo-alt me-2"></i> Sale Return Request</h5>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-light text-dark border"><i class="fas fa-file-invoice me-1"></i> Original Invoice #
                        {{ $sale->invoice_no }}</span>
                    <a href="{{ route('sale.index') }}" class="btn btn-sm btn-outline-secondary">Back to List</a>
                </div>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('sales.return.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                    <input type="hidden" name="branch_id" value="1">
                    <input type="hidden" name="warehouse_id" value="1">

                    <!-- Alert Section -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Top Section: Customer & Reference -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Customer</label>
                            {{-- Read-Only Customer Name --}}
                            <input type="text" class="form-control form-control-sm"
                                value="{{ optional($sale->customer_relation)->customer_name ?? 'Unknown Customer' }}"
                                readonly style="background-color: #e9ecef; border-color: #dee2e6;">
                            {{-- Hidden ID for Form Submission --}}
                            <input type="hidden" name="customer" value="{{ $sale->customer_id }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reference #</label>
                            <input type="text" name="reference" class="form-control form-control-sm"
                                value="{{ $sale->reference }}" readonly>
                        </div>
                        <div class="col-md-5 text-end align-self-end">
                            <div class="p-2 bg-light rounded d-inline-block border">
                                <small class="text-muted d-block text-start" style="font-size: 0.7rem;">ORIGINAL SALE
                                    DATE</small>
                                <strong class="text-dark"><i class="far fa-calendar-alt me-1"></i>
                                    {{ $sale->created_at->format('d M, Y h:i A') }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Return Deadline Warning -->
                    @if (!$isWithinDeadline)
                        <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">Return Period Expired!</h6>
                                    <p class="mb-0">This sale is past the {{ $returnDeadlineDays }}-day return deadline.
                                        Sale Date: <strong>{{ $sale->created_at->format('d-M-Y') }}</strong> |
                                        Deadline: <strong>{{ $returnDeadline->format('d-M-Y') }}</strong></p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info border-0 shadow-sm mb-4" role="alert">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle fa-lg me-3"></i>
                                    <div>
                                        <strong>Return Deadline:</strong> {{ $returnDeadline->format('d M, Y') }}
                                        <span class="badge bg-success ms-2">{{ now()->diffInDays($returnDeadline) }} days
                                            remaining</span>
                                    </div>
                                </div>
                                <div>
                                    <small class="text-muted">Sale Date: {{ $sale->created_at->format('d-M-Y') }}</small>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Quality Status Selection -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Quality Status <span class="text-danger">*</span></label>
                            <select name="quality_status" class="form-select" required>
                                <option value="pending_inspection">Pending Inspection</option>
                                <option value="good">Good Condition</option>
                                <option value="damaged">Damaged</option>
                                <option value="defective">Defective</option>
                            </select>
                            <small class="text-muted">Inspect returned items and select their condition</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Inspection Notes (Optional)</label>
                            <input type="text" name="inspection_notes" class="form-control"
                                placeholder="e.g., Box slightly damaged, product intact">
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive erp-table-container mb-4">
                        <table class="table table-erp table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">Product</th>
                                    <th style="width: 10%;">Item Code</th>
                                    <th style="width: 15%;">PC per box</th>
                                    <th style="width: 10%;">Sold Price Per Pc</th>
                                    <th style="width: 10%;">Return Qty PC</th>
                                    <th style="width: 10%;">Return Qty BOX</th>
                                    <th style="width: 12%;">Total Refund</th>
                                    <th style="width: 5%;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="purchaseItems">
                                @foreach ($saleItems as $index => $item)
                                    <tr>
                                        <input type="hidden" name="product_id[]" value="{{ $item['product_id'] }}">
                                        {{-- Hidden Discount to preserve refund math --}}
                                        <input type="hidden" name="item_disc[]" class="item_disc"
                                            value="{{ $item['discount'] }}">

                                        <td>
                                            <input type="text" name="product[]" class="form-control fw-bold"
                                                value="{{ $item['item_name'] }}" readonly>
                                            <small class="text-muted d-block ms-2"
                                                style="font-size: 0.75rem;">{{ $item['brand'] ?? '' }}</small>
                                        </td>

                                        <td><input type="text" name="item_code[]" class="form-control text-center"
                                                value="{{ $item['item_code'] }}" readonly></td>

                                        {{-- PC per box --}}
                                        <td>
                                            <input type="number" class="form-control text-center pieces-per-box"
                                                value="{{ $item['pieces_per_box'] ?? 1 }}" readonly>
                                        </td>


                                        {{-- Sold Price Per Pc --}}
                                        <td><input type="number" name="price[]" step="0.01"
                                                class="form-control text-end price" value="{{ $item['price'] }}"
                                                readonly>
                                        </td>


                                        {{-- Return Qty PC --}}
                                        <td>
                                            <input type="number" name="qty[]"
                                                class="form-control text-center fw-bold text-primary quantity"
                                                value="{{ $item['max_returnable'] ?? $item['qty'] }}" min="0"
                                                max="{{ $item['max_returnable'] ?? $item['qty'] }}"
                                                data-max="{{ $item['max_returnable'] ?? $item['qty'] }}"
                                                data-original-sold="{{ $item['qty'] }}"
                                                data-already-returned="{{ $item['already_returned'] ?? 0 }}">
                                            @if (isset($item['already_returned']) && $item['already_returned'] > 0)
                                                <small class="text-muted d-block">Already returned:
                                                    {{ $item['already_returned'] }}</small>
                                            @endif
                                        </td>

                                        {{-- Return Qty BOX --}}
                                        <td>
                                            <input type="number" name="qty_box[]"
                                                class="form-control text-center fw-bold text-success quantity-box"
                                                value="{{ $item['pieces_per_box'] > 0 ? round(($item['max_returnable'] ?? $item['qty']) / $item['pieces_per_box'], 2) : 0 }}"
                                                step="0.01">
                                        </td>

                                        <td><input type="text" name="total[]"
                                                class="form-control text-end fw-bold row-total"
                                                value="{{ $item['total'] }}" readonly></td>
                                        <td class="text-center">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger border-0 remove-row rounded-circle"
                                                title="Remove Item"><i class="fas fa-times"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Summary -->
                    <div class="row mt-4">
                        <div class="col-md-7">
                            <div class="p-4 bg-light rounded border h-100">
                                <label class="form-label text-muted small">AMOUNT IN WORDS</label>
                                <input type="text" name="total_amount_Words"
                                    class="form-control border-0 bg-transparent fw-bold text-primary fs-5 fst-italic"
                                    id="amountInWords" readonly placeholder="...">

                                <div class="mt-4 pt-4 border-top">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-cubes text-muted"></i>
                                        <span class="text-muted small">Total Pieces Returned:</span>
                                        <strong id="totalPieces" class="text-dark fs-5">0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Partial Return Status Indicator -->
                        <div class="col-md-12 mb-3">
                            <div class="partial-return-indicator shadow-sm border-0 p-3 rounded"
                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 text-white">
                                        <i class="fas fa-chart-pie me-2"></i>Return Status
                                    </h6>
                                    <span id="returnTypeBadge" class="badge bg-light text-dark">
                                        <i class="fas fa-spinner fa-spin me-1"></i>Calculating...
                                    </span>
                                </div>
                                <div class="progress" style="height: 25px; background: rgba(255,255,255,0.2);">
                                    <div id="returnProgressBar"
                                        class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                        style="width: 0%; background: #10ac84;" aria-valuenow="0" aria-valuemin="0"
                                        aria-valuemax="100">
                                        <strong id="returnPercentage">0%</strong>
                                    </div>
                                </div>
                                <div class="mt-2 text-white small" id="returnStatusText">
                                    <i class="fas fa-info-circle me-1"></i>Select items to return
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="summary-card shadow-sm border-0">
                                <h6 class="mb-3 text-uppercase fw-bold text-muted"
                                    style="font-size: 0.8rem; letter-spacing: 1px;">Refund Summary</h6>
                                <div class="summary-row">
                                    <span class="text-muted">Subtotal</span>
                                    <input type="text" name="total_subtotal" id="billAmount"
                                        class="form-control form-control-sm w-50 text-end border-0 bg-transparent p-0"
                                        readonly value="0.00">
                                </div>
                                <div class="summary-row">
                                    <span class="text-muted">Less: Item Discount</span>
                                    <input type="text" name="total_discount" id="itemDiscount"
                                        class="form-control form-control-sm w-50 text-end border-0 bg-transparent p-0 text-danger"
                                        readonly value="0.00">
                                </div>
                                <div class="summary-row align-items-center mt-2">
                                    <span class="text-dark fw-medium">Less: Adjustment Cost</span>
                                    <input type="number" name="total_extra_cost" id="extraDiscount"
                                        class="form-control form-control-sm w-50 text-end bg-white" value="0">
                                </div>
                                <hr class="my-3">
                                <div class="summary-row total">
                                    <span>NET REFUND AMOUNT</span>
                                    <input type="text" name="total_net" id="netAmount"
                                        class="form-control form-control-lg w-50 text-end border-0 bg-transparent p-0 fw-bold text-primary"
                                        readonly value="0.00">
                                </div>

                                <!-- Hidden Cash/Card fields if strictly return -->
                                <input type="hidden" name="cash" id="cash" value="0">
                                <input type="hidden" name="card" id="card" value="0">
                                <input type="hidden" name="change" id="change" value="0">

                                <!-- Payment Voucher Section -->
                                <div class="mt-4 pt-4 border-top">
                                    <h6 class="mb-3 text-uppercase fw-bold text-muted"
                                        style="font-size: 0.8rem; letter-spacing: 1px;">
                                        <i class="fas fa-money-bill-wave me-2"></i>Refund Payment Details
                                    </h6>

                                    <div class="payment-voucher-rows">
                                        <div class="payment-row mb-2">
                                            <div class="row g-2">
                                                <div class="col-7">
                                                    <select name="payment_account_id[]"
                                                        class="form-select form-select-sm payment-account" required>
                                                        <option value="">Select Account</option>
                                                        @foreach ($accounts as $acc)
                                                            <option value="{{ $acc->id }}">{{ $acc->title }}
                                                                ({{ $acc->account_code }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-5">
                                                    <input type="number" name="payment_amount[]" step="0.01"
                                                        class="form-control form-control-sm text-end payment-amount"
                                                        placeholder="Amount" required readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2"
                                        id="addPaymentRow">
                                        <i class="fas fa-plus"></i> Add Another Account
                                    </button>
                                </div>

                                <!-- Return Note -->
                                <div class="mt-4 pt-3 border-top">
                                    <label class="form-label text-muted small">RETURN REASON / NOTES</label>
                                    <textarea name="return_note" class="form-control" rows="2" placeholder="Enter reason for return (optional)"></textarea>
                                </div>

                                <div class="mt-4 d-grid gap-2">
                                    <button type="submit" class="btn btn-erp-primary btn-lg shadow-sm">
                                        <i class="fas fa-check-circle me-2"></i> Process Sale Return
                                    </button>

                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {

        // Initialize Select2
        // Initialize Select2
        // $('.js-example-basic-single').select2(); // Removed as Customer is now Read-Only Input
        $('.select2-color').select2({
            placeholder: "Select Color",
            tags: true,
            width: '100%'
        });

        function num(n) {
            return isNaN(parseFloat(n)) ? 0 : parseFloat(n);
        }

        function numberToWords(num) {
            const a = ["", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten",
                "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen",
                "Eighteen", "Nineteen"
            ];
            const b = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];
            if ((num = num.toString()).length > 9) return "Overflow";
            const n = ("000000000" + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{3})$/);
            if (!n) return;
            let str = "";
            str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + " " + a[n[1][1]]) + " Crore " : "";
            str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + " " + a[n[2][1]]) + " Lakh " : "";
            str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + " " + a[n[3][1]]) + " Thousand " : "";
            str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + " " + a[n[4][1]]) + " " : "";
            return str.trim() + " Rupees Only";
        }

        function recalcRow($row) {
            const qty = num($row.find('.quantity').val()); // Pieces
            const price = num($row.find('.price').val()); // Price Per Piece
            const discPercent = num($row.find('.item_disc').val()); // Percentage

            // Gross Total = Pieces * Price Per Piece
            const gross = qty * price;

            // Discount Calculation (Amount derived from Percentage)
            const discAmount = (gross * discPercent) / 100;

            // Net Row Total
            let total = gross - discAmount;

            if (total < 0) total = 0;
            $row.find('.row-total').val(total.toFixed(2));

            // Store calculated discount amount for summary
            $row.data('disc-amount', discAmount);
        }

        function recalcSummary() {
            let billAmount = 0;
            let itemDiscount = 0;
            let totalQty = 0;

            $('#purchaseItems tr').each(function() {
                const qty = num($(this).find('.quantity').val());
                const price = num($(this).find('.price').val());

                // Recalculate bill amount based on qty * price (Gross)
                billAmount += (qty * price);

                // Get pre-calculated Discount Amount
                const discAmount = $(this).data('disc-amount') || 0;
                itemDiscount += discAmount;

                totalQty += qty;
            });

            const extraDiscount = num($('#extraDiscount').val()); // Adjustment

            const net = billAmount - itemDiscount - extraDiscount;

            $('#billAmount').val(billAmount.toFixed(2));
            $('#itemDiscount').val(itemDiscount.toFixed(2));
            $('#netAmount').val(net.toFixed(2));

            if (net > 0) {
                $('#amountInWords').val(numberToWords(Math.round(net)));
            } else {
                $('#amountInWords').val('Zero Rupees');
            }

            $('#totalPieces').text(totalQty);

            // Update visual indicators
            if (typeof updatePartialReturnIndicator === 'function') {
                updatePartialReturnIndicator();
            }
        }

        // Events
        $(document).on('input', '.quantity, .price, .item_disc, #extraDiscount', function() {
            const $row = $(this).closest('tr');

            // Validate max returnable quantity
            if ($(this).hasClass('quantity')) {
                const qtyPc = num($(this).val());
                const maxReturnable = num($(this).attr('data-max'));

                if (qtyPc > maxReturnable) {
                    $(this).val(maxReturnable);
                    $(this).addClass('border-danger');

                    // Show warning
                    if (!$(this).next('.text-danger').length) {
                        $(this).after('<small class="text-danger d-block">Max: ' + maxReturnable +
                            ' pieces</small>');
                    }

                    setTimeout(() => {
                        $(this).removeClass('border-danger');
                        $(this).next('.text-danger').fadeOut(300, function() {
                            $(this).remove();
                        });
                    }, 2000);
                }

                // Auto Update Box Count from Pieces
                const ppb = num($row.find('.pieces-per-box').val());
                if (ppb > 0) {
                    $row.find('.quantity-box').val((qtyPc / ppb).toFixed(2));
                }
            }

            if ($row.length) {
                recalcRow($row);
            }
            recalcSummary();
        });

        // New listener for Box input
        $(document).on('input', '.quantity-box', function() {
            const $row = $(this).closest('tr');
            const qtyBox = num($(this).val());
            const ppb = num($row.find('.pieces-per-box').val());

            if (ppb > 0) {
                // Calculate pieces (rounding to integer or keeping decimal if needed)
                // Assuming pieces are whole numbers generally
                const qtyPc = Math.round(qtyBox * ppb);
                $row.find('.quantity').val(qtyPc);
            }

            if ($row.length) {
                recalcRow($row);
            }
            recalcSummary();
        });

        // Initialize
        $('#purchaseItems tr').each(function() {
            recalcRow($(this));
        });
        recalcSummary();

        // Remove row
        $(document).on('click', '.remove-row', function() {
            if (confirm('Are you sure you want to remove this item from return?')) {
                $(this).closest('tr').remove();
                recalcSummary();
            }
        });

        // Payment Voucher Handling
        function updatePaymentAmounts() {
            const netAmount = num($('#netAmount').val());
            const paymentRows = $('.payment-amount');

            if (paymentRows.length === 1) {
                // Single payment row - fill with full amount
                paymentRows.first().val(netAmount.toFixed(2));
            } else {
                // Multiple rows - distribute evenly or keep manual
                // For now, just update if they're empty
                paymentRows.each(function() {
                    if (!$(this).val() || $(this).val() == '0.00') {
                        $(this).val((netAmount / paymentRows.length).toFixed(2));
                    }
                });
            }

            // Update partial return indicator
            updatePartialReturnIndicator();
        }

        // Update Partial Return Visual Indicator
        function updatePartialReturnIndicator() {
            let totalOriginalPieces = 0;
            let totalReturningPieces = 0;
            let totalAlreadyReturned = 0;

            $('#purchaseItems tr').each(function() {
                const $qtyInput = $(this).find('.quantity');
                const soldQty = num($qtyInput.attr('data-original-sold'));
                const alreadyReturned = num($qtyInput.attr('data-already-returned'));
                const returningQty = num($qtyInput.val());

                totalOriginalPieces += soldQty;
                totalAlreadyReturned += alreadyReturned;
                totalReturningPieces += returningQty;
            });

            const totalAfterReturn = totalAlreadyReturned + totalReturningPieces;
            const returnPercentage = totalOriginalPieces > 0 ? (totalAfterReturn / totalOriginalPieces * 100) :
                0;

            // Update progress bar
            $('#returnProgressBar').css('width', returnPercentage + '%');
            $('#returnProgressBar').attr('aria-valuenow', returnPercentage);
            $('#returnPercentage').text(returnPercentage.toFixed(1) + '%');

            // Update badge and status text
            if (totalReturningPieces === 0) {
                $('#returnTypeBadge').html('<i class="fas fa-info-circle me-1"></i>No Items Selected');
                $('#returnTypeBadge').removeClass().addClass('badge bg-secondary');
                $('#returnStatusText').html('<i class="fas fa-info-circle me-1"></i>Select items to return');
                $('#returnProgressBar').css('background', '#6c757d');
            } else if (returnPercentage >= 100) {
                $('#returnTypeBadge').html('<i class="fas fa-check-circle me-1"></i>Full Return');
                $('#returnTypeBadge').removeClass().addClass('badge bg-success');
                $('#returnStatusText').html('<i class="fas fa-check-circle me-1"></i>Returning all ' +
                    totalOriginalPieces + ' pieces (100% of sale)');
                $('#returnProgressBar').css('background', '#10ac84');
            } else {
                $('#returnTypeBadge').html('<i class="fas fa-chart-pie me-1"></i>Partial Return');
                $('#returnTypeBadge').removeClass().addClass('badge bg-warning text-dark');
                $('#returnStatusText').html('<i class="fas fa-chart-pie me-1"></i>Returning ' +
                    totalReturningPieces + ' of ' + totalOriginalPieces + ' pieces (' + returnPercentage
                    .toFixed(1) + '%)');
                $('#returnProgressBar').css('background', '#f79f1f');
            }
        }


        // Add payment row
        $('#addPaymentRow').on('click', function() {
            const newRow = `
                <div class="payment-row mb-2">
                    <div class="row g-2">
                        <div class="col-7">
                            <select name="payment_account_id[]" class="form-select form-select-sm payment-account" required>
                                <option value="">Select Account</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->title }} ({{ $acc->account_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-5">
                            <div class="input-group input-group-sm">
                                <input type="number" name="payment_amount[]" step="0.01" 
                                    class="form-control text-end payment-amount" 
                                    placeholder="Amount" required>
                                <button type="button" class="btn btn-outline-danger remove-payment-row">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('.payment-voucher-rows').append(newRow);
            updatePaymentAmounts();
        });

        // Remove payment row
        $(document).on('click', '.remove-payment-row', function() {
            $(this).closest('.payment-row').remove();
            updatePaymentAmounts();
        });

        // Update payment amounts when net amount changes
        $(document).on('input', '#extraDiscount', function() {
            setTimeout(updatePaymentAmounts, 100);
        });

        // Initial payment amount setup
        updatePaymentAmounts();
    });
</script>
