@extends('admin_panel.layout.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* ================= RESPONSIVE PURCHASE UI (Copied from Sales UI) ================= */

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .sales-table {
            min-width: 1000px;
            /* Base width */
        }

        /* Premium Table Look */
        .table-bordered>:not(caption)>*>* {
            border-width: 1px;
            border-color: #e9ecef;
            vertical-align: middle;
        }

        /* Column widths to match Sale UI */
        .col-product {
            width: 330px;
            min-width: 250px;
        }

        .col-warehouse {
            width: 140px;
        }

        .col-stock {
            width: 90px;
        }

        .col-qty {
            width: 90px;
        }

        /* Removed Loose Column */

        .col-pieces {
            width: 90px;
        }

        .col-price {
            width: 110px;
        }

        .col-disc {
            width: 85px;
        }

        .col-disc-amt {
            width: 95px;
        }

        .col-price-p {
            width: 100px;
        }

        .col-amount {
            width: 120px;
            text-align: right;
        }

        .col-action {
            width: 50px;
            text-align: center;
        }

        .input-readonly {
            background: #f8f9fa;
            color: #6c757d;
            font-weight: 500;
            border-color: #dee2e6;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .main-container {
            font-size: .85rem;
            max-width: 98%;
        }

        .form-control,
        .form-select,
        .btn {
            font-size: .82rem;
            padding: .3rem .4rem;
        }

        .section-title {
            font-weight: 700;
            color: #6c757d;
            letter-spacing: .3px;
        }

        /* Product Search Dropdown */
        .search-results {
            position: absolute;
            background: white;
            border: 1px solid #ddd;
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
            width: 100%;
            list-style: none;
            padding: 0;
            margin: 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .search-result-item {
            padding: 8px 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }

        .search-result-item:hover,
        .search-result-item.active {
            background-color: #f0f7ff;
            color: #0d6efd;
        }
    </style>

    <div class="container-fluid py-2">
        <div class="main-container bg-white border shadow-sm mx-auto p-2 rounded-3">

            <div id="alertBox" class="alert d-none mb-3" role="alert"></div>

            <form id="purchaseForm" action="{{ route('store.Purchase') }}" method="POST" autocomplete="off">
                @csrf
                <input type="hidden" id="action" name="action" value="purchase">

                {{-- HEADER --}}
                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                    <div>
                        <a href="{{ route('Purchase.home') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                    </div>

                    <h2 class="header-text text-secondary fw-bold mb-0">Purchase Entry</h2>

                    <div class="d-flex align-items-center gap-2">
                        <small class="text-secondary" id="entryDate">Date: {{ date('d-M-Y') }}</small>
                    </div>
                </div>

                <div class="d-flex gap-3 align-items-start border-bottom py-3">
                    {{-- LEFT: Invoice & Vendor --}}
                    <div class="p-3 border rounded-3" style="min-width: 350px;">
                        <div class="section-title mb-3">Invoice & Vendor</div>

                        <div class="mb-2 d-flex align-items-center gap-2">
                            <label class="form-label fw-bold mb-0">System No.</label>
                            <input type="text" class="form-control input-readonly" name="invoice_no" style="width:150px"
                                value="{{ $nextInvoice ?? 'NEW' }}" readonly>
                        </div>

                        <div class="mb-2 d-flex align-items-center gap-2">
                            <label class="form-label fw-bold mb-0">Vendor Inv#</label>
                            <input type="text" class="form-control" name="purchase_order_no"
                                placeholder="Manual Invoice / Ref">
                        </div>

                        <!-- VENDOR SELECT -->
                        <div class="mb-2">
                            <label class="form-label fw-bold mb-1">Select Vendor</label>
                            <select class="form-select select2" id="vendorSelect" name="vendor_id">
                                <option value="" selected disabled>Select Vendor</option>
                                @foreach ($Vendor as $v)
                                    <option value="{{ $v->id }}" data-phone="{{ $v->phone }}"
                                        data-address="{{ $v->address }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold mb-1">Date</label>
                            <input type="date" name="purchase_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold">Remarks</label>
                            <textarea class="form-control" name="note" id="remarks" rows="2"></textarea>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold">Default Warehouse</label>
                            <select name="warehouse_id" class="form-control">
                                @foreach ($Warehouse as $w)
                                    <option value="{{ $w->id }}">{{ $w->warehouse_name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    {{-- RIGHT: Items --}}
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="section-title mb-0">Purchase Items</div>
                            <button type="button" class="btn btn-sm btn-primary" id="btnAdd">Add Row</button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered sales-table mb-0" id="purchaseTable">
                                <thead>
                                    <tr>
                                        <th class="col-product">Product</th>
                                        <th class="col-qty">Total Boxes</th> <!-- Was Total Pcs -->
                                        <th class="col-stock">Pack Size</th>
                                        <!-- Loose Column Removed -->
                                        <th class="col-pieces">Pieces</th> <!-- Was Boxes -->
                                        <th class="col-price">Cost Price</th>
                                        <th class="col-disc">Disc %</th>
                                        <th class="col-disc-amt">Disc Amt</th>
                                        <th class="col-price-p">Cost/Pc</th>
                                        <th class="col-amount">Amount</th>
                                        <th class="col-action">x</th>
                                    </tr>
                                </thead>
                                <tbody id="purchaseTableBody">
                                    <!-- Rows added via JS -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8" class="text-end fw-bold">Total:</td>
                                        <td class="text-end fw-bold"><span id="totalAmount">0.00</span></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Totals + Summary --}}
                <div class="row g-3 mt-3">
                    <div class="col-lg-7">
                        <div class="section-title mb-2">Payment / Receipt Voucher</div>
                        <div id="paymentWrapper" class="border rounded p-2 bg-white">
                            <div class="d-flex gap-2 align-items-center mb-2 payment-row">
                                <select class="form-select rv-account" name="payment_account_id[]"
                                    style="max-width: 300px">
                                    <option value="" selected disabled>Select Account</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->title }}</option>
                                    @endforeach
                                </select>
                                <input type="number" class="form-control text-end payment-amount"
                                    name="payment_amount[]" placeholder="Amount" style="max-width:150px">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddPayment">Add
                                    More</button>
                            </div>
                            <!-- Additional rows will be appended here -->
                        </div>
                        <div class="text-end mt-2">
                            <span class="me-2 fw-bold">Total Paid:</span>
                            <span class="fw-bold" id="totalPaid">0.00</span>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="section-title mb-2">Summary</div>
                        <div class="border rounded p-3 bg-light">
                            <div class="row py-1">
                                <div class="col-7 text-muted">Total Qty (Pieces)</div>
                                <div class="col-5 text-end"><span id="tQty">0</span></div>
                            </div>
                            <div class="row py-1">
                                <div class="col-7 fw-semibold">Sub-Total</div>
                                <div class="col-5 text-end fw-semibold"><span id="tSub">0.00</span></div>
                            </div>
                            <div class="row py-1">
                                <div class="col-7">Bill Discount</div>
                                <div class="col-5 text-end">
                                    <input type="number" class="form-control text-end" name="discount"
                                        id="billDiscount" value="0">
                                </div>
                            </div>
                            <div class="row py-1">
                                <div class="col-7">Extra Cost</div>
                                <div class="col-5 text-end">
                                    <input type="number" class="form-control text-end" name="extra_cost" id="extraCost"
                                        value="0">
                                </div>
                            </div>
                            <div class="row py-2 border-top">
                                <div class="col-7 fw-bold text-primary">Net Payable</div>
                                <div class="col-5 text-end fw-bold text-primary"><span id="tPayable">0.00</span></div>
                                <input type="hidden" name="net_amount" id="netAmountInput" value="0">
                                <input type="hidden" name="subtotal" id="subtotalInput" value="0">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="d-flex flex-wrap gap-2 justify-content-center p-3 mt-3 border-top">
                    <button type="button" class="btn btn-warning" onclick="window.location.reload()">Reset</button>
                    <button type="submit" class="btn btn-success px-4">Save Purchase</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Init Select2
            $('.select2').select2({
                width: '100%'
            });

            // Add First Row
            addBlankRow();

            // Add Row Button
            $('#btnAdd').click(function() {
                addBlankRow();
            });

            // Remove Row
            $(document).on('click', '.remove-row', function() {
                if ($('#purchaseTableBody tr').length > 1) {
                    $(this).closest('tr').remove();
                    recalcAll();
                }
            });

            // Inputs -> Calc
            $('#purchaseTableBody').on('input', '.box-qty, .price, .item-disc-percent, .item-disc-amt', function() {
                if ($(this).hasClass('box-qty')) {
                    normalizeQtyInput($(this), $(this).closest('tr'));
                }
                recalcRow($(this).closest('tr'));
                recalcAll();
            });

            // Summary Inputs
            $('#billDiscount, #extraCost').on('input', function() {
                recalcAll();
            });

            function normalizeQtyInput($input, $row) {
                const val = $input.val();
                const ppb = parseFloat($row.find('.pack-size').val()) || 1;
                // Since this is Purchase, we assume box input logic applies whenever PPB > 1
                // logic similar to shared_logic.blade.php

                if (ppb > 1 && val.includes('.')) {
                    const parts = val.split('.');
                    const boxes = parseInt(parts[0]) || 0;
                    const looseStr = parts[1];

                    if (looseStr && looseStr !== '') {
                        const loose = parseInt(looseStr);
                        // If loose pieces >= pack size, convert to boxes
                        if (loose >= ppb) {
                            const extraBoxes = Math.floor(loose / ppb);
                            const newLoose = loose % ppb;
                            const newBoxes = boxes + extraBoxes;

                            let newVal = newBoxes.toString();
                            if (newLoose > 0) {
                                newVal += '.' + newLoose;
                            }
                            // Update input value
                            $input.val(newVal);
                        }
                    }
                }
            }

            function addBlankRow() {
                const rowCount = $('#purchaseTableBody tr').length;
                const html = `
                <tr>
                    <td style="min-width: 250px;">
                        <select class="form-select product-select2" name="product_id[]"></select>
                    </td>
                    <td><input type="text" class="form-control box-qty" value="0" placeholder="Boxes"></td>
                    <td><input type="number" class="form-control input-readonly pack-size" value="1" readonly></td>
                    <!-- Loose Column Removed -->
                    <td><input type="number" name="qty[]" class="form-control input-readonly qty-pcs" value="0" readonly></td>
                    <td><input type="number" name="price[]" class="form-control price" value="0"></td>
                    <td><input type="number" name="item_discount[]" class="form-control item-disc-percent" value="0"></td>
                    <td><input type="number" class="form-control item-disc-amt" value="0" readonly></td>
                    <td><input type="number" class="form-control input-readonly cost-pc" value="0" readonly></td>
                    <td><input type="number" class="form-control input-readonly row-total" value="0" readonly></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-row">x</button></td>
                </tr>
            `;
                const $row = $(html);
                $('#purchaseTableBody').append($row);
                initProductSelect2($row.find('.product-select2'));
            }

            function initProductSelect2($el) {
                $el.select2({
                    placeholder: 'Search Product (Name / SKU / Barcode)',
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        url: '{{ route('products.ajax.search') }}',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                term: params.term,
                                page: params.page || 1
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.results,
                                pagination: {
                                    more: data.pagination.more
                                }
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 0,
                    templateResult: formatProduct,
                    templateSelection: formatSelection
                });

                $el.on('select2:select', function(e) {
                    const data = e.params.data;
                    const $row = $(this).closest('tr');

                    $row.find('.price').val(data.trade_price || 0);
                    $row.find('.pack-size').val(data.ppb || 1);
                    $row.data('sizemode', data.size_mode || 'std');

                    // Trigger recalc
                    $row.find('.box-qty').focus();
                    recalcRow($row);
                    recalcAll();
                });
            }

            function formatProduct(repo) {
                if (repo.loading) return repo.text;
                let stock = repo.stock !== undefined ? repo.stock : 0;
                let sku = repo.sku || 'N/A';
                let badgeClass = 'bg-info'; // Neutral for Purchase

                return $(`
            <div class="clearfix">
                <div class="float-start">
                    <div class="fw-bold">${repo.name || repo.text}</div>
                    <small class="text-muted">SKU: ${sku}</small>
                </div>
                <div class="float-end">
                    <span class="badge ${badgeClass} rounded-pill">Stock: ${stock}</span>
                </div>
            </div>
            `);
            }

            function formatSelection(repo) {
                return repo.name || repo.text;
            }

            function recalcRow($row) {
                // Read BOX input
                const boxesStr = $row.find('.box-qty').val() || "0";
                const ppb = parseFloat($row.find('.pack-size').val()) || 1;

                let totalPieces = 0;
                let boxes = 0;
                let loose = 0;

                // Box.Loose Logic (Similar to shared_logic)
                if (ppb > 1 && boxesStr.includes('.')) {
                    const parts = boxesStr.split('.');
                    boxes = parseInt(parts[0]) || 0;
                    loose = parts[1] ? parseInt(parts[1]) : 0;

                    totalPieces = (boxes * ppb) + loose;
                } else {
                    // Whole boxes
                    boxes = parseFloat(boxesStr) || 0;
                    totalPieces = boxes * ppb;
                }

                // Update the hidden/readonly Piece Input (which is sent as qty[])
                $row.find('.qty-pcs').val(totalPieces);

                const price = parseFloat($row.find('.price').val()) || 0;

                // Discount
                const discPct = parseFloat($row.find('.item-disc-percent').val()) || 0;

                // Total Amount calculation
                let total = totalPieces * price;

                // Discount Amount
                const discAmt = total * (discPct / 100);
                $row.find('.item-disc-amt').val(discAmt.toFixed(2));

                total = total - discAmt;

                $row.data('total-pieces', totalPieces);

                $row.find('.row-total').val(total.toFixed(2));
                $row.find('.cost-pc').val(price.toFixed(2));
            }

            function recalcAll() {
                let totalQty = 0;
                let subtotal = 0;

                $('#purchaseTableBody tr').each(function() {
                    let qty = $(this).data('total-pieces');
                    // Fallback if data attribute not set
                    if (qty === undefined) {
                        qty = parseFloat($(this).find('.qty-pcs').val()) || 0;
                    }
                    const total = parseFloat($(this).find('.row-total').val()) || 0;

                    totalQty += qty;
                    subtotal += total;
                });

                $('#tQty').text(totalQty.toFixed(2));
                $('#tSub').text(subtotal.toFixed(2));
                $('#subtotalInput').val(subtotal.toFixed(2));

                const billDisc = parseFloat($('#billDiscount').val()) || 0;
                const extraCost = parseFloat($('#extraCost').val()) || 0;

                const net = subtotal - billDisc + extraCost;

                $('#tPayable').text(net.toFixed(2));
                $('#netAmountInput').val(net.toFixed(2));
                $('#totalAmount').text(subtotal.toFixed(2));
            }
        });
    </script>
@endsection
