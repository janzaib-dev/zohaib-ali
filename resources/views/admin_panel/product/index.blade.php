@extends('admin_panel.layout.app')
@section('content')
    <style>
        div.dataTables_wrapper div.dataTables_length select {
            width: 75px !important
        }
    </style>



    <div class="card shadow-sm border-0">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">📦 Product List</h5>
                <small class="text-muted">Manage all products here</small>
            </div>
            <div class="d-flex justify-content-between align-items-end gap-1">
                @if (auth()->user()->can('discount.products.view') || auth()->user()->email === 'admin@admin.com')
                    <a href="{{ route('discount.index') }}" class="btn btn-success btn-sm">
                        View Discount
                    </a>
                @endif
                @if (auth()->user()->can('products.create') || auth()->user()->email === 'admin@admin.com')
                    <a href="create_prodcut" class="btn btn-primary"> Add product</a>
                @endif

                @if (auth()->user()->can('discount.products.create') || auth()->user()->email === 'admin@admin.com')
                    <button id="createDiscountBtn" class="btn btn-success btn-sm">
                        ➡ Create Discount
                    </button>
                @endif
            </div>

        </div>

        <div class="card-body">
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    ✅ {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table id="productTable" class="table table-striped table-bordered align-middle nowrap" style="width:100%">
                    <div class="mb-3">
                        <input type="text" id="search_all" class="form-control"
                            placeholder="Search Item Name, Code, Category, Brand">
                    </div>

                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>#</th>
                            <th>Code</th>
                            <th>Image</th>
                            <th>Category</th>
                            <th>Item Name</th>
                            <th>Dimensions (cm)</th>
                            <th>Total m²</th>
                            <th>Price / m²</th>
                            <th>Sale Total</th>
                            <th>Brand</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $key => $product)
                            <tr>
                                <td><input type="checkbox" class="selectProduct" value="{{ $product->id }}"></td>
                                <td>{{ $key + 1 }}</td>
                                <td class="fw-bold">{{ $product->item_code }}</td>
                                <td>
                                    @if ($product->image)
                                        <img src="{{ asset('uploads/products/' . $product->image) }}" alt="Product"
                                            width="50" height="50" class="rounded border">
                                    @else
                                        <span class="badge bg-secondary">No Img</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $product->category_relation->name ?? '-' }}</strong><br>
                                    <small class="text-muted">{{ $product->sub_category_relation->name ?? '-' }}</small>
                                </td>
                                <td>{{ $product->item_name }}</td>
                                <td>
                                    @if ($product->height && $product->width)
                                        {{ $product->height }} x {{ $product->width }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="fw-bold">{{ number_format($product->total_m2, 2) }}</td>
                                <td>Rs. {{ number_format($product->price_per_m2, 2) }}</td>
                                <td class="text-success fw-bold">Rs. {{ number_format($product->total_price, 2) }}</td>
                                <td>{{ $product->brand->name ?? '-' }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-warning viewProductBtn"
                                        data-id="{{ $product->id }}">
                                        View
                                    </button>


                                    @if (auth()->user()->can('products.edit') || auth()->user()->email === 'admin@admin.com')
                                        <a href="{{ route('products.edit', $product->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            ✏ Edit
                                        </a>
                                    @endif

                                    <a href="{{ route('generate-barcode-image', $product->id) }}"
                                        class="btn btn-sm btn-outline-success">
                                        🏷 Barcode
                                    </a>



                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-end">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    {{-- add product modal --}}

    <div class="modal fade bd-example-modal-lg" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger">Please use the main "Add Product" page for the new per-m² flow.</p>
                </div>
            </div>
        </div>
    </div>


    <!-- Product Detail View Modal (Premium UI) -->
    <div class="modal fade" id="productViewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg"
                style="border-radius: 16px; overflow: hidden; background-color: #f8f9fa;">

                <!-- Modal Header -->
                <div class="modal-header bg-white border-bottom align-items-center py-3 px-4 shadow-sm z-index-1">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 48px; height: 48px;">
                            <i class="las la-box-open fs-2"></i>
                        </div>
                        <div>
                            <h4 class="modal-title fw-bold text-dark mb-0" id="view_item_name">Product Name</h4>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="badge bg-light text-dark border"><i class="las la-barcode"></i> <span
                                        id="view_item_code">CODE</span></span>
                                <span class="badge" id="view_size_mode_badge">Mode</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-0">
                    <!-- Loading Spinner -->
                    <div id="modalLoadingSpinner" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary shadow-sm" style="width: 3rem; height: 3rem;"
                            role="status"></div>
                        <p class="mt-3 text-muted fw-semibold">Loading product details...</p>
                    </div>

                    <!-- Main Content -->
                    <div class="row g-0" id="modalContentRow">

                        <!-- Left Sidebar: Image & Core Info -->
                        <div class="col-lg-4 bg-white border-end p-4">
                            <!-- Image Container -->
                            <div class="text-center mb-4">
                                <div class="position-relative mx-auto rounded-4 shadow-sm"
                                    style="width: 200px; height: 200px; overflow: hidden; border: 4px solid #fff; background: #f8f9fa;">
                                    <img id="view_image_preview" src=""
                                        class="img-fluid w-100 h-100 object-fit-cover d-none">
                                    <div id="view_image_placeholder"
                                        class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                                        <i class="las la-image opacity-50" style="font-size: 4rem;"></i>
                                        <small class="fw-semibold mt-2">No Image</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Hierarchy / Categorization -->
                            <h6 class="fw-bold text-uppercase text-muted letter-spacing-1 mb-3"
                                style="font-size: 0.75rem;">Classification</h6>
                            <div class="d-flex flex-column gap-3 mb-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-primary bg-primary bg-opacity-10 p-2 rounded"><i
                                            class="las la-tags fs-5"></i></div>
                                    <div>
                                        <small class="text-muted d-block">Category & Sub</small>
                                        <span class="fw-bold text-dark" id="view_cat_sub">-</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-info bg-info bg-opacity-10 p-2 rounded"><i
                                            class="las la-copyright fs-5"></i></div>
                                    <div>
                                        <small class="text-muted d-block">Brand / Model</small>
                                        <span class="fw-bold text-dark" id="view_brand_model">-</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-secondary bg-secondary bg-opacity-10 p-2 rounded"><i
                                            class="las la-palette fs-5"></i></div>
                                    <div>
                                        <small class="text-muted d-block">Colors</small>
                                        <span class="fw-bold text-dark" id="view_color">-</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-warning bg-warning bg-opacity-10 p-2 rounded"><i
                                            class="las la-file-alt fs-5"></i></div>
                                    <div>
                                        <small class="text-muted d-block">HS Code</small>
                                        <span class="fw-bold text-dark" id="view_hs_code">-</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-success bg-success bg-opacity-10 p-2 rounded"><i
                                            class="las la-calendar fs-5"></i></div>
                                    <div>
                                        <small class="text-muted d-block">Created On</small>
                                        <span class="fw-bold text-dark" id="view_created_at">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Body: Specs, Stock & Financials -->
                        <div class="col-lg-8 p-4">

                            <div class="row g-4">
                                <!-- Measurement & Dimensions Container -->
                                <div class="col-12">
                                    <h6 class="fw-bold text-uppercase text-info mb-3" style="font-size: 0.75rem;">
                                        <i class="las la-ruler-combined fs-6 me-1"></i> Measurement & Packings
                                    </h6>
                                    <div class="card shadow-sm border-0 border-start border-4 border-info">
                                        <div class="card-body">

                                            <!-- By Size Layout -->
                                            <div id="sec_by_size" class="d-none">
                                                <div class="row text-center g-3">
                                                    <div class="col-sm-3 col-6">
                                                        <div class="p-3 bg-light rounded-3 h-100">
                                                            <small
                                                                class="text-muted d-block text-uppercase fw-semibold mb-1"
                                                                style="font-size:10px;">Dimensions</small>
                                                            <strong class="fs-5 text-dark" id="view_dimensions">-</strong>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3 col-6">
                                                        <div class="p-3 bg-light rounded-3 h-100">
                                                            <small
                                                                class="text-muted d-block text-uppercase fw-semibold mb-1"
                                                                style="font-size:10px;">m² / Pc</small>
                                                            <strong class="fs-5 text-dark" id="view_m2_piece">-</strong>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3 col-6">
                                                        <div class="p-3 bg-light rounded-3 h-100">
                                                            <small
                                                                class="text-muted d-block text-uppercase fw-semibold mb-1"
                                                                style="font-size:10px;">Pcs / Box</small>
                                                            <strong class="fs-5 text-dark"
                                                                id="view_pcs_box_size">-</strong>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3 col-6">
                                                        <div
                                                            class="p-3 text-white rounded-3 shadow-sm bg-info h-100 d-flex flex-column justify-content-center">
                                                            <small
                                                                class="text-white-50 d-block text-uppercase fw-bold mb-1"
                                                                style="font-size:10px;">Total Area</small>
                                                            <strong class="fs-4" id="view_total_m2">-</strong><small>
                                                                m²</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- By Box Layout -->
                                            <div id="sec_packing" class="d-none">
                                                <div class="row text-center g-3">
                                                    <div class="col-4">
                                                        <div class="p-3 bg-light rounded-3">
                                                            <small
                                                                class="text-muted d-block text-uppercase fw-semibold mb-1"
                                                                style="font-size:10px;">Pcs / Box</small>
                                                            <strong class="fs-4 text-dark" id="view_pcs_box">-</strong>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-3">
                                                            <small class="d-block text-uppercase fw-bold mb-1"
                                                                style="font-size:10px;">Total Boxes</small>
                                                            <strong class="fs-4" id="view_boxes_qty">-</strong>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-3">
                                                            <small class="d-block text-uppercase fw-bold mb-1"
                                                                style="font-size:10px;">Loose Pcs</small>
                                                            <strong class="fs-4" id="view_loose_pcs">-</strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- By Piece Layout -->
                                            <div id="sec_by_piece" class="d-none text-center">
                                                <div
                                                    class="p-4 bg-light rounded-3 d-flex align-items-center justify-content-center gap-3">
                                                    <i class="las la-puzzle-piece fs-1 text-primary"></i>
                                                    <div class="text-start">
                                                        <h5 class="fw-bold mb-0">Single Unit Item</h5>
                                                        <small class="text-muted">Tracked exclusively by individual
                                                            pieces.</small>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- Financials Container -->
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-uppercase text-success mb-3" style="font-size: 0.75rem;">
                                        <i class="las la-wallet fs-6 me-1"></i> Pricing Summary
                                    </h6>
                                    <div class="card shadow-sm border-0 h-100 border-start border-4 border-success">
                                        <div class="card-body">

                                            <div class="mb-4">
                                                <div
                                                    class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                                                    <div>
                                                        <small class="text-muted d-block text-uppercase fw-bold mb-1"
                                                            style="font-size:10px;" id="lbl_price_unit">Sale Price</small>
                                                        <span class="fs-4 fw-bold text-success"
                                                            id="view_price_unit">-</span>
                                                    </div>
                                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px;">
                                                        <i class="las la-arrow-up"></i>
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <small class="text-muted d-block text-uppercase fw-bold mb-1"
                                                            style="font-size:10px;" id="lbl_purch_unit">Purchase
                                                            Price</small>
                                                        <span class="fs-5 fw-bold text-dark" id="view_purch_unit">-</span>
                                                    </div>
                                                    <div class="bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px;">
                                                        <i class="las la-arrow-down"></i>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- Stock Valuation Container -->
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-uppercase text-danger mb-3" style="font-size: 0.75rem;">
                                        <i class="las la-boxes fs-6 me-1"></i> Stock Valuation
                                    </h6>
                                    <div class="card shadow-sm border-0 h-100 bg-dark text-white position-relative overflow-hidden"
                                        style="border-radius: 12px;">
                                        <i class="las la-chart-pie position-absolute text-white opacity-25"
                                            style="font-size: 10rem; right: -20px; bottom: -20px;"></i>
                                        <div class="card-body position-relative z-index-1">

                                            <div class="mb-3">
                                                <small class="text-white-50 d-block text-uppercase fw-bold mb-1"
                                                    style="font-size:10px;">Total Physical Stock (Pcs)</small>
                                                <span class="display-6 fw-bold text-warning"
                                                    id="view_total_stock_qty">0</span>
                                            </div>

                                            <hr class="text-secondary opacity-50 my-2">

                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <div>
                                                    <small class="text-white-50 d-block text-uppercase fw-bold mb-0"
                                                        style="font-size:10px;">Est. Total Sale Value</small>
                                                    <span class="fs-5 fw-bold text-white lh-1"
                                                        id="view_sale_total">-</span>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-2">
                                                <div>
                                                    <small class="text-white-50 d-block text-uppercase fw-bold mb-0"
                                                        style="font-size:10px;">Est. Purchase Value</small>
                                                    <span class="fs-6 fw-semibold text-white-50 lh-1"
                                                        id="view_purch_total">-</span>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </div> <!-- End Row -->

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- product model --}}
    <script>
        $(document).on('click', '.viewProductBtn', function() {
            let productId = $(this).data('id');

            // 1. Reset & Loading State
            $('#modalContentRow').addClass('d-none');
            $('#modalLoadingSpinner').removeClass('d-none');
            $('#productViewModal').modal('show');

            $.ajax({
                url: "/productview/" + productId,
                type: "GET",
                success: function(product) {

                    // 2. Hide Spinner, Show Content
                    $('#modalLoadingSpinner').addClass('d-none');
                    $('#modalContentRow').removeClass('d-none');

                    // --- Basic ---
                    $('#view_item_name').text(product.item_name ?? 'Unknown Product');
                    $('#view_item_code').text(product.item_code ?? 'N/A');
                    $('#view_cat_sub').text((product.category_relation?.name ?? '') + (product
                        .sub_category_relation ? ' • ' + product.sub_category_relation.name : ''
                    ));
                    $('#view_brand_model').text((product.brand?.name ?? '-') + (product.model ? ' / ' +
                        product.model : ''));
                    $('#view_hs_code').text(product.hs_code ?? '-');
                    $('#view_created_at').text(product.created_at ? new Date(product.created_at)
                        .toLocaleDateString() : '-');

                    // --- Image ---
                    if (product.image) {
                        $('#view_image_preview').attr('src', '/uploads/products/' + product.image)
                            .removeClass('d-none');
                        $('#view_image_placeholder').addClass('d-none');
                    } else {
                        $('#view_image_preview').addClass('d-none');
                        $('#view_image_placeholder').removeClass('d-none');
                    }

                    // --- Colors ---
                    if (product.color) {
                        let parsed = [];
                        try {
                            parsed = JSON.parse(product.color);
                        } catch (e) {
                            parsed = product.color.split(',');
                        }
                        if (!Array.isArray(parsed)) parsed = [parsed];

                        let badges = parsed.filter(c => c.trim() !== '').map(c =>
                            `<span class="badge bg-light text-dark border">${c.trim()}</span>`);
                        $('#view_color').html(badges.length ? badges.join('') : '-');
                    } else {
                        $('#view_color').html('<span class="text-muted">-</span>');
                    }

                    // --- Mode & Layout Switching ---
                    let mode = product.size_mode ?? 'by_size';

                    // Defaults
                    $('#sec_by_size, #sec_packing, #sec_by_piece').addClass('d-none');

                    let calcBoxes = product.calculated_boxes_quantity ?? 0;
                    let calcLoose = product.calculated_loose_pieces ?? 0;
                    let calcTotal = product.calculated_total_stock_qty ?? 0;

                    let salePrice = 0;
                    let purchPrice = 0;
                    let estSaleVal = 0;
                    let estPurchVal = 0;

                    if (mode === 'by_size') {
                        $('#view_size_mode_badge').text('By Size').removeClass('bg-info bg-warning')
                            .addClass('bg-light text-primary border-primary');
                        $('#sec_by_size').removeClass('d-none');

                        // Fill Size Data
                        $('#view_dimensions').text((product.height ?? 0) + ' x ' + (product.width ??
                            0));
                        let m2Piece = ((product.height * product.width) / 10000).toFixed(4);
                        $('#view_m2_piece').text(m2Piece);
                        $('#view_boxes_qty_size').text(calcBoxes); // Box count for Size mode
                        $('#view_pcs_box_size').text(product.pieces_per_box ?? 0);
                        $('#view_total_m2').text(parseFloat(product.total_m2 ?? 0).toFixed(2));

                        // Stock
                        $('#view_total_stock_qty').text(calcTotal);

                        // Price Labels
                        $('#lbl_price_unit').text('Price per m²');
                        $('#lbl_purch_unit').text('Cost per m²');
                        salePrice = product.price_per_m2;
                        purchPrice = product.purchase_price_per_m2;

                        estSaleVal = (product.total_m2 ?? 0) * calcBoxes * salePrice;
                        estPurchVal = (product.total_m2 ?? 0) * calcBoxes * purchPrice;

                    } else if (mode === 'by_cartons') {
                        $('#view_size_mode_badge').text('By Box').removeClass(
                            'bg-light text-primary border-primary bg-warning').addClass(
                            'bg-info text-white border-0');
                        $('#sec_packing').removeClass('d-none');

                        $('#view_boxes_qty').text(calcBoxes);
                        $('#view_loose_pcs').text(calcLoose);
                        $('#view_pcs_box').text(product.pieces_per_box ?? '-');

                        // Stock
                        $('#view_total_stock_qty').text(calcTotal);

                        // Price Labels
                        $('#lbl_price_unit').text('Price per Box');
                        $('#lbl_purch_unit').text('Cost per Piece');
                        salePrice = product.sale_price_per_box;
                        purchPrice = product.purchase_price_per_piece;

                        // Calc Value
                        // Sale Value: Boxes * SalePricePerBox + Loose * (SalePricePerBox/PcsPerBox)
                        let ppb = product.pieces_per_box > 0 ? product.pieces_per_box : 1;
                        let pricePerPieceScale = salePrice / ppb;
                        estSaleVal = calcTotal * pricePerPieceScale;
                        estPurchVal = calcTotal * purchPrice;

                    } else { // by_pieces
                        $('#view_size_mode_badge').text('By Piece').removeClass(
                            'bg-light text-primary border-primary bg-info text-white').addClass(
                            'bg-warning text-dark border-0');
                        $('#sec_by_piece').removeClass('d-none');

                        // Stock
                        $('#view_total_stock_qty').text(calcTotal);

                        // Price Labels
                        $('#lbl_price_unit').text('Price per Piece');
                        $('#lbl_purch_unit').text('Cost per Piece');
                        salePrice = product.sale_price_per_box;
                        purchPrice = product.purchase_price_per_piece;

                        estSaleVal = calcTotal * salePrice;
                        estPurchVal = calcTotal * purchPrice;
                    }

                    // Format Financials
                    $('#view_price_unit').text('Rs. ' + parseFloat(salePrice || 0).toFixed(2));
                    $('#view_purch_unit').text('Rs. ' + parseFloat(purchPrice || 0).toFixed(2));
                    $('#view_sale_total').text('Rs. ' + parseFloat(estSaleVal || 0).toLocaleString(
                        'en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                    $('#view_purch_total').text('Rs. ' + parseFloat(estPurchVal || 0).toLocaleString(
                        'en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));

                    $('#productViewModal').modal('show');
                },
                error: function() {
                    $('#modalLoadingSpinner').addClass('d-none');
                    Swal.fire('Error', 'Could not fetch details', 'error');
                }
            });
        });
    </script>


    <script>
        $(document).ready(function() {

            // Select/Deselect all checkboxes
            $('#selectAll').click(function() {
                $('.selectProduct').prop('checked', this.checked);
            });

            // On "Create Discount" click
            $('#createDiscountBtn').click(function() {
                var selected = [];
                $('.selectProduct:checked').each(function() {
                    selected.push($(this).val());
                });

                if (selected.length === 0) {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Please select at least one product!",

                    });
                    return;
                }

                // Redirect with product IDs as query param
                window.location.href = "{{ route('discount.create') }}" + "?products=" + selected.join(
                    ',');
            });
        });
    </script>

    <script>
        $(document).ready(function() {

            function debounce(func, delay) {
                let timer;
                return function(...args) {
                    clearTimeout(timer);
                    timer = setTimeout(() => func.apply(this, args), delay);
                }
            }

            let table = $('#productTable').DataTable({
                responsive: true,
                paging: false,
                ordering: true,
                info: false,
                order: [
                    [1, 'asc']
                ],
                dom: '<"top"f>rt<"bottom"><"clear">',
                language: {
                    search: "",
                    searchPlaceholder: "Search by code, name, category, brand..."
                },
                columnDefs: [{
                    targets: [0, 11],
                    searchable: false
                }, ]
            }); // Optional: fast typing experience 
            $('.dataTables_filter input').off().on('keyup', function() {
                table.search(this.value).draw();
            });
            // ===== Initialize Products DataTable =====

        });
    </script>

    <!-- DataTables CSS -->
@endsection
<script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let cartonQuantityInput = document.getElementById("carton_quantity");
        let piecesPerCartonInput = document.getElementById("pieces_per_carton");
        let initialStockInput = document.getElementById("initial_stock");

        if (cartonQuantityInput && piecesPerCartonInput && initialStockInput) {
            function updateInitialStock() {
                let cartonQuantity = parseInt(cartonQuantityInput.value) || 0;
                let piecesPerCarton = parseInt(piecesPerCartonInput.value) || 0;
                initialStockInput.value = cartonQuantity * piecesPerCarton;
            }

            cartonQuantityInput.addEventListener("input", updateInitialStock);
            piecesPerCartonInput.addEventListener("input", updateInitialStock);
        }
    });


    $(document).ready(function() {
        // Add Product Modal: Fetch Subcategories on Category Change
        $('#categorySelect').change(function() {
            var categoryId = $(this).val();

            $('#subCategorySelect').html('<option value="">Loading...</option>');

            if (categoryId) {
                $.ajax({
                    url: "/get-subcategories/" + categoryId,

                    type: "GET",
                    data: {
                        category_id: categoryId
                    },
                    success: function(data) {
                        $('#subCategorySelect').html(
                            '<option value="">Select Sub-Category</option>');
                        $.each(data, function(key, subCategory) {
                            $('#subCategorySelect').append('<option value="' +
                                subCategory.id + '">' +
                                subCategory.name + '</option>');
                        });
                    },
                    error: function() {
                        alert('Error fetching subcategories.');
                    }
                });
            } else {
                $('#subCategorySelect').html('<option value="">Select Sub-Category</option>');
            }
        });

        // Edit Product Modal: Fetch Subcategories when Category is Changed
        $('#edit_category').change(function() {
            var categoryId = $(this).val();
            $('#edit_sub_category').html('<option value="">Loading...</option>');

            if (categoryId) {
                $.ajax({
                    url: "/get-subcategories/" + categoryId,

                    type: "GET",
                    data: {
                        category_id: categoryId
                    },
                    success: function(data) {
                        $('#edit_sub_category').html(
                            '<option value="">Select Sub-Category</option>');
                        $.each(data, function(key, subCategory) {
                            $('#edit_sub_category').append('<option value="' +
                                subCategory.sub_category_name + '">' +
                                subCategory.sub_category_name + '</option>');
                        });
                    },
                    error: function() {
                        alert('Error fetching subcategories.');
                    }
                });
            } else {
                $('#edit_sub_category').html('<option value="">Select Sub-Category</option>');
            }
        });
    });
</script>
