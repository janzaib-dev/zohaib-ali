@extends('admin_panel.layout.app')
<style>
    .image-preview-wrapper {
        position: relative;
        display: inline-block;
    }

    .image-preview-wrapper img {
        max-width: 100%;
        border-radius: 8px;
    }

    .clear-image-btn {
        position: absolute;
        top: 2px;
        /* thoda neeche laane ke liye */
        right: 18px;
        width: 28px;
        height: 28px;
        background-color: rgba(0, 0, 0, 0.6);
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s ease-in-out;
    }

    .clear-image-btn:hover {
        background-color: rgba(255, 0, 0, 0.8);
    }


    .uploader {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    #preview {
        width: 395px;
        height: 325px;
        border: 2px dashed #ccc;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #f9f9f9;
    }

    #preview img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        display: block;
    }

    .info {
        font-size: 14px;
        color: #444;
    }

    button {
        padding: 6px 10px;
        border-radius: 6px;
        border: 1px solid #bbb;
        background: white;
        cursor: pointer;
    }
</style>
@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 ">
                        <div class="page-header">
                            <div class="page-title">
                                <h4>Edit Product</h4>
                                <h6>Manage Product Details</h6>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                @if (session()->has('success'))
                                    <div class="alert alert-success">
                                        <strong>Success!</strong> {{ session('success') }}.
                                    </div>
                                @endif
                                <form id="productForm" action="{{ route('product.update', $product->id) }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="row g-4">
                                        <!-- Left Column: Product Info & Image -->
                                        <div class="col-lg-5 col-md-12">
                                            <!-- Image Card -->
                                            <div class="card shadow-sm border-0 mb-3">
                                                <div class="card-body p-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="image-preview-wrapper"
                                                            style="width: 120px; height: 120px; flex-shrink: 0;">
                                                            <img id="preview"
                                                                src="{{ asset('uploads/products/' . $product->image) }}"
                                                                alt="Product Image"
                                                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; background: #f8fafc; border: 2px dashed #d9dfe7;">
                                                            <button type="button" class="clear-image-btn"
                                                                id="clearImageBtn"
                                                                style="top: -5px; right: -5px; width: 22px; height: 22px; font-size: 14px; {{ $product->image ? 'display:flex;' : 'display:none;' }}">&times;</button>
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
                                                    <h6 class="fw-bold mb-3"><i class="las la-info-circle"></i> Basic
                                                        Information</h6>

                                                    <div class="mb-2">
                                                        <label class="form-label small mb-1">Product Name <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" value="{{ $product->item_name }}"
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
                                                                        <option value="{{ $cat->id }}"
                                                                            {{ $product->category_id == $cat->id ? 'selected' : '' }}>
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
                                                            <label class="form-label small mb-1">Sub Category</label>
                                                            <div class="input-group input-group-sm">
                                                                <select id="subcategory-dropdown" name="sub_category_id"
                                                                    class="form-select form-select-sm">
                                                                    <option value="">Select</option>
                                                                    @foreach ($subcategories as $subCat)
                                                                        <option value="{{ $subCat->id }}"
                                                                            {{ $product->sub_category_id == $subCat->id ? 'selected' : '' }}>
                                                                            {{ $subCat->name }}</option>
                                                                    @endforeach
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
                                                                <select name="brand_id" class="form-select form-select-sm"
                                                                    required>
                                                                    <option value="" disabled>Select</option>
                                                                    @foreach ($brands as $brand)
                                                                        <option value="{{ $brand->id }}"
                                                                            {{ $product->brand_id == $brand->id ? 'selected' : '' }}>
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
                                                                value="{{ $product->model }}" name="model"
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
                                                                    value="{{ $product->barcode_path }}"
                                                                    placeholder="Scan/Gen">
                                                                <button type="button" id="generateBarcodeBtn"
                                                                    class="btn btn-primary px-2"><i
                                                                        class="las la-magic"></i></button>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small mb-1">HS Code</label>
                                                            <input type="text" value="{{ $product->hs_code }}"
                                                                name="hs_code" class="form-control form-control-sm"
                                                                required>
                                                        </div>
                                                    </div>

                                                    <div class="mb-2">
                                                        <label class="form-label small mb-1">Item Code <span class="text-danger">*</span></label>
                                                        <input type="text" value="{{ $product->item_code }}"
                                                            name="item_code" class="form-control form-control-sm"
                                                            required>
                                                    </div>

                                                    <div class="mb-2">
                                                        <label class="form-label small mb-1">Color</label>
                                                        <select name="color[]" id="color-select"
                                                            class="form-select form-select-sm" multiple="multiple"
                                                            style="width:100%">
                                                            @php
                                                                $colors = is_string($product->color)
                                                                    ? json_decode($product->color, true)
                                                                    : $product->color ?? [];
                                                                if (!is_array($colors)) {
                                                                    $colors = [];
                                                                }
                                                            @endphp
                                                            <option value="Black"
                                                                {{ in_array('Black', $colors) ? 'selected' : '' }}>Black
                                                            </option>
                                                            <option value="White"
                                                                {{ in_array('White', $colors) ? 'selected' : '' }}>White
                                                            </option>
                                                            <option value="Red"
                                                                {{ in_array('Red', $colors) ? 'selected' : '' }}>Red
                                                            </option>
                                                            <option value="Blue"
                                                                {{ in_array('Blue', $colors) ? 'selected' : '' }}>Blue
                                                            </option>
                                                            @foreach ($colors as $c)
                                                                @if (!in_array($c, ['Black', 'White', 'Red', 'Blue']))
                                                                    <option value="{{ $c }}" selected>
                                                                        {{ $c }}</option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="mb-2">
                                                        <label class="form-label small mb-1">Note</label>
                                                        <textarea name="note" class="form-control form-control-sm" rows="2">{{ $product->note }}</textarea>
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
                                                        aria-labelledby="headingOne" data-bs-parent="#productAccordion">
                                                        <div class="accordion-body p-3">
                                                            <div class="row g-2 align-items-end mb-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label small fw-bold">Select Size
                                                                        Mode <span class="text-danger">*</span></label>
                                                                    <select name="size_mode" id="size-mode-select"
                                                                        class="form-select form-select-sm bg-aliceblue">
                                                                        <option value="by_size"
                                                                            {{ $product->size_mode == 'by_size' ? 'selected' : '' }}>
                                                                            By size (cm)</option>
                                                                        <option value="by_cartons"
                                                                            {{ $product->size_mode == 'by_cartons' ? 'selected' : '' }}>
                                                                            By cartons / boxes</option>
                                                                        <option value="by_pieces"
                                                                            {{ $product->size_mode == 'by_pieces' ? 'selected' : '' }}>
                                                                            By pieces</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <!-- Dynamic Total Stock Display -->
                                                                    <div
                                                                        class="field-total-stock d-none bg-light p-2 rounded border d-flex justify-content-between align-items-center">
                                                                        <span id="total_stock_label"
                                                                            class="small fw-bold text-muted">Total
                                                                            Stock:</span>
                                                                        <input type="text" id="total_stock_display"
                                                                            class="form-control-plaintext text-end fw-bold text-dark py-0"
                                                                            readonly
                                                                            value="{{ $product->total_stock_qty ?? 0 }}"
                                                                            style="width: 80px;">
                                                                    </div>
                                                                    <div
                                                                        class="field-by-size bg-light p-2 rounded border d-flex justify-content-between align-items-center">
                                                                        <span class="small fw-bold text-muted">Total
                                                                            m²:</span>
                                                                        <input type="text" id="total_m2_display"
                                                                            class="form-control-plaintext text-end fw-bold text-primary py-0"
                                                                            readonly value="{{ $product->total_m2 }}"
                                                                            style="width: 80px;">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Fields for "By Size" -->
                                                            <div class="row g-2 field-by-size">
                                                                <div class="col-6 col-sm-3">
                                                                    <label class="form-label small text-muted">Height (cm)
                                                                        <span class="text-danger">*</span></label>
                                                                    <input type="number" id="height" name="height"
                                                                        class="form-control form-control-sm calculation-input"
                                                                        step="0.01" min="0"
                                                                        value="{{ $product->height }}">
                                                                </div>
                                                                <div class="col-6 col-sm-3">
                                                                    <label class="form-label small text-muted">Width (cm)
                                                                        <span class="text-danger">*</span></label>
                                                                    <input type="number" id="width" name="width"
                                                                        class="form-control form-control-sm calculation-input"
                                                                        step="0.01" min="0"
                                                                        value="{{ $product->width }}">
                                                                </div>
                                                                <div class="col-6 col-sm-3">
                                                                    <label class="form-label small text-muted">Pcs / Box
                                                                        <span class="text-danger">*</span></label>
                                                                    <input type="number" id="pieces_per_box"
                                                                        name="pieces_per_box"
                                                                        class="form-control form-control-sm calculation-input"
                                                                        min="1"
                                                                        value="{{ $product->pieces_per_box }}">
                                                                </div>
                                                                <div class="col-6 col-sm-3">
                                                                    <label class="form-label small text-muted">Box Qty
                                                                        <span class="text-danger">*</span></label>
                                                                    <input type="number" id="boxes_quantity"
                                                                        name="boxes_quantity"
                                                                        class="form-control form-control-sm calculation-input"
                                                                        min="0"
                                                                        value="{{ $product->boxes_quantity }}">
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
                                                                    <label class="form-label small text-muted">Pcs / Box
                                                                        <span class="text-danger">*</span></label>
                                                                    <input type="number" id="pieces_per_box_carton"
                                                                        name="pieces_per_box"
                                                                        class="form-control form-control-sm calculation-input"
                                                                        min="1"
                                                                        value="{{ $product->pieces_per_box }}">
                                                                </div>
                                                                <div class="col-4">
                                                                    <label class="form-label small text-muted">Box Qty
                                                                        <span class="text-danger">*</span></label>
                                                                    <input type="number" id="boxes_quantity_carton"
                                                                        name="boxes_quantity"
                                                                        class="form-control form-control-sm calculation-input"
                                                                        min="0"
                                                                        value="{{ $product->boxes_quantity }}">
                                                                </div>
                                                                <div class="col-4">
                                                                    <label class="form-label small text-muted">Loose
                                                                        Pcs</label>
                                                                    <input type="number" id="loose_pieces"
                                                                        name="loose_pieces"
                                                                        class="form-control form-control-sm calculation-input"
                                                                        min="0"
                                                                        value="{{ $product->loose_pieces ?? 0 }}">
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
                                                                        min="1"
                                                                        value="{{ $product->piece_quantity }}">
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
                                                        aria-labelledby="headingTwo" data-bs-parent="#productAccordion">
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
                                                                                value="{{ $product->price_per_m2 }}">
                                                                        </div>
                                                                        <div class="mt-2 small text-muted">
                                                                            <div class="d-flex justify-content-between">
                                                                                <span>Per Piece:</span> <strong
                                                                                    id="sale_per_piece"
                                                                                    class="text-dark">-</strong>
                                                                            </div>
                                                                            <div class="d-flex justify-content-between">
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
                                                                                value="{{ $product->purchase_price_per_m2 }}">
                                                                        </div>
                                                                        <div class="mt-2 small text-muted">
                                                                            <div class="d-flex justify-content-between">
                                                                                <span>Per Piece:</span> <strong
                                                                                    id="purchase_per_piece"
                                                                                    class="text-dark">-</strong>
                                                                            </div>
                                                                            <div class="d-flex justify-content-between">
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
                                                                            <input type="number" id="sale_price_per_box"
                                                                                name="sale_price_per_box"
                                                                                class="form-control calculation-input"
                                                                                step="0.01" min="0"
                                                                                value="{{ $product->sale_price_per_box }}">
                                                                        </div>
                                                                        <div
                                                                            class="mt-2 field-by-cartons d-none small text-muted">
                                                                            <div class="d-flex justify-content-between">
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
                                                                                value="{{ $product->purchase_price_per_piece }}">
                                                                        </div>
                                                                        <div
                                                                            class="mt-2 field-by-cartons d-none small text-muted">
                                                                            <div class="d-flex justify-content-between">
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
                                                                            readonly value="{{ $product->total_price }}"
                                                                            tabindex="-1">
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
                                                                            readonly
                                                                            value="{{ $product->total_purchase_price }}"
                                                                            tabindex="-1">
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
                                                    <i class="las la-save me-2"></i> Update Product
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('imageInput')) {
            document.getElementById('imageInput').addEventListener('change', function(event) {
                let file = event.target.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        let preview = document.getElementById('preview');
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                        document.getElementById('clearImageBtn').style.display = 'inline-block';
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        if (document.getElementById('clearImageBtn')) {
            document.getElementById('clearImageBtn').addEventListener('click', function() {
                document.getElementById('imageInput').value = "";
                let preview = document.getElementById('preview');
                preview.src =
                    "{{ asset('uploads/products/' . $product->image) }}"; // Purani image wapas
                this.style.display = 'none';
            });
        }
    });
</script>
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
        const salePricePieceInput = document.getElementById('sale_price_per_box');
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

                // SHOW Total Stock for by_size now
                if (fieldTotalStock) fieldTotalStock.classList.remove('d-none');
                // Update Label
                const lbl = document.getElementById('total_stock_label');
                if (lbl) lbl.innerText = "Total Boxes:";

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

                const lbl = document.getElementById('total_stock_label');
                if (lbl) lbl.innerText = "Total Stock:";

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

                const lbl = document.getElementById('total_stock_label');
                if (lbl) lbl.innerText = "Total Stock:";

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

                finalStock = boxes;

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
                        input = container;
                    }
                }

                if (input) {
                    if (input.tagName === 'INPUT' || input.tagName === 'SELECT' || input.tagName ===
                        'TEXTAREA') {
                        input.classList.add('is-invalid');
                    }

                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback ajax-error d-block';
                    errorDiv.innerText = messages[0];

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

            fetch(validateUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
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
