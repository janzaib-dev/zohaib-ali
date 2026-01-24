@extends('admin_panel.layout.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')

    <style>
        .image-preview-wrapper {
            position: relative;
            display: inline-block
        }

        .image-preview-wrapper img {
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .06)
        }

        .clear-image-btn {
            position: absolute;
            top: 8px;
            right: 10px;
            width: 30px;
            height: 30px;
            background: rgba(0, 0, 0, .6);
            color: #fff;
            border: none;
            border-radius: 50%;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: .2s
        }

        .clear-image-btn:hover {
            background: rgba(220, 53, 69, .9)
        }

        #preview {
            width: 395px;
            height: 325px;
            border: 2px dashed #d9dfe7;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #f8fafc
        }

        #preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block
        }

        .page-title {
            font-weight: 700;
            letter-spacing: .3px
        }

        .btn-outline--primary {
            border-color: #3b82f6;
            color: #3b82f6
        }

        .btn-outline--primary:hover {
            background: #3b82f6;
            border-color: #3b82f6;
            color: #fff
        }

        .card {
            border-radius: 14px;
            border: 1px solid #eef1f5
        }

        .form-label {
            font-weight: 600
        }

        .select2-container--default .select2-selection--multiple {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            min-height: 38px;
            max-height: 38px;
            white-space: nowrap;
            scrollbar-width: thin
        }

        .select2-selection__choice {
            white-space: nowrap;
            margin-right: 4px;
            font-size: 11px;
            padding: 2px 6px
        }

        .badge-note {
            font-size: .75rem
        }

        .small-help {
            font-size: .8rem;
            color: #6b7280
        }

        .modal-wide {
            max-width: 1100px
        }

        .bom-table thead th,
        .bom-table tbody td {
            vertical-align: middle
        }

        .bom-table input[readonly] {
            background: #f8fafc
        }

        .toolbar-gap>* {
            margin-right: .4rem
        }
    </style>
    <style>
        .add-btn {
            width: 40px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;

            border-left: 0;
        }

        .add-btn i {
            font-size: 13px;
            transition: transform 0.2s ease;
        }

        /* Hover polish */
        .add-btn:hover i {
            transform: scale(1.15);
        }

        /* Focus consistency */
        .category-group .form-select:focus {
            box-shadow: none;
        }

        .add-btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container-fluid">
                <div class="body-wrapper">
                    <div class="bodywrapper__inner">
                        {{-- <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <h6 class="page-title mb-0">Add Product</h6> --}}

                        {{-- <div class="d-flex justify-content-center flex-wrap gap-2 flex-grow-1 toolbar-gap">
              <button type="button" class="btn btn-sm btn-outline--primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                <i class="la la-plus-circle"></i> Add Category
              </button>
              <button type="button" class="btn btn-sm btn-outline--primary" data-bs-toggle="modal" data-bs-target="#subcategoryModal">
                <i class="las la-plus"></i> Add Subcategory
              </button>
              <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn" data-modal_title="Add New Model" data-bs-toggle="modal" data-bs-target="#modelModal">
                <i class="las la-plus"></i> Add Models
              </button>
              <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn" data-modal_title="Add New Brand" data-bs-toggle="modal" data-bs-target="#cuModal">
                <i class="las la-plus"></i> Add Brand
              </button>
              <a class="btn btn-sm btn-outline--primary" href="{{ url('/home') }}">
                <i class="la la-tachometer-alt"></i> Go To Dashboard
              </a>
            </div> --}}
                        @if (session('swal_error'))
                            <script>
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: "{{ session('swal_error') }}"
                                });
                            </script>
                        @elseif(session('catagory_swal_error'))
                            <script>
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: "{{ session('catagory_swal_error') }}"
                                });
                            </script>
                        @elseif(session('subcatagory_swal_error'))
                            <script>
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: "{{ session('catagory_swal_error') }}"
                                });
                            </script>
                        @endif









                        {{-- ////////////////////////////////////// --}}

                        <div class="d-flex">
                            <a href="{{ route('product') }}" class="btn btn-sm btn-outline--primary">
                                <i class="la la-undo"></i> Back
                            </a>
                        </div>
                    </div>

                    <div class="row mb-none-30">
                        <div class="col-lg-12 col-md-12 mb-30">
                            <div class="card">
                                <div class="card-body">
                                    @if (session()->has('success'))
                                        <div class="alert alert-success">
                                            <strong>Success!</strong> {{ session('success') }}.
                                        </div>
                                    @endif

                                    <form id="productForm" action="{{ route('store-product') }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="row g-4">
                                            <!-- Left Column: Product Info & Image -->
                                            <div class="col-lg-5 col-md-12">
                                                <!-- Image Card -->
                                                <div class="card shadow-sm border-0 mb-3">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="image-preview-wrapper"
                                                                style="width: 120px; height: 120px; flex-shrink: 0;">
                                                                <img id="preview" src="" alt="No Img"
                                                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; background: #f8fafc; border: 2px dashed #d9dfe7;">
                                                                <button type="button" class="clear-image-btn"
                                                                    id="clearImageBtn"
                                                                    style="top: -5px; right: -5px; width: 22px; height: 22px; font-size: 14px;">&times;</button>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <label class="form-label mb-1">Product Image</label>
                                                                <input type="file" id="imageInput" name="image"
                                                                    class="form-control form-control-sm">
                                                                <div class="small-help mt-1 text-muted"
                                                                    style="font-size: 0.75rem;">PNG/JPG up to 2MB.</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Basic Info Card -->
                                                <div class="card shadow-sm border-0">
                                                    <div class="card-body">
                                                        <h6 class="fw-bold mb-3"><i class="las la-info-circle"></i>
                                                            Basic Information</h6>

                                                        <div class="mb-2">
                                                            <label class="form-label small mb-1">Item Name <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" value="{{ old('product_name') }}"
                                                                name="product_name" class="form-control form-control-sm"
                                                                required>
                                                        </div>

                                                        <div class="row g-2 mb-2">
                                                            <div class="col-6">
                                                                <label class="form-label small mb-1">Category <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="input-group input-group-sm">
                                                                    <select id="category-dropdown" name="category_id"
                                                                        class="form-select form-select-sm" required>
                                                                        <option value="">Select</option>
                                                                        @foreach ($categories as $cat)
                                                                            <option value="{{ $cat->id }}">
                                                                                {{ $cat->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <button type="button" class="btn btn-primary add-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#categoryModal"><i
                                                                            class="las la-plus"></i></button>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="form-label small mb-1">Sub
                                                                    Category</label>
                                                                <div class="input-group input-group-sm">
                                                                    <select id="subcategory-dropdown" name="sub_category_id"
                                                                        class="form-select form-select-sm">
                                                                        <option value="">Select</option>
                                                                    </select>
                                                                    <button type="button" class="btn btn-primary add-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#subcategoryModal"><i
                                                                            class="las la-plus"></i></button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row g-2 mb-2">
                                                            <div class="col-6">
                                                                <label class="form-label small mb-1">Brand <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="input-group input-group-sm">
                                                                    <select name="brand_id"
                                                                        class="form-select form-select-sm" required>
                                                                        <option value="" disabled selected>Select
                                                                        </option>
                                                                        @foreach ($brands as $brand)
                                                                            <option value="{{ $brand->id }}">
                                                                                {{ $brand->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <button type="button" class="btn btn-primary add-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#brandcategoryModal"><i
                                                                            class="las la-plus"></i></button>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="form-label small mb-1">Model <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" id="model"
                                                                    value="{{ old('model') }}" name="model"
                                                                    class="form-control form-control-sm" required>
                                                            </div>
                                                        </div>

                                                        <div class="row g-2 mb-2">
                                                            <div class="col-6">
                                                                <label class="form-label small mb-1">Barcode</label>
                                                                <div class="input-group input-group-sm">
                                                                    <input type="text" id="barcodeInput"
                                                                        name="barcode_path"
                                                                        class="form-control form-control-sm"
                                                                        placeholder="Scan/Gen">
                                                                    <button type="button" id="generateBarcodeBtn"
                                                                        class="btn btn-primary px-2"><i
                                                                            class="las la-magic"></i></button>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="form-label small mb-1">HS Code</label>
                                                                <input type="text" value="{{ old('hs_code') }}"
                                                                    name="hs_code" class="form-control form-control-sm"
                                                                    required>
                                                            </div>
                                                        </div>

                                                        <div class="mb-2">
                                                            <label class="form-label small mb-1">Color</label>
                                                            <select name="color[]" id="color-select"
                                                                class="form-select form-select-sm" multiple="multiple"
                                                                style="width:100%">
                                                                <option value="Black">Black</option>
                                                                <option value="White">White</option>
                                                                <option value="Red">Red</option>
                                                                <option value="Blue">Blue</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Right Column: Size Mode & Pricing (Accordion) -->
                                            <div class="col-lg-7 col-md-12">

                                                <div class="accordion" id="productAccordion">

                                                    <!-- Section 1: Stock & Size Mode (Always Open Default) -->
                                                    <div class="accordion-item shadow-sm border-0 mb-3 overflow-hidden"
                                                        style="border-radius: 8px;">
                                                        <h2 class="accordion-header" id="headingOne">
                                                            <button class="accordion-button fw-bold text-primary bg-light"
                                                                type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#collapseOne" aria-expanded="true"
                                                                aria-controls="collapseOne">
                                                                <i class="las la-ruler-combined me-2"></i> Size & Stock
                                                                Configuration
                                                            </button>
                                                        </h2>
                                                        <div id="collapseOne" class="accordion-collapse collapse show"
                                                            aria-labelledby="headingOne"
                                                            data-bs-parent="#productAccordion">
                                                            <div class="accordion-body p-3">
                                                                <div class="row g-2 align-items-end mb-3">
                                                                    <div class="col-md-6">
                                                                        <label class="form-label small fw-bold">Select
                                                                            Size Mode <span
                                                                                class="text-danger">*</span></label>
                                                                        <select name="size_mode" id="size-mode-select"
                                                                            class="form-select form-select-sm bg-aliceblue">
                                                                            <option value="by_size">By size (cm)
                                                                            </option>
                                                                            <option value="by_cartons">By cartons /
                                                                                boxes</option>
                                                                            <option value="by_pieces">By pieces
                                                                            </option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <!-- Dynamic Total Stock Display -->
                                                                        <div
                                                                            class="field-total-stock d-none bg-light p-2 rounded border d-flex justify-content-between align-items-center">
                                                                            <span class="small fw-bold text-muted">Total
                                                                                Stock:</span>
                                                                            <input type="text" id="total_stock_display"
                                                                                class="form-control-plaintext text-end fw-bold text-dark py-0"
                                                                                readonly value="0"
                                                                                style="width: 80px;">
                                                                        </div>
                                                                        <div
                                                                            class="field-by-size bg-light p-2 rounded border d-flex justify-content-between align-items-center">
                                                                            <span class="small fw-bold text-muted">Total
                                                                                m²:</span>
                                                                            <input type="text" id="total_m2_display"
                                                                                class="form-control-plaintext text-end fw-bold text-primary py-0"
                                                                                readonly value="0.0000"
                                                                                style="width: 80px;">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Fields for "By Size" -->
                                                                <div class="row g-2 field-by-size">
                                                                    <div class="col-6 col-sm-3">
                                                                        <label class="form-label small text-muted">Height
                                                                            (cm) <span class="text-danger">*</span></label>
                                                                        <input type="number" id="height"
                                                                            name="height"
                                                                            class="form-control form-control-sm calculation-input"
                                                                            step="0.01" min="0" value="0">
                                                                    </div>
                                                                    <div class="col-6 col-sm-3">
                                                                        <label class="form-label small text-muted">Width
                                                                            (cm) <span class="text-danger">*</span></label>
                                                                        <input type="number" id="width"
                                                                            name="width"
                                                                            class="form-control form-control-sm calculation-input"
                                                                            step="0.01" min="0" value="0">
                                                                    </div>
                                                                    <div class="col-6 col-sm-3">
                                                                        <label class="form-label small text-muted">Pcs
                                                                            / Box <span
                                                                                class="text-danger">*</span></label>
                                                                        <input type="number" id="pieces_per_box"
                                                                            name="pieces_per_box"
                                                                            class="form-control form-control-sm calculation-input"
                                                                            min="1">
                                                                    </div>
                                                                    <div class="col-6 col-sm-3">
                                                                        <label class="form-label small text-muted">Box
                                                                            Qty <span class="text-danger">*</span></label>
                                                                        <input type="number" id="boxes_quantity"
                                                                            name="boxes_quantity"
                                                                            class="form-control form-control-sm calculation-input"
                                                                            min="0">
                                                                    </div>

                                                                    <!-- Read-only stats -->
                                                                    <div class="col-12 mt-2">
                                                                        <div class="d-flex text-muted small gap-3">
                                                                            <span>m²/Pc: <strong id="m2_per_piece"
                                                                                    class="text-dark">-</strong></span>
                                                                            <span>m²/Box: <strong id="m2_per_box"
                                                                                    class="text-dark">-</strong></span>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Fields for "By Cartons" -->
                                                                <div class="row g-2 field-by-cartons d-none">
                                                                    <div class="col-4">
                                                                        <label class="form-label small text-muted">Pcs
                                                                            / Box <span
                                                                                class="text-danger">*</span></label>
                                                                        <input type="number" id="pieces_per_box_carton"
                                                                            name="pieces_per_box"
                                                                            class="form-control form-control-sm calculation-input"
                                                                            min="1">
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <label class="form-label small text-muted">Box
                                                                            Qty <span class="text-danger">*</span></label>
                                                                        <input type="number" id="boxes_quantity_carton"
                                                                            name="boxes_quantity"
                                                                            class="form-control form-control-sm calculation-input"
                                                                            min="0">
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <label class="form-label small text-muted">Loose
                                                                            Pcs</label>
                                                                        <input type="number" id="loose_pieces"
                                                                            name="loose_pieces"
                                                                            class="form-control form-control-sm calculation-input"
                                                                            min="0" value="0">
                                                                    </div>
                                                                </div>

                                                                <!-- Fields for "By Pieces" -->
                                                                <div class="row g-2 field-by-pieces d-none">
                                                                    <div class="col-6">
                                                                        <label class="form-label small text-muted">Total
                                                                            Quantity (Units) <span
                                                                                class="text-danger">*</span></label>
                                                                        <input type="number" id="piece_quantity"
                                                                            name="piece_quantity"
                                                                            class="form-control form-control-sm calculation-input"
                                                                            min="1">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Section 2: Sale & Purchase (Accordion) -->
                                                    <div class="accordion-item shadow-sm border-0 mb-3 overflow-hidden"
                                                        style="border-radius: 8px;">
                                                        <h2 class="accordion-header" id="headingTwo">
                                                            <button
                                                                class="accordion-button collapsed fw-bold text-success bg-light"
                                                                type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#collapseTwo" aria-expanded="false"
                                                                aria-controls="collapseTwo">
                                                                <i class="las la-tags me-2"></i> Pricing & Financials
                                                            </button>
                                                        </h2>
                                                        <div id="collapseTwo" class="accordion-collapse collapse"
                                                            aria-labelledby="headingTwo"
                                                            data-bs-parent="#productAccordion">
                                                            <div class="accordion-body p-3">

                                                                <!-- By Size Pricing -->
                                                                <div class="field-by-size">
                                                                    <div class="row g-3">
                                                                        <div class="col-6">
                                                                            <label
                                                                                class="form-label small fw-bold text-success">Sale
                                                                                Price / m² <span
                                                                                    class="text-danger">*</span></label>
                                                                            <div class="input-group input-group-sm">
                                                                                <span
                                                                                    class="input-group-text bg-success text-white">Rs.</span>
                                                                                <input type="number" id="price_per_m2"
                                                                                    name="price_per_m2"
                                                                                    class="form-control calculation-input"
                                                                                    step="0.01" min="0"
                                                                                    value="0">
                                                                            </div>
                                                                            <div class="mt-2 small text-muted">
                                                                                <div
                                                                                    class="d-flex justify-content-between">
                                                                                    <span>Per Piece:</span> <strong
                                                                                        id="sale_per_piece"
                                                                                        class="text-dark">-</strong>
                                                                                </div>
                                                                                <div
                                                                                    class="d-flex justify-content-between">
                                                                                    <span>Per Box:</span> <strong
                                                                                        id="sale_per_box"
                                                                                        class="text-dark">-</strong>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <label
                                                                                class="form-label small fw-bold text-info">Purchase
                                                                                Price / m² <span
                                                                                    class="text-danger">*</span></label>
                                                                            <div class="input-group input-group-sm">
                                                                                <span
                                                                                    class="input-group-text bg-info text-white">Rs.</span>
                                                                                <input type="number"
                                                                                    id="purchase_price_per_m2"
                                                                                    name="purchase_price_per_m2"
                                                                                    class="form-control calculation-input"
                                                                                    step="0.01" min="0"
                                                                                    value="0">
                                                                            </div>
                                                                            <div class="mt-2 small text-muted">
                                                                                <div
                                                                                    class="d-flex justify-content-between">
                                                                                    <span>Per Piece:</span> <strong
                                                                                        id="purchase_per_piece"
                                                                                        class="text-dark">-</strong>
                                                                                </div>
                                                                                <div
                                                                                    class="d-flex justify-content-between">
                                                                                    <span>Per Box:</span> <strong
                                                                                        id="purchase_per_box"
                                                                                        class="text-dark">-</strong>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- By Unit/Carton Pricing -->
                                                                <div class="field-unit-pricing d-none">
                                                                    <div class="row g-3">
                                                                        <div class="col-6">
                                                                            <label
                                                                                class="form-label small fw-bold text-success">Sale
                                                                                Price / Pc <span
                                                                                    class="text-danger">*</span></label>
                                                                            <div class="input-group input-group-sm">
                                                                                <span
                                                                                    class="input-group-text bg-success text-white">Rs.</span>
                                                                                <input type="number"
                                                                                    id="sale_price_per_piece"
                                                                                    name="sale_price_per_piece"
                                                                                    class="form-control calculation-input"
                                                                                    step="0.01" min="0"
                                                                                    value="0">
                                                                            </div>
                                                                            <div
                                                                                class="mt-2 field-by-cartons d-none small text-muted">
                                                                                <div
                                                                                    class="d-flex justify-content-between">
                                                                                    <span>Per Box:</span> <strong
                                                                                        id="u_sale_per_box"
                                                                                        class="text-dark">-</strong>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <label
                                                                                class="form-label small fw-bold text-info">Purchase
                                                                                Price / Pc <span
                                                                                    class="text-danger">*</span></label>
                                                                            <div class="input-group input-group-sm">
                                                                                <span
                                                                                    class="input-group-text bg-info text-white">Rs.</span>
                                                                                <input type="number"
                                                                                    id="purchase_price_per_piece"
                                                                                    name="purchase_price_per_piece"
                                                                                    class="form-control calculation-input"
                                                                                    step="0.01" min="0"
                                                                                    value="0">
                                                                            </div>
                                                                            <div
                                                                                class="mt-2 field-by-cartons d-none small text-muted">
                                                                                <div
                                                                                    class="d-flex justify-content-between">
                                                                                    <span>Per Box:</span> <strong
                                                                                        id="u_purchase_per_box"
                                                                                        class="text-dark">-</strong>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <hr class="my-2">

                                                                <!-- Totals Footer (Always Visible in Accordion) -->
                                                                <div class="row g-2">
                                                                    <div class="col-6">
                                                                        <div class="p-2 border rounded bg-success-subtle">
                                                                            <span
                                                                                class="d-block small text-success fw-bold text-uppercase">Total
                                                                                Sale</span>
                                                                            <input type="text" id="sale_total"
                                                                                class="form-control-plaintext fw-bold  text-success fs-5 p-0"
                                                                                readonly value="0.00" tabindex="-1">
                                                                            <!-- Shared ID for total sale display logic -->
                                                                            <input type="hidden" id="u_sale_total">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <div class="p-2 border rounded bg-info-subtle">
                                                                            <span
                                                                                class="d-block small text-info fw-bold text-uppercase">Total
                                                                                Purchase</span>
                                                                            <input type="text" id="purchase_total"
                                                                                class="form-control-plaintext fw-bold text-info fs-5 p-0"
                                                                                readonly value="0.00" tabindex="-1">
                                                                            <!-- Shared ID for total purchase display logic -->
                                                                            <input type="hidden" id="u_purchase_total">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Sticky Save Button -->
                                                <div class="d-grid mt-3">
                                                    <button type="submit" class="btn btn-primary py-2 fw-bold shadow-sm">
                                                        <i class="las la-save me-2"></i> Save Product
                                                    </button>
                                                </div>

                                            </div>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- bodywrapper__inner end -->
            </div><!-- body-wrapper end -->
        </div>

        {{-- category modal --}}
        <div id="categoryModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><span class="type"></span> <span>Add Category</span></h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <form action="{{ route('store.category') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <input type="hidden" name="page" value="product_page" class="form-control" required>
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn--primary h-45 w-100">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- subcategory modal --}}
        <div id="subcategoryModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><span class="type"></span> <span>Add Subcategory</span></h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <form action="{{ route('store.subcategory') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" id="" name="page"value="product_page" class="form-control"
                                required>
                            <div class="form-group">
                                <label>Category Name</label>
                                <select name="category_id" class="form-select">
                                    @foreach ($categories as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Sub-Category Name</label>
                                <input type="text" id="sub_category" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn--primary h-45 w-100">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- model modal --}}
        <div id="modelModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><span class="type"></span> <span>Add Models</span></h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <form action="{{ route('store.Unit') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="unit" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn--primary h-45 w-100">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- brand modal --}}
        <div id="brandcategoryModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><span class="type"></span> <span>Add Brand</span></h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <form action="{{ route('store.Brand') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page" class="form-control" required>
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn--primary h-45 w-100">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- Pakagetype model --}}
        {{-- <div id="PackageTypeModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><span class="type"></span> <span>Add Package type</span></h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <form action="{{ route('package-type.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page" class="form-control" required>
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary h-45 w-100">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}


    </div>
    </div>

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sizeModeSelect = document.getElementById('size-mode-select');

            // --- INPUTS ---
            const heightInput = document.getElementById('height');
            const widthInput = document.getElementById('width');

            // By Size specific
            const piecesBySize = document.getElementById('pieces_per_box');
            const boxesBySize = document.getElementById('boxes_quantity');
            const pricePerM2Input = document.getElementById('price_per_m2');
            const purchasePerM2Input = document.getElementById('purchase_price_per_m2');

            // By Cartons specific
            const piecesByCarton = document.getElementById('pieces_per_box_carton');
            const boxesByCarton = document.getElementById('boxes_quantity_carton');
            const loosePiecesInput = document.getElementById('loose_pieces');

            // By Pieces specific
            const pieceQuantityInput = document.getElementById('piece_quantity');

            // Shared (Cartons + Pieces) Pricing
            const salePricePieceInput = document.getElementById('sale_price_per_piece');
            const purchasePricePieceInput = document.getElementById('purchase_price_per_piece');

            // --- OUTPUTS ---
            const m2PieceOut = document.getElementById('m2_per_piece');
            const m2BoxOut = document.getElementById('m2_per_box');
            const totalM2Out = document.getElementById('total_m2_display');
            const totalStockOut = document.getElementById('total_stock_display');
            const salePerPieceOut = document.getElementById('sale_per_piece');
            const salePerBoxOut = document.getElementById('sale_per_box');
            const purchPerPieceOut = document.getElementById('purchase_per_piece');
            const purchPerBoxOut = document.getElementById('purchase_per_box');
            const uSalePerBoxOut = document.getElementById('u_sale_per_box');
            const uPurchPerBoxOut = document.getElementById('u_purchase_per_box');
            const saleTotalOut = document.getElementById('sale_total');
            const purchaseTotalOut = document.getElementById('purchase_total');
            const uSaleTotalOut = document.getElementById('u_sale_total');
            const uPurchaseTotalOut = document.getElementById('u_purchase_total');

            // --- GROUPS (Visibility Control) ---
            const fieldBySize = document.querySelectorAll('.field-by-size');
            const fieldByCartons = document.querySelectorAll('.field-by-cartons');
            const fieldByPieces = document.querySelectorAll('.field-by-pieces');

            const fieldUnitPricing = document.querySelector('.field-unit-pricing');
            const fieldTotalStock = document.querySelector('.field-total-stock');
            const fieldPackingHeader = document.querySelector('.field-packing-header');


            function updateVisibility() {
                const mode = sizeModeSelect.value;

                // 1. Hide Everything First
                fieldBySize.forEach(el => toggleGroup(el, false));
                fieldByCartons.forEach(el => toggleGroup(el, false));
                fieldByPieces.forEach(el => toggleGroup(el, false));
                if (fieldUnitPricing) toggleGroup(fieldUnitPricing, false);
                if (fieldTotalStock) fieldTotalStock.classList.add('d-none');
                if (fieldPackingHeader) fieldPackingHeader.classList.add('d-none');

                // 2. Show Based on Mode & Clear Others
                if (mode === 'by_size') {
                    fieldBySize.forEach(el => toggleGroup(el, true));
                    if (fieldPackingHeader) fieldPackingHeader.classList.remove('d-none');

                    setRequired([heightInput, widthInput, piecesBySize, boxesBySize, pricePerM2Input,
                        purchasePerM2Input
                    ], true);
                    setRequired([piecesByCarton, boxesByCarton, pieceQuantityInput, salePricePieceInput,
                        purchasePricePieceInput
                    ], false);

                    clearInputs([piecesByCarton, boxesByCarton, loosePiecesInput, pieceQuantityInput,
                        salePricePieceInput, purchasePricePieceInput
                    ]);

                } else if (mode === 'by_cartons') {
                    fieldByCartons.forEach(el => toggleGroup(el, true));
                    if (fieldUnitPricing) toggleGroup(fieldUnitPricing, true);
                    if (fieldTotalStock) fieldTotalStock.classList.remove('d-none');
                    if (fieldPackingHeader) fieldPackingHeader.classList.remove('d-none');

                    setRequired([piecesByCarton, boxesByCarton, salePricePieceInput, purchasePricePieceInput],
                        true);
                    setRequired([heightInput, widthInput, piecesBySize, boxesBySize, pricePerM2Input,
                        purchasePerM2Input, pieceQuantityInput
                    ], false);

                    clearInputs([heightInput, widthInput, piecesBySize, boxesBySize, pricePerM2Input,
                        purchasePerM2Input, pieceQuantityInput
                    ]);

                } else if (mode === 'by_pieces') {
                    fieldByPieces.forEach(el => toggleGroup(el, true));
                    if (fieldUnitPricing) toggleGroup(fieldUnitPricing, true);
                    if (fieldTotalStock) fieldTotalStock.classList.remove('d-none');

                    setRequired([pieceQuantityInput, salePricePieceInput, purchasePricePieceInput], true);
                    setRequired([heightInput, widthInput, piecesBySize, boxesBySize, pricePerM2Input,
                        purchasePerM2Input, piecesByCarton, boxesByCarton
                    ], false);

                    clearInputs([heightInput, widthInput, piecesBySize, boxesBySize, pricePerM2Input,
                        purchasePerM2Input, piecesByCarton, boxesByCarton, loosePiecesInput
                    ]);
                }

                calculate();
            }

            function toggleGroup(node, show) {
                if (show) {
                    node.classList.remove('d-none');
                    node.querySelectorAll('input, select').forEach(el => el.removeAttribute('disabled'));
                } else {
                    node.classList.add('d-none');
                    node.querySelectorAll('input, select').forEach(el => el.setAttribute('disabled', 'disabled'));
                }
            }

            function setRequired(inputs, required) {
                inputs.forEach(input => {
                    if (input) {
                        if (required) {
                            input.setAttribute('required', 'required');
                        } else {
                            input.removeAttribute('required');
                        }
                    }
                });
            }

            function clearInputs(inputs) {
                inputs.forEach(input => {
                    if (input) input.value = '';
                });
            }

            // Helper to set value safely for both Input and non-Input elements
            function setDisplay(el, val) {
                if (!el) return;
                if (el.tagName === 'INPUT') {
                    el.value = val;
                } else {
                    el.innerText = val;
                }
            }

            function calculate() {
                const mode = sizeModeSelect.value;
                let finalStock = 0;
                let finalSaleTotal = 0;
                let finalPurchTotal = 0;

                if (mode === 'by_size') {
                    const h = parseFloat(heightInput.value) || 0;
                    const w = parseFloat(widthInput.value) || 0;
                    const pcs = parseInt(piecesBySize.value) || 0;
                    const boxes = parseInt(boxesBySize.value) || 0;
                    const sPriceM2 = parseFloat(pricePerM2Input.value) || 0;
                    const pPriceM2 = parseFloat(purchasePerM2Input.value) || 0;

                    // Metrics
                    const m2Piece = (h * w) / 10000;
                    const m2Box = m2Piece * pcs;
                    const totalM2 = m2Box * boxes;

                    finalStock = pcs * boxes;

                    // Sale
                    const sPerPiece = m2Piece * sPriceM2;
                    const sPerBox = m2Box * sPriceM2;
                    finalSaleTotal = totalM2 * sPriceM2;

                    // Purchase
                    const pPerPiece = m2Piece * pPriceM2;
                    const pPerBox = m2Box * pPriceM2;
                    finalPurchTotal = totalM2 * pPriceM2;

                    // UI
                    setDisplay(m2PieceOut, m2Piece > 0 ? m2Piece.toFixed(4) : '');
                    setDisplay(m2BoxOut, m2Box > 0 ? m2Box.toFixed(4) : '');
                    setDisplay(totalM2Out, totalM2 > 0 ? totalM2.toFixed(4) : '');

                    setDisplay(salePerPieceOut, sPerPiece > 0 ? sPerPiece.toFixed(2) : '');
                    setDisplay(salePerBoxOut, sPerBox > 0 ? sPerBox.toFixed(2) : '');

                    setDisplay(purchPerPieceOut, pPerPiece > 0 ? pPerPiece.toFixed(2) : '');
                    setDisplay(purchPerBoxOut, pPerBox > 0 ? pPerBox.toFixed(2) : '');


                } else if (mode === 'by_cartons') {
                    const pcs = parseInt(piecesByCarton.value) || 0;
                    const boxes = parseInt(boxesByCarton.value) || 0;
                    const loose = parseInt(loosePiecesInput.value) || 0;
                    const sPrice = parseFloat(salePricePieceInput.value) || 0;
                    const pPrice = parseFloat(purchasePricePieceInput.value) || 0;

                    finalStock = (pcs * boxes) + loose;

                    const sPerBox = pcs * sPrice;
                    finalSaleTotal = finalStock * sPrice;

                    const pPerBox = pcs * pPrice;
                    finalPurchTotal = finalStock * pPrice;

                    setDisplay(uSalePerBoxOut, sPerBox > 0 ? sPerBox.toFixed(2) : '');
                    setDisplay(uPurchPerBoxOut, pPerBox > 0 ? pPerBox.toFixed(2) : '');


                } else if (mode === 'by_pieces') {
                    const qty = parseInt(pieceQuantityInput.value) || 0;
                    const sPrice = parseFloat(salePricePieceInput.value) || 0;
                    const pPrice = parseFloat(purchasePricePieceInput.value) || 0;

                    finalStock = qty;
                    finalSaleTotal = qty * sPrice;
                    finalPurchTotal = qty * pPrice;

                    setDisplay(uSalePerBoxOut, '');
                    setDisplay(uPurchPerBoxOut, '');
                }

                // Global Updates
                setDisplay(totalStockOut, finalStock);

                setDisplay(saleTotalOut, finalSaleTotal > 0 ? finalSaleTotal.toFixed(2) : '0.00');
                setDisplay(purchaseTotalOut, finalPurchTotal > 0 ? finalPurchTotal.toFixed(2) : '0.00');

                setDisplay(uSaleTotalOut, finalSaleTotal > 0 ? finalSaleTotal.toFixed(2) : '0.00');
                setDisplay(uPurchaseTotalOut, finalPurchTotal > 0 ? finalPurchTotal.toFixed(2) : '0.00');
            }

            // --- LISTENERS ---
            const allInputs = [
                heightInput, widthInput, piecesBySize, boxesBySize, pricePerM2Input, purchasePerM2Input,
                piecesByCarton, boxesByCarton, loosePiecesInput,
                pieceQuantityInput,
                salePricePieceInput, purchasePricePieceInput
            ];

            allInputs.forEach(input => {
                if (input) {
                    input.addEventListener('input', calculate);
                    input.addEventListener('change', calculate);
                }
            });

            if (sizeModeSelect) {
                sizeModeSelect.addEventListener('change', updateVisibility);
            }

            // Init
            setTimeout(updateVisibility, 50);
        });
    </script>




    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('barcodeInput');
            const btn = document.getElementById('generateBarcodeBtn');

            let manualMode = false; // 👈 flag

            // 1️⃣ Auto-generate ONLY on page load
            if (input.value.trim() === '') {
                fetch('{{ route('generate-barcode-image') }}')
                    .then(res => res.json())
                    .then(data => {
                        if (!manualMode) {
                            input.value = data.barcode_number;
                        }
                    });
            }

            // 2️⃣ Button click → clear field & allow manual typing
            btn.addEventListener('click', function() {
                manualMode = true; // ❌ stop auto
                input.value = ''; // clear
                input.focus(); // cursor ready
            });
        });
    </script>



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Barcode generate
            const generateBtn = document.getElementById('generateBarcodeBtn');
            if (generateBtn) {
                generateBtn.addEventListener('click', function() {
                    let barcodeInput = document.getElementById('barcodeInput');
                    if (barcodeInput) {
                        let currentValue = barcodeInput.value.trim();
                        const hit = (url) => fetch(url).then(r => r.json()).then(data => {
                            if (document.getElementById('barcodeInput')) {
                                document.getElementById('barcodeInput').value = data.barcode_number;
                            }
                        });
                        if (currentValue !== "") {
                            hit('/generate-barcode-image?code=' + currentValue);
                        } else {
                            hit('{{ route('generate-barcode-image') }}');
                        }
                    }
                });
            }


            // Image preview/clear
            const imageInput = document.getElementById('imageInput');
            const preview = document.getElementById('preview');
            const clearImageBtn = document.getElementById('clearImageBtn');

            if (imageInput) {
                imageInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = e => {
                            if (preview) preview.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            if (clearImageBtn) {
                clearImageBtn.addEventListener('click', function() {
                    if (preview) preview.src = "";
                    if (imageInput) imageInput.value = "";
                });
            }
        });

        // Dependent Subcategory
        $('#category-dropdown').on('change', function() {
            var categoryId = $(this).val();
            if (categoryId) {
                $.ajax({
                    url: '/get-subcategories/' + categoryId,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('#subcategory-dropdown').empty().append(
                            '<option selected disabled>Select Subcategory</option>');
                        $.each(data, function(_, v) {
                            $('#subcategory-dropdown').append('<option value="' + v.id + '">' +
                                v.name + '</option>');
                        });
                    }
                });
            } else {
                $('#subcategory-dropdown').empty().append('<option value="">Select Subcategory</option>');
            }
        });

        // Color select2
        $(document).ready(function() {
            $('#color-select').select2({
                tags: true,
                placeholder: "Select or type color(s)",
                allowClear: true,
                width: 'resolve'
            });
        });
    </script>
    <script>
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            new bootstrap.Tooltip(el)
        })
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('productForm');
            if (!form) return;

            const validateUrl = "{{ route('product.validate') }}";

            // Helper: Debounce
            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            // Helper: Show Errors
            function showErrors(errors) {
                // Remove existing errors
                document.querySelectorAll('.invalid-feedback.ajax-error').forEach(el => el.remove());
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

                for (const [fieldName, messages] of Object.entries(errors)) {
                    // Try to find input (handles arrays like color[])
                    let input = form.querySelector(`[name="${fieldName}"]`) ||
                        form.querySelector(`[name="${fieldName}[]"]`);

                    // Special handle for Select2 (target the sibling container)
                    if (input && input.classList.contains('select2-hidden-accessible')) {
                        const container = input.nextElementSibling;
                        if (container && container.classList.contains('select2-container')) {
                            // We can't add is-invalid to container easily, but we can append error after it
                            input = container;
                        }
                    }

                    if (input) {
                        // Only add is-invalid to actual inputs, not select2 containers (unless styled)
                        if (input.tagName === 'INPUT' || input.tagName === 'SELECT' || input.tagName ===
                            'TEXTAREA') {
                            input.classList.add('is-invalid');
                        }

                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback ajax-error d-block'; // d-block to force show
                        errorDiv.innerText = messages[0];

                        // Placement logic
                        if (input.closest('.input-group')) {
                            input.closest('.input-group').after(errorDiv);
                        } else {
                            input.after(errorDiv);
                        }
                    }
                }
            }

            // Function to perform validation
            function validateData(isSubmit = false) {
                const formData = new FormData(form);

                // Add flag to tell backend we want JSON (though headers should handle it)
                // fetch automatically sets Content-Type to multipart/form-data with bounds for FormData

                fetch(validateUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            // CSRF token is in body (_token)
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'error') {
                            showErrors(data.errors);
                            if (isSubmit) {
                                const firstError = document.querySelector('.is-invalid') || document
                                    .querySelector('.ajax-error');
                                if (firstError) firstError.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                            }
                        } else {
                            // Clear errors
                            document.querySelectorAll('.invalid-feedback.ajax-error').forEach(el => el
                        .remove());
                            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove(
                                'is-invalid'));

                            if (isSubmit) {
                                form.submit();
                            }
                        }
                    })
                    .catch(err => console.error('Validation error:', err));
            }

            // Attach listeners
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(el => {
                // Skip hidden inputs to avoid excessive triggers (unless necessary)
                if (el.type !== 'hidden') {
                    el.addEventListener('input', debounce(() => validateData(false), 500));
                    el.addEventListener('change', debounce(() => validateData(false), 500));
                }
            });

            // Handle Select2 Change Events (jQuery)
            if (window.jQuery) {
                $(form).find('select').on('change', debounce(() => validateData(false), 500));
            }

            // Submit Handler
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                validateData(true);
            });
        });
    </script>
@endsection
