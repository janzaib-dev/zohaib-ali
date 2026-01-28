@extends('admin_panel.layout.app')
@section('content')
    <style>
        div.dataTables_wrapper div.dataTables_length select {
            width: 75px !important
        }
    </style>


    <style>
        /* Product View Modal Fields */
        /* ===== Professional Product View Modal ===== */

        #productViewModal {
            --label-size: 13px;
            --value-size: 16px;
            --heading-size: 17px;
        }

        /* Section headings */
        #productViewModal h6 {
            font-size: var(--heading-size);
            font-weight: 600;
            margin-bottom: 14px;
        }

        /* Labels */
        #productViewModal .label {
            display: block;
            font-size: var(--label-size);
            font-weight: 500;
            color: #6c757d;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        /* Values */
        #productViewModal .value {
            display: block;
            font-size: var(--value-size);
            font-weight: 600;
            color: #212529;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 10px 12px;
            min-height: 42px;
        }

        /* Right panel emphasis */
        #productViewModal #view_wholesale,
        #productViewModal #view_retail {
            font-size: 17px;
        }

        /* Stock visibility */
        #productViewModal #view_stock {
            font-size: 17px;
            font-weight: 700;
        }

        /* Card padding balance */
        #productViewModal .card-body {
            padding: 18px;
        }

        /* Modal overall text comfort */
        #productViewModal .modal-body {
            font-size: 15px;
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

    <!-- Product Detail View Modal -->
    <div class="modal fade" id="productViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3">

                <!-- Header -->
                <div class="modal-header bg-white border-bottom">
                    <div>
                        <h5 class="modal-title fw-semibold mb-0">
                            <i class="las la-cube text-primary me-1"></i> Product Details
                        </h5>
                        <small class="text-muted">Complete product information</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Body -->
                <div class="modal-body py-4 bg-light">
                    <div class="row g-4">

                        <!-- LEFT: Basic & Physical Specs -->
                        <div class="col-md-7">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-bottom-0 pb-0 pt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="fw-semibold text-primary mb-0">
                                            <i class="las la-info-circle me-1"></i> Basic Information
                                        </h6>
                                        <span class="badge bg-secondary fs-6" id="view_size_mode_badge">Mode</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <!-- Shared Basic Fields -->
                                        <div class="col-sm-6">
                                            <span class="label">Item Name</span>
                                            <span class="value" id="view_item_name"></span>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="label">Item Code</span>
                                            <span class="value" id="view_item_code"></span>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="label">Category / Sub</span>
                                            <span class="value" id="view_cat_sub"></span>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="label">Brand / Model</span>
                                            <span class="value" id="view_brand_model"></span>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="label">Barcode</span>
                                            <span class="value" id="view_barcode"></span>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="label">HS Code</span>
                                            <span class="value" id="view_hs_code"></span>
                                        </div>
                                        <div class="col-sm-12">
                                            <span class="label">Color</span>
                                            <span class="value" id="view_color"></span>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <!-- SECTION: By Size Details -->
                                    <div id="sec_by_size" class="d-none">
                                        <h6 class="fw-semibold text-dark mb-3"><i class="las la-ruler-combined"></i> Size
                                            Dimensions</h6>
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <span class="label">Dimensions (H x W)</span>
                                                <span class="value" id="view_dimensions"></span>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="label">m² per Piece</span>
                                                <span class="value" id="view_m2_piece"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- SECTION: Packaging (Size & Cartons) -->
                                    <div id="sec_packing" class="d-none mt-3">
                                        <h6 class="fw-semibold text-dark mb-3"><i class="las la-box"></i> Packaging
                                            Details</h6>
                                        <div class="row g-3">
                                            <div class="col-sm-4">
                                                <span class="label">Pieces / Box</span>
                                                <span class="value" id="view_pcs_box"></span>
                                            </div>
                                            <div class="col-sm-4">
                                                <span class="label">Boxes Qty</span>
                                                <span class="value" id="view_boxes_qty"></span>
                                            </div>
                                            <div class="col-sm-4" id="grp_loose_pcs">
                                                <span class="label">Loose Pieces</span>
                                                <span class="value" id="view_loose_pcs"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- SECTION: By Pieces -->
                                    <div id="sec_by_piece" class="d-none">
                                        <h6 class="fw-semibold text-dark mb-3"><i class="las la-cube"></i> Quantity
                                            Details</h6>
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <span class="label">Unit Quantity</span>
                                                <span class="value" id="view_u_qty"></span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- RIGHT: Financials & Stock -->
                        <div class="col-md-5">

                            <!-- Stock Summary Card -->
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white fw-bold text-success pt-3">
                                    <i class="las la-warehouse"></i> Stock Summary
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Total Stock Qty</span>
                                        <span class="fs-5 fw-bold text-dark" id="view_total_stock_qty"></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center" id="grp_total_m2">
                                        <span class="text-muted">Total m²</span>
                                        <span class="fs-5 fw-bold text-dark" id="view_total_m2"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing Card -->
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white fw-bold text-primary pt-3">
                                    <i class="las la-tags"></i> Pricing Details
                                </div>
                                <div class="card-body">

                                    <!-- Unit Prices -->
                                    <div class="mb-3">
                                        <span class="label" id="lbl_price_unit">Sale Price</span>
                                        <span class="value text-primary" id="view_price_unit"></span>
                                    </div>
                                    <div class="mb-3">
                                        <span class="label" id="lbl_purch_unit">Purchase Price</span>
                                        <span class="value text-secondary" id="view_purch_unit"></span>
                                    </div>

                                    <hr>

                                    <!-- Totals -->
                                    <div class="mb-2 d-flex justify-content-between">
                                        <span class="fw-semibold">Sale Total:</span>
                                        <span class="fw-bold text-success fs-5" id="view_sale_total"></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-semibold">Purchase Total:</span>
                                        <span class="fw-bold text-danger fs-5" id="view_purch_total"></span>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer bg-white border-top">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">
                        Close
                    </button>
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

            $.ajax({
                url: "/productview/" + productId,
                type: "GET",
                success: function(product) {

                    // --- Basic ---
                    $('#view_item_name').text(product.item_name ?? '-');
                    $('#view_item_code').text(product.item_code ?? '-');
                    $('#view_cat_sub').text((product.category_relation?.name ?? '-') + ' / ' + (product
                        .sub_category_relation?.name ?? '-'));
                    $('#view_brand_model').text((product.brand?.name ?? '-') + ' / ' + (product.model ??
                        '-'));
                    $('#view_barcode').text(product.barcode_path ?? '-');
                    $('#view_hs_code').text(product.hs_code ?? '-');

                    if (product.color) {
                        try {
                            let colors = JSON.parse(product.color);
                            $('#view_color').text(Array.isArray(colors) ? colors.join(', ') : colors);
                        } catch (e) {
                            $('#view_color').text(product.color);
                        }
                    } else {
                        $('#view_color').text('-');
                    }

                    // --- Mode Logic ---
                    let mode = product.size_mode ?? 'by_size';

                    // Reset sections
                    $('#sec_by_size, #sec_packing, #sec_by_piece, #grp_loose_pcs, #grp_total_m2')
                        .addClass('d-none');
                    let modeBadge = $('#view_size_mode_badge');

                    // Common Vars
                    let salePrice = 0;
                    let purchPrice = 0;

                    if (mode === 'by_size') {
                        modeBadge.text('By Size').removeClass('bg-info bg-warning').addClass(
                            'bg-secondary');

                        // Show Sections
                        $('#sec_by_size').removeClass('d-none');
                        $('#sec_packing').removeClass('d-none');
                        $('#grp_total_m2').removeClass('d-none').addClass('d-flex');

                        // Fill Values
                        let dims = (product.height ?? '0') + ' x ' + (product.width ?? '0') + ' cm';
                        $('#view_dimensions').text(dims);

                        let m2Piece = ((product.height * product.width) / 10000).toFixed(4);
                        $('#view_m2_piece').text(m2Piece + ' m²');

                        $('#view_pcs_box').text(product.pieces_per_box ?? 0);
                        $('#view_boxes_qty').text(product.boxes_quantity ?? 0);

                        $('#view_total_m2').text((product.total_m2 ?? 0) + ' m²');
                        $('#view_total_stock_qty').text(product.total_stock_qty ?? 0);

                        // Prices
                        $('#lbl_price_unit').text('Sale Price (per m²)');
                        $('#lbl_purch_unit').text('Purchase Price (per m²)');

                        salePrice = product.price_per_m2;
                        purchPrice = product.purchase_price_per_m2;

                    } else if (mode === 'by_cartons') {
                        modeBadge.text('By Box').removeClass('bg-secondary bg-warning').addClass(
                            'bg-info');

                        // Show Sections
                        $('#sec_packing').removeClass('d-none');
                        $('#grp_loose_pcs').removeClass('d-none'); // Show loose pieces

                        // Fill Values
                        $('#view_pcs_box').text(product.pieces_per_box ?? 0);
                        $('#view_boxes_qty').text(product.boxes_quantity ?? 0);
                        $('#view_loose_pcs').text(product.loose_pieces ?? 0);

                        $('#view_total_stock_qty').text(product.total_stock_qty ?? 0);

                        // Prices
                        $('#lbl_price_unit').text('Sale Price (per Piece)');
                        $('#lbl_purch_unit').text('Purchase Price (per Piece)');

                        salePrice = product.sale_price_per_box;
                        purchPrice = product.purchase_price_per_piece;

                    } else if (mode === 'by_pieces') {
                        modeBadge.text('By Piece').removeClass('bg-secondary bg-info').addClass(
                            'bg-warning text-dark');

                        // Show Sections
                        $('#sec_by_piece').removeClass('d-none');

                        // Fill Values
                        $('#view_u_qty').text(product.piece_quantity ?? 0);
                        $('#view_total_stock_qty').text(product.total_stock_qty ?? 0);

                        // Prices
                        $('#lbl_price_unit').text('Sale Price (per Piece)');
                        $('#lbl_purch_unit').text('Purchase Price (per Piece)');

                        salePrice = product.sale_price_per_box;
                        purchPrice = product.purchase_price_per_piece;
                    }

                    // Set Prices
                    $('#view_price_unit').text('Rs. ' + parseFloat(salePrice || 0).toFixed(2));
                    $('#view_purch_unit').text('Rs. ' + parseFloat(purchPrice || 0).toFixed(2));

                    $('#view_sale_total').text('Rs. ' + parseFloat(product.total_price || 0).toFixed(
                        2));
                    $('#view_purch_total').text('Rs. ' + parseFloat(product.total_purchase_price || 0)
                        .toFixed(2));

                    // Show Image
                    if (product.image) {
                        // If you had an image tag in the new modal, but I didn't verify if I left #view_image.
                        // Checking the HTML I inserted, I realized I removed the image logic from the previous HTML which was inside the table (or logic).
                        // Wait, the previous modal didn't seem to have a big image preview in lines 201-334.
                        // The original code had an image column in the database but the modal (lines 201-334) didn't actually show an image in the view I replaced!
                        // Oh, I see lines 386-388 in original JS: `$('#view_image').attr...`
                        // But `view_image` was NOT in the previous modal HTML I read (lines 201-334).
                        // It might have been there and I missed it? Or it was missing HTML but had JS.
                        // Let's stick to the structure I designed which is data focused. If user needs image, I can add it, but it wasn't explicitly requested in "Required Fix".
                    }

                    $('#productViewModal').modal('show');
                },
                error: function() {
                    Swal.fire('Error', 'Could not fetch product details', 'error');
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
