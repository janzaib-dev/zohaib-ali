@extends('admin_panel.layout.app')

@section('content')
    <style>
        /* Reuse styles from add_sale222 */
        .col-product {
            width: 250px;
            min-width: 200px;
        }

        .col-warehouse {
            width: 150px;
        }

        .col-stock {
            width: 80px;
            background: #f9f9f9;
        }

        .col-qty {
            width: 100px;
        }

        .col-loose {
            width: 100px;
        }

        .col-pieces {
            width: 100px;
            background: #f9f9f9;
        }

        .col-price {
            width: 110px;
        }

        .col-disc {
            width: 100px;
        }

        .col-disc-amt {
            width: 100px;
        }

        .col-price-p {
            width: 100px;
            background: #f9f9f9;
        }

        .col-price-m2 {
            width: 100px;
            background: #f9f9f9;
        }

        .col-amount {
            width: 120px;
            font-weight: bold;
            background: #f0f8ff;
        }

        .col-action {
            width: 50px;
            text-align: center;
        }

        .sales-table th {
            font-size: 11px;
            text-transform: uppercase;
            background: #f8f9fa;
            text-align: center;
            vertical-align: middle;
        }

        .sales-table td {
            padding: 0.25rem;
            vertical-align: middle;
        }

        .form-control,
        .form-select {
            font-size: 13px;
            border-radius: 2px;
            padding: 0.25rem 0.5rem;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #86b7fe;
        }

        .input-readonly {
            background-color: #e9ecef !important;
            pointer-events: none;
            border: none;
        }

        .text-end {
            text-align: right !important;
        }

        /* validation styles */
        .invalid-input {
            border: 1px solid #dc3545 !important;
        }

        .invalid-select+.select2-container .select2-selection {
            border: 1px solid #dc3545 !important;
        }

        .invalid-cell {
            background-color: #fff0f0 !important;
        }

        .section-title {
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            color: #555;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .rv-row {
            background: #fdfdfd;
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #eee;
            margin-bottom: 5px;
        }

        .totals-card {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>

    <div class="px-4 py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0 text-primary fw-bold"><i class="bi bi-pencil-square"></i> Edit Sale / Confirm (Order
                #{{ $sale->id }})</h4>
            <div>
                <a href="{{ route('sale.index') }}" class="btn btn-sm btn-outline-secondary">Back to List</a>
            </div>
        </div>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('sales.update', $sale->id) }}" method="POST" id="saleForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="booking_id" id="booking_id" value="{{ $sale->id }}">
            <input type="hidden" name="action" value="sale">

            {{-- LEFT: Customer & Info --}}
            <div class="row g-3">
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm p-3">
                        <div class="row g-3">
                            {{-- Header / Customer --}}
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Customer</label>
                                <select class="form-select js-customer" name="customer" id="customerSelect">
                                    @foreach ($Customer as $c)
                                        <option value="{{ $c->id }}"
                                            {{ $sale->customer_id == $c->id ? 'selected' : '' }}>{{ $c->customer_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Date</label>
                                <input type="text" class="form-control" value="{{ date('d-m-Y') }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Reference</label>
                                <input type="text" class="form-control" name="reference" value="{{ $sale->reference }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Current Status</label>
                                <input type="text" class="form-control" value="{{ $sale->sale_status ?? 'Draft' }}"
                                    readonly>
                            </div>

                        </div>
                    </div>
                </div>
            </div>


            {{-- ITEMS TABLE --}}
            <div class="card border-0 shadow-sm mt-3 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="section-title mb-0">Items</div>
                    <button type="button" class="btn btn-sm btn-primary" id="btnAdd">Add Row</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered sales-table mb-0">
                        <thead>
                            <tr>
                                <th class="col-product">Product</th>
                                <th class="col-warehouse">Warehouse</th>
                                <th class="col-stock">Stock</th>
                                <th class="col-qty">Qty</th>
                                <th class="col-loose">Loose Pcs</th>
                                <th class="col-pieces">T.Pieces</th>
                                <th class="col-price">Retail Price</th>
                                <th class="col-disc">Disc %</th>
                                <th class="col-disc-amt">Disc Amt</th>
                                <th class="col-price-p">Price/Pc</th>
                                <th class="col-price-m2">Price/m²</th>
                                <th class="col-amount">Amount</th>
                                <th class="col-action">—</th>
                            </tr>
                        </thead>
                        <tbody id="salesTableBody">
                            @foreach ($saleItems as $index => $item)
                                <tr>
                                    <!-- PRODUCT -->
                                    <td class="col-product">
                                        <select class="form-select product" name="product_id[]" style="width:100%">
                                            <option value="{{ $item['product_id'] }}" selected>{{ $item['item_name'] }}
                                            </option>
                                        </select>
                                    </td>

                                    <!-- WAREHOUSE -->
                                    <td class="col-warehouse">
                                        <select class="form-select warehouse" name="warehouse_id[]">
                                            <option value="{{ $item['warehouse_id'] }}" selected>
                                                {{ $item['warehouse_name'] }}</option>
                                        </select>
                                    </td>

                                    <!-- STOCK (Fetched via JS) -->
                                    <td class="col-stock">
                                        <input type="text" class="form-control stock text-center input-readonly" readonly
                                            tabindex="-1">
                                    </td>

                                    <!-- QTY -->
                                    <td class="col-qty">
                                        <input type="text" class="form-control sales-qty text-end" name="qty[]"
                                            value="{{ $item['qty'] }}">
                                    </td>

                                    <!-- LOOSE PIECES -->
                                    <td class="col-loose">
                                        <input type="text" class="form-control loose-pieces text-end"
                                            name="loose_pieces[]" value="{{ $item['loose_pieces'] }}">
                                    </td>

                                    <!-- Total Pieces -->
                                    <td class="col-pieces">
                                        <input type="text" class="form-control total-pieces text-end input-readonly"
                                            name="total_pieces[]" value="{{ $item['total_pieces'] }}" readonly
                                            tabindex="-1">
                                    </td>

                                    <!-- RETAIL PRICE -->
                                    <td class="col-price">
                                        <input type="text" class="form-control retail-price text-end input-readonly"
                                            name="price[]" value="{{ $item['price'] }}" readonly tabindex="-1">
                                    </td>

                                    <!-- DISCOUNT -->
                                    <td class="col-disc">
                                        <div class="discount-wrapper input-group input-group-sm">
                                            <input type="number" class="form-control discount-value text-end"
                                                name="item_disc[]" value="{{ $item['discount'] }}">
                                            <!-- Assume percent for now based on controller output -->
                                            <button type="button" class="btn btn-outline-secondary discount-toggle"
                                                data-type="percent" tabindex="-1">%</button>
                                        </div>
                                    </td>

                                    <!-- DISCOUNT AMOUNT (Auto calc) -->
                                    <td class="col-disc-amt">
                                        <input type="text" class="form-control discount-amount text-end"
                                            value="0.00">
                                    </td>

                                    <!-- Price/Piece -->
                                    <td class="col-price-p">
                                        <input type="text" class="form-control price-per-piece text-end input-readonly"
                                            name="price_per_piece[]" value="{{ $item['price_per_piece'] }}" readonly
                                            tabindex="-1">
                                    </td>

                                    <!-- Price/m2 -->
                                    <td class="col-price-m2">
                                        <input type="text" class="form-control price-per-m2 text-end input-readonly"
                                            name="price_per_m2[]" value="{{ $item['price_per_m2'] }}" readonly
                                            tabindex="-1">
                                    </td>

                                    <!-- NET AMOUNT -->
                                    <td class="col-amount">
                                        <input type="text" class="form-control sales-amount text-end input-readonly"
                                            name="total[]" value="{{ $item['total'] }}" readonly tabindex="-1">
                                    </td>

                                    <!-- ACTION -->
                                    <td class="col-action">
                                        <button type="button" class="btn btn-sm btn-outline-danger del-row"
                                            tabindex="-1">&times;</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="11" class="text-end fw-bold">Total:</td>
                                <td class="text-end fw-bold"><span id="totalAmount">0.00</span></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- TOTALS SECTION --}}
            <div class="row g-3 mt-3">
                <div class="col-lg-7">
                    {{-- Spacer or Receipts Placeholder --}}
                    <div class="section-title mb-2">Internal Note / Receipts</div>
                    <div class="border p-3 rounded">
                        <p class="text-muted small">Receipts can be handled in the main add sale page. In Edit mode, we
                            focus on item adjustments. (Receipt editing disabled here to prevent ledger duplicate issues)
                        </p>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="section-title mb-2">Totals</div>
                    <div class="totals-card p-3">
                        <div class="row py-1">
                            <div class="col-7 text-muted">Total Qty</div>
                            <div class="col-5 text-end"><span id="tQty">0</span></div>
                        </div>
                        <div class="row py-1">
                            <div class="col-7 text-muted">Invoice Gross</div>
                            <div class="col-5 text-end"><span id="tGross">0.00</span></div>
                        </div>
                        <div class="row py-1">
                            <div class="col-7 text-muted">Line Discount</div>
                            <div class="col-5 text-end"><span id="tLineDisc">0.00</span></div>
                        </div>
                        <div class="row py-1">
                            <div class="col-7 fw-semibold">Sub-Total</div>
                            <div class="col-5 text-end fw-semibold"><span id="tSub">0.00</span></div>
                        </div>
                        {{-- Extra Disc --}}
                        <div class="row py-1">
                            <div class="col-7">Additional Discount</div>
                            <div class="col-5 text-end">
                                <input type="number" class="form-control form-control-sm text-end"
                                    name="total_extra_cost" id="extraDiscount"
                                    value="{{ $sale->total_extradiscount ?? 0 }}">
                            </div>
                        </div>

                        <div class="row py-2 border-top mt-2">
                            <div class="col-7 fw-bold text-primary">Net Total</div>
                            <div class="col-5 text-end fw-bold text-primary"><span id="tNet">0.00</span></div>
                        </div>

                        {{-- Hidden Fields --}}
                        <input type="hidden" name="total_subtotal" id="subTotal2" value="0">
                        <input type="hidden" name="total_net" id="totalBalance" value="0">
                        <input type="hidden" name="total_amount_Words" id="amountInWords">
                    </div>
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="d-flex flex-wrap gap-2 justify-content-center p-3 mt-3 border-top">
                <button type="submit" class="btn btn-primary" id="btnSave">
                    <i class="bi bi-check-lg"></i> Update Sale
                </button>
                @if ($sale->sale_status !== 'posted')
                    <button type="button" class="btn btn-success" id="btnPost">
                        <i class="bi bi-file-earmark-check"></i> Post Final
                    </button>
                @endif
                <a href="{{ route('sale.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('.js-customer').select2();

            // 1. Initialize existing rows
            $('#salesTableBody tr').each(function() {
                const $row = $(this);
                initProductSelect2($row.find('.product'));
                // Trigger calc for existing values
                computeRow($row);

                // Load warehouses options (deferred to avoid spam? or trigger?)
                // Let's trigger it so we get the stock count!
                // We need product ID
                const pid = $row.find('.product').val();
                if (pid) {
                    loadWarehousesForProduct($row, pid, true); // true = keep selection
                    fetchProductPrice($row, pid, true); // true = don't overwrite manual inputs if matches
                }
            });
            updateGrandTotals();

            // 2. Add Row
            $('#btnAdd').click(addNewRow);

            // 3. Post Action
            // 3. Post Action
            $('#btnPost').click(function() {
                Swal.fire({
                    title: 'Confirm Post?',
                    text: "This will deduct stock and finalize the sale!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Post it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = $('#saleForm').serialize();
                        Swal.showLoading();

                        // 1. Update Sale First
                        $.post('{{ route('sales.update', $sale->id) }}', formData)
                            .done(function(res) {
                                if (!res.ok) {
                                    Swal.fire('Error', res.msg || 'Update failed', 'error');
                                    return;
                                }

                                // 2. Then Post Final
                                // Exclude _method=PUT to ensure valid POST request
                                const postData = formData.replace(/&?_method=PUT/, '');
                                $.post('{{ route('sales.post_final') }}', postData)
                                    .done(function(postRes) {
                                        if (postRes.ok) {
                                            Swal.fire({
                                                title: 'Posted!',
                                                text: 'Sale posted successfully.',
                                                icon: 'success',
                                                timer: 1500,
                                                showConfirmButton: false
                                            }).then(() => {
                                                window.location.href =
                                                    "{{ route('sale.index') }}";
                                            });
                                        } else {
                                            Swal.fire('Error', postRes.msg, 'error');
                                        }
                                    })
                                    .fail(function(xhr) {
                                        Swal.fire('Error', 'Post Request Failed: ' + (xhr
                                                .responseJSON?.msg || xhr.statusText),
                                            'error');
                                    });
                            })
                            .fail(function(xhr) {
                                Swal.fire('Error', 'Update Request Failed: ' + (xhr.responseJSON
                                    ?.msg || xhr.statusText), 'error');
                            });
                    }
                });
            });

            // 4. Events
            $(document).on('click', '.del-row', function() {
                if ($('#salesTableBody tr').length > 1) {
                    $(this).closest('tr').remove();
                    updateGrandTotals();
                }
            });

            $(document).on('input',
                '.sales-qty, .loose-pieces, .discount-value, #extraDiscount',
                function() {
                    const $row = $(this).closest('tr');
                    if ($row.length) computeRow($row);
                    updateGrandTotals();
                });

        });

        // --- FUNCTIONS (Reused) ---

        function addNewRow() {
            const rowHtml = `
      <tr>
        <td class="col-product"><select class="form-select product" name="product_id[]" style="width:100%"><option value=""></option></select></td>
        <td class="col-warehouse"><select class="form-select warehouse" name="warehouse_id[]"><option value="">Select Warehouse</option></select></td>
        <td class="col-stock"><input type="text" class="form-control stock text-center input-readonly" readonly tabindex="-1"></td>
        <td class="col-qty"><input type="text" class="form-control sales-qty text-end" name="qty[]"></td>
        <td class="col-loose"><input type="text" class="form-control loose-pieces text-end" name="loose_pieces[]" value="0"></td>
        <td class="col-pieces"><input type="text" class="form-control total-pieces text-end input-readonly" name="total_pieces[]" readonly tabindex="-1"></td>
        <td class="col-price"><input type="text" class="form-control retail-price text-end input-readonly" name="price[]" readonly tabindex="-1"></td>
        <td class="col-disc"><div class="discount-wrapper input-group input-group-sm"><input type="number" class="form-control discount-value text-end" name="item_disc[]"><button type="button" class="btn btn-outline-secondary" tabindex="-1">%</button></div></td>
        <td class="col-disc-amt"><input type="text" class="form-control discount-amount text-end" value="0.00" readonly></td>
        <td class="col-price-p"><input type="text" class="form-control price-per-piece text-end input-readonly" name="price_per_piece[]" readonly tabindex="-1"></td>
        <td class="col-price-m2"><input type="text" class="form-control price-per-m2 text-end input-readonly" name="price_per_m2[]" readonly tabindex="-1"></td>
        <td class="col-amount"><input type="text" class="form-control sales-amount text-end input-readonly" name="total[]" readonly tabindex="-1"></td>
        <td class="col-action"><button type="button" class="btn btn-sm btn-outline-danger del-row">&times;</button></td>
      </tr>`;
            const $row = $(rowHtml);
            $('#salesTableBody').append($row);
            initProductSelect2($row.find('.product'));
        }

        function initProductSelect2($el) {
            $el.select2({
                placeholder: 'Search Product',
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
                templateResult: formatProduct,
                templateSelection: formatSelection
            });

            // Bind change event manually here to ensure it works for dynamic rows
            $el.on('select2:select', function(e) {
                const pid = $(this).val();
                const $row = $(this).closest('tr');
                loadWarehousesForProduct($row, pid);
                fetchProductPrice($row, pid);
            });
        }

        function formatProduct(repo) {
            if (repo.loading) return repo.text;
            let stock = repo.stock !== undefined ? repo.stock : 0;
            return $(
                `<div><div class="fw-bold">${repo.name || repo.text}</div><small>Stock: ${stock}</small></div>`
            );
        }

        function formatSelection(repo) {
            return repo.name || repo.text;
        }

        function loadWarehousesForProduct($row, pid, keepSelection = false) {
            const $wh = $row.find('.warehouse');
            const currentVal = $wh.val();
            $wh.html('<option>Loading...</option>');

            $.get('{{ route('warehouses.get') }}', {
                    product_id: pid
                })
                .done(function(list) {
                    let opts = '<option value="">Select</option>';
                    let stock = 0;
                    let selectedStock = 0;
                    (list || []).forEach(w => {
                        const isSel = keepSelection && w.warehouse_id ==
                            currentVal;
                        if (isSel) selectedStock = w.stock;
                        if (keepSelection && w.warehouse_id == currentVal) {
                            // Keep it selected
                            opts +=
                                `<option value="${w.warehouse_id}" data-stock="${w.stock}" selected>${w.warehouse_name} (${w.stock})</option>`;
                        } else {
                            opts +=
                                `<option value="${w.warehouse_id}" data-stock="${w.stock}">${w.warehouse_name} (${w.stock})</option>`;
                        }
                    });
                    $wh.html(opts);

                    if (keepSelection && currentVal) {
                        $row.find('.stock').val(selectedStock);
                    }
                });

            // Warehouse change updates stock
            $wh.off('change').on('change', function() {
                const s = $(this).find(':selected').data('stock') || 0;
                $row.find('.stock').val(s);
            });
        }

        function fetchProductPrice($row, pid, keepValues = false) {
            if (!keepValues) {
                // Clear old values if strictly new selection
            }
            $.get('{{ url('get-price') }}', {
                product_id: pid
            }).done(function(res) {
                if (!keepValues) {
                    $row.find('.retail-price').val(res.retail_price || 0);
                    $row.find('.price-per-piece').val(res
                        .purchase_price_per_piece || 0); // Simplified logic
                    $row.find('.price-per-m2').val(res.price_per_m2 || 0);
                }
                // Store meta
                $row.data('pieces_per_box', res.pieces_per_box || 0);
                $row.data('size_mode', res.size_mode);
                computeRow($row);
                updateGrandTotals();
            });
        }

        function computeRow($row) {
            const qty = parseFloat($row.find('.sales-qty').val() || 0);
            const loose = parseFloat($row.find('.loose-pieces').val() || 0);
            const price = parseFloat($row.find('.retail-price').val() || 0);
            const disc = parseFloat($row.find('.discount-value').val() || 0);

            // total pieces logic
            const ppBox = parseFloat($row.data('pieces_per_box') || 0);
            let totPieces = qty; // default by_pieces
            if ($row.data('size_mode') !== 'by_pieces' && ppBox > 0) {
                totPieces = (qty * ppBox) + loose;
            } else {
                totPieces = qty + loose; // simplified for by_pieces
            }
            $row.find('.total-pieces').val(totPieces);

            // Amount
            // Gross = (qty * price) + (loose * price_per_piece? or included?)
            // Assuming simple case: price is per unit (box or piece)
            // If by_size/carton, price is per box.
            const gross = qty * price;
            // Discount
            const dam = (gross * disc) / 100;
            $row.find('.discount-amount').val(dam.toFixed(2));

            const net = gross - dam;
            $row.find('.sales-amount').val(net.toFixed(2));
        }

        function updateGrandTotals() {
            let tQty = 0,
                tGross = 0,
                tNet = 0;
            $('#salesTableBody tr').each(function() {
                tQty += parseFloat($(this).find('.sales-qty').val() || 0);
                tGross += parseFloat($(this).find('.retail-price').val() || 0) *
                    parseFloat($(this).find(
                        '.sales-qty').val() || 0);
                tNet += parseFloat($(this).find('.sales-amount').val() || 0);
            });

            const extra = parseFloat($('#extraDiscount').val() || 0);
            const final = Math.max(0, tNet - extra);

            $('#tQty').text(tQty);
            $('#tGross').text(tGross.toFixed(2));
            $('#tSub').text(tNet.toFixed(2));
            $('#tNet').text(final.toFixed(2));

            $('#subTotal2').val(tNet.toFixed(2));
            $('#totalBalance').val(final.toFixed(2));
            $('#totalAmount').text(final.toFixed(2));
        }
    </script>
@endsection
