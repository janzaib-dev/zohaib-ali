@extends('admin_panel.layout.app')

@section('content')
    {{-- CSS: Keeping Bootstrap 5 CSS for the specific form styling but scoped carefully. 
         Ideally, we should rely on theme, but for 'Modern' look requested, we keep custom styles. --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Select2 CSS is fine to keep if we want specific styling, but theme might have it. Keeping for safety of 'glass' look --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css">

    {{-- REMOVED: SweetAlert2 Script (Already in Layout) --}}

    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-bg: #f8fafc;
            --border-color: #e2e8f0;
            --text-muted: #64748b;
            --text-dark: #1e293b;
            --input-bg: #ffffff;
            --radius-md: 12px;
            --radius-lg: 16px;
        }

        /* -------------------------------------------------------------------------
               LAYOUT: 3-Column Split View (Full Height, No Window Scroll)
               ------------------------------------------------------------------------- */
        html,
        body {
            height: 100%;
            overflow: hidden !important;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #fff;
        }

        .body-wrapper {
            padding: 0 !important;
            height: 100vh;
            overflow: hidden;
        }

        .three-col-container {
            display: flex;
            height: calc(100vh - 70px);
            /* Adjust for header */
            width: 100%;
            overflow: hidden;
            margin-top: 0;
            border-top: 1px solid var(--border-color);
        }

        /* Common Panel Styles */
        .panel {
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        .panel-header {
            padding: 20px 24px;
            flex-shrink: 0;
            border-bottom: 1px solid var(--border-color);
            background: #fff;
            z-index: 10;
        }

        .panel-title {
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .panel-content {
            flex-grow: 1;
            overflow-y: auto;
            padding: 24px;
            scrollbar-width: thin;
        }

        /* -------------------------------------------------------------------------
               PANEL 1: Identity (Left)
               ------------------------------------------------------------------------- */
        .panel-identity {
            width: 30%;
            min-width: 320px;
            background: #fff;
            border-right: 1px solid var(--border-color);
        }

        .product-image-uploader {
            width: 62%;
            aspect-ratio: 16/9;
            background: var(--secondary-bg);
            border: 2px dashed #cbd5e1;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            margin-bottom: 20px;
            transition: all 0.2s ease;
            overflow: hidden;
        }

        .product-image-uploader:hover {
            border-color: var(--primary-color);
            background: #eef2ff;
        }

        .product-image-uploader img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* -------------------------------------------------------------------------
               PANEL 2: Measurements (Center)
               ------------------------------------------------------------------------- */
        .panel-specs {
            width: 40%;
            min-width: 350px;
            background: #fdfdfd;
            /* Slight contrast */
            border-right: 1px solid var(--border-color);
        }

        .mode-selector-pill {
            display: flex;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 100px;
            margin-bottom: 0;
        }

        .mode-btn {
            flex: 1;
            text-align: center;
            padding: 8px 12px;
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 0;
        }

        .mode-btn.active {
            background: var(--primary-color) !important;
            color: white !important;
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.3);
        }

        .mode-btn i {
            font-size: 1.1em;
            vertical-align: middle;
            margin-right: 4px;
        }

        .stat-card-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 16px;
            text-align: center;
        }

        .stat-card label {
            display: block;
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .stat-card .value {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-dark);
        }

        /* -------------------------------------------------------------------------
               PANEL 3: Pricing (Right)
               ------------------------------------------------------------------------- */
        .panel-price {
            width: 30%;
            min-width: 300px;
            background: #fff;
            display: flex;
            flex-direction: column;
        }

        .price-display-big {
            background: #1e293b;
            color: #fff;
            border-radius: var(--radius-lg);
            padding: 24px;
            text-align: center;
            margin-bottom: 24px;
            box-shadow: 0 10px 30px -10px rgba(30, 41, 59, 0.3);
        }

        .price-display-big .label {
            opacity: 0.7;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .price-display-big .amount {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.1;
            margin: 8px 0;
        }

        .currency {
            font-size: 1rem;
            font-weight: 400;
            opacity: 0.7;
            vertical-align: super;
        }

        .price-inputs-container {
            background: var(--secondary-bg);
            border-radius: var(--radius-md);
            padding: 20px;
            margin-bottom: 20px;
        }

        .save-btn-container {
            padding: 24px;
            background: #fff;
            border-top: 1px solid var(--border-color);
            margin-top: auto;
        }

        /* Form Elements */
        .glass-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 6px;
            display: block;
        }

        .glass-input {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.9rem;
            width: 100%;
            transition: all 0.2s;
        }

        .glass-input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Mobile Responsive */
        @media (max-width: 991px) {

            html,
            body {
                overflow: auto !important;
                height: auto !important;
            }

            .three-col-container {
                flex-direction: column;
                height: auto;
                overflow: visible;
            }

            .panel-identity,
            .panel-specs,
            .panel-price {
                width: 100%;
                height: auto;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
            }

            .panel-content {
                overflow: visible;
                padding-bottom: 40px;
            }

            .save-btn-container {
                position: sticky;
                bottom: 0;
                z-index: 100;
                box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.05);
            }
        }
    </style>

    <div class="container-fluid p-0">

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between px-4"
            style="height: 70px; border-bottom: 1px solid var(--border-color);">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('product') }}" class="btn btn-light btn-sm rounded-circle shadow-sm"
                    style="width:36px;height:36px;line-height:34px;"><i class="las la-arrow-left"></i></a>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">Create Product</h6>
                    <small class="text-muted" style="font-size: 0.75rem;">New Inventory Item</small>
                </div>
            </div>
        </div>

        <form id="productForm" action="{{ route('store-product') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="three-col-container">

                {{-- ================= PANEL 1: PRODUCT INFO ================= --}}
                <div class="panel panel-identity">
                    <div class="panel-header">
                        <h6 class="panel-title"><i class="las la-tag text-primary"></i> Product Info</h6>
                    </div>
                    <div class="panel-content">

                        {{-- Image --}}
                        <input type="file" id="imageInput" name="image" class="d-none" accept="image/*">
                        <div class="product-image-uploader" onclick="document.getElementById('imageInput').click()">
                            <img id="preview" src="" class="d-none">
                            <div id="uploadPlaceholder" class="text-center">
                                <i class="las la-camera fs-2 text-muted opacity-50"></i>
                                <div class="small fw-semibold mt-2 text-dark">Add Photo</div>
                            </div>
                            <button type="button" id="clearImageBtn"
                                class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 m-2 d-none"
                                style="width:24px;height:24px;padding:0;line-height:22px;">&times;</button>
                        </div>

                        {{-- Basic Details --}}
                        <div class="mb-3">
                            <label class="glass-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="glass-input fw-bold" name="product_name" required
                                placeholder="e.g. Ceramic Tile X">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-7">
                                <label class="glass-label">Barcode</label>
                                <div class="input-group">
                                    <input type="text" class="glass-input form-control border-end-0" id="barcodeInput"
                                        name="barcode_path" style="border-radius: 8px 0 0 8px;">
                                    <button type="button" class="btn btn-light border border-start-0"
                                        id="generateBarcodeBtn" style="border-radius: 0 8px 8px 0;"><i
                                            class="las la-magic"></i></button>
                                </div>
                            </div>
                            <div class="col-5">
                                <label class="glass-label">HS Code</label>
                                <input type="text" class="glass-input" name="hs_code" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="glass-label">Category</label>
                            <div class="d-flex gap-1">
                                <select class="form-select glass-input" id="category-dropdown" name="category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                {{-- Changed data-bs-* to data-* for BS4 compatibility --}}
                                <button type="button" class="btn btn-light border rounded" data-toggle="modal"
                                    data-target="#categoryModal">+</button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="glass-label">Sub Category</label>
                            <div class="d-flex gap-1">
                                <select class="form-select glass-input" id="subcategory-dropdown" name="sub_category_id">
                                    <option value="">Select Subcategory</option>
                                </select>
                                <button type="button" class="btn btn-light border rounded" data-toggle="modal"
                                    data-target="#subcategoryModal">+</button>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="glass-label">Brand</label>
                                <select class="form-select glass-input" name="brand_id" required>
                                    <option value="">Select</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="glass-label">Model</label>
                                <input type="text" class="glass-input" name="model">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="glass-label">Colors</label>
                            <select class="glass-input" name="color[]" id="color-select" multiple="multiple">
                                <option value="Black">Black</option>
                                <option value="White">White</option>
                                <option value="Red">Red</option>
                                <option value="Blue">Blue</option>
                                <option value="Beige">Beige</option>
                            </select>
                        </div>

                    </div>
                </div>

                {{-- ================= PANEL 2: MEASUREMENTS ================= --}}
                <div class="panel panel-specs">
                    <div class="panel-header d-flex justify-content-between align-items-center">
                        <h6 class="panel-title"><i class="las la-ruler-combined text-info"></i> Dimensions</h6>
                    </div>

                    <div class="p-3 bg-white border-bottom">
                        {{-- Mode Selector --}}
                        <div class="mode-selector-pill">
                            <input type="radio" class="btn-check" name="size_mode" id="mode_size" value="by_size"
                                checked>
                            <label class="mode-btn" for="mode_size"><i class="las la-compress-arrows-alt"></i> By
                                Size</label>

                            <input type="radio" class="btn-check" name="size_mode" id="mode_carton"
                                value="by_cartons">
                            <label class="mode-btn" for="mode_carton"><i class="las la-box"></i> By Carton</label>

                            <input type="radio" class="btn-check" name="size_mode" id="mode_piece" value="by_pieces">
                            <label class="mode-btn" for="mode_piece"><i class="las la-puzzle-piece"></i> By Piece</label>
                        </div>
                    </div>

                    <div class="panel-content">

                        {{-- Stats --}}
                        <div class="stat-card-row">
                            <div class="stat-card">
                                <label id="stock_unit_label">Total Boxes</label>
                                <div class="value text-primary" id="total_stock_display">0</div>
                            </div>
                            <div class="stat-card" id="total_m2_card">
                                <label>Total m²</label>
                                <div class="value text-secondary" id="total_m2_display">0.00</div>
                            </div>
                        </div>

                        {{-- By Size Group --}}
                        <div class="group-by-size">

                            {{-- Height / Width --}}
                            <div class="row g-3 mb-3">
                                <div class="col-6" id="div_height">
                                    <label class="glass-label">Height (cm)</label>
                                    <input type="number" class="glass-input fw-bold" name="height" id="height"
                                        step="0.01">
                                </div>
                                <div class="col-6" id="div_width">
                                    <label class="glass-label">Width (cm)</label>
                                    <input type="number" class="glass-input fw-bold" name="width" id="width"
                                        step="0.01">
                                </div>
                            </div>

                            {{-- m2 Info Helper --}}
                            <div id="m2_display_container"
                                class="mb-4 p-2 bg-light border border-dashed rounded text-center"
                                style="font-size: 0.75rem;">
                                <div class="row">
                                    <div class="col-6 border-end">m² / Piece: <strong id="m2_per_piece"
                                            class="text-dark">0</strong></div>
                                    <div class="col-6">m² / Box: <strong id="m2_per_box" class="text-dark">0</strong>
                                    </div>
                                </div>
                            </div>

                            {{-- Packing --}}
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="glass-label">Pieces / Box</label>
                                    <input type="number" class="glass-input fw-bold text-dark"
                                        style="background:#f8fafc;" name="pieces_per_box" id="pieces_per_box">
                                </div>
                                <div class="col-6">
                                    <label class="glass-label">Total Boxes</label>
                                    <input type="number" class="glass-input fw-bold border-primary text-primary"
                                        name="boxes_quantity" id="boxes_quantity">
                                </div>
                            </div>
                        </div>

                        {{-- Carton Extra --}}
                        <div class="group-loose d-none mt-3">
                            <label class="glass-label">Loose Pieces</label>
                            <input type="number" class="glass-input border-warning" name="loose_pieces"
                                id="loose_pieces">
                        </div>

                        {{-- Piece Mode --}}
                        <div class="group-piece-only d-none mt-3">
                            <label class="glass-label">Total Quantity (Pieces)</label>
                            <input type="number" class="glass-input fw-bold border-primary" name="piece_quantity"
                                id="piece_quantity">
                        </div>

                    </div>
                </div>

                {{-- ================= PANEL 3: PRICING ================= --}}
                <div class="panel panel-price">
                    <div class="panel-header">
                        <h6 class="panel-title"><i class="las la-wallet text-success"></i> Financials</h6>
                    </div>

                    <div class="panel-content">

                        {{-- Total Value --}}
                        <div class="price-display-big">
                            <div class="label">Estimated Total Value</div>
                            <div class="amount"><span class="currency">PKR</span> <span
                                    id="sale_total_display">0.00</span></div>
                        </div>

                        {{-- M2 Pricing Inputs --}}
                        <div class="group-price-m2">
                            <div class="price-inputs-container">
                                <h6 class="small fw-bold text-muted text-uppercase mb-3 border-bottom pb-2">Pricing Per SQM
                                </h6>
                                <div class="mb-3">
                                    <label class="glass-label text-success">Sale Price / m²</label>
                                    <input type="number" class="glass-input fw-bold text-success" name="price_per_m2"
                                        id="price_per_m2" step="0.01" placeholder="0.00">
                                </div>
                                <div class="mb-0">
                                    <label class="glass-label text-info">Purchase Price / m²</label>
                                    <input type="number" class="glass-input text-secondary" name="purchase_price_per_m2"
                                        id="purchase_price_per_m2" step="0.01" placeholder="0.00">
                                </div>
                            </div>

                            {{-- Calculated Unit Prices --}}
                            <div id="calc_unit_prices" class="p-3 rounded bg-light border">
                                <h6 class="small fw-bold text-muted text-center mb-2">Calculated Unit Prices</h6>
                                <div class="row text-center g-2" style="font-size: 0.8rem;">
                                    <div class="col-6">
                                        <div class="bg-white p-2 rounded border shadow-sm">
                                            <div class="d-block text-muted">Per Piece</div>
                                            <strong class="text-success" id="calc_sale_piece">0.00</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-white p-2 rounded border shadow-sm">
                                            <div class="d-block text-muted">Per Box</div>
                                            <strong class="text-success" id="calc_sale_box">0.00</strong>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-1">
                                        <small class="text-muted">Purchase: <span id="calc_purch_piece">0</span> /pc |
                                            <span id="calc_purch_box">0</span> /box</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Unit Pricing Inputs --}}
                        <div class="group-price-unit d-none">
                            <div class="price-inputs-container border-warning" style="background:#fffcf2;">
                                <h6 class="small fw-bold text-muted text-uppercase mb-3 border-bottom pb-2">Pricing Per
                                    Unit</h6>
                                <div class="mb-3">
                                    <label class="glass-label text-success">Sale Price (<span
                                            class="unit-label">Piece</span>)</label>
                                    <input type="number" class="glass-input fw-bold text-success"
                                        name="sale_price_per_box" id="sale_price_per_box" step="0.01">
                                </div>
                                <div class="mb-0">
                                    <label class="glass-label text-info">Purchase Price (<span
                                            class="unit-label">Piece</span>)</label>
                                    <input type="number" class="glass-input text-secondary"
                                        name="purchase_price_per_piece" id="purchase_price_per_piece" step="0.01">
                                </div>
                            </div>
                        </div>
    {{-- Save Button --}}
                    <div class="save-btn-container">
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-lg">
                            SAVE PRODUCT
                        </button>
                    </div>
                    </div>

                
                </div>

            </div>
        </form>

        {{-- Modals --}}
        <div id="categoryModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('store.category') }}" method="POST">@csrf <div class="modal-header">
                            <h5 class="modal-title">Add Category</h5><button type="button" class="close"
                                data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body"><input type="hidden" name="page" value="product_page">
                            <div class="mb-3"><label class="form-label">Name</label><input type="text"
                                    name="name" class="form-control" required></div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary w-100">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="subcategoryModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('store.subcategory') }}" method="POST">@csrf <div class="modal-header">
                            <h5 class="modal-title">Add SubCategory</h5><button type="button" class="close"
                                data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body"><input type="hidden" name="page" value="product_page">
                            <div class="mb-3"><label>Category</label><select name="category_id" class="form-select">
                                    @foreach ($categories as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select></div>
                            <div class="mb-3"><label>Name</label><input type="text" name="name"
                                    class="form-control" required></div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary w-100">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    {{-- Scripts: Removed external libs (Select2, SweetAlert2, Bootstrap Bundle) as they are likely in app.blade.php already 
         or interfere with theme's jQuery version. Relying on theme's assets. --}}

    <script>
        // Ensure code runs after jQuery (if loaded by theme)
        document.addEventListener('DOMContentLoaded', function() {
            // --- UI Elements ---
            const form = document.getElementById('productForm');
            const modeRadios = document.querySelectorAll('input[name="size_mode"]');

            // Containers
            const grpBySize = document.querySelector('.group-by-size');
            const grpLoose = document.querySelector('.group-loose');
            const grpPieceOnly = document.querySelector('.group-piece-only');
            const grpPriceM2 = document.querySelector('.group-price-m2');
            const grpPriceUnit = document.querySelector('.group-price-unit');
            const grpCalcUnit = document.getElementById('calc_unit_prices');

            // Elements to toggle in By Carton Mode
            const divHeight = document.getElementById('div_height');
            const divWidth = document.getElementById('div_width');
            const m2Display = document.getElementById('m2_display_container');
            const totalM2Card = document.getElementById('total_m2_card');

            // Labels
            const unitLabels = document.querySelectorAll('.unit-label');
            const stockLabel = document.getElementById('stock_unit_label');

            // --- Logic Update Mode ---
            function updateMode() {
                const mode = document.querySelector('input[name="size_mode"]:checked').value;

                // 1. Highlight Button
                document.querySelectorAll('.mode-btn').forEach(btn => btn.classList.remove('active'));
                const modeMap = {
                    'by_size': 'mode_size',
                    'by_cartons': 'mode_carton',
                    'by_pieces': 'mode_piece'
                };
                const activeLabel = document.querySelector(`label[for="${modeMap[mode]}"]`);
                if (activeLabel) activeLabel.classList.add('active');

                // 2. Hide ALL first
                if (grpBySize) grpBySize.classList.add('d-none');
                if (grpLoose) grpLoose.classList.add('d-none');
                if (grpPieceOnly) grpPieceOnly.classList.add('d-none');
                if (grpPriceM2) grpPriceM2.classList.add('d-none');
                if (grpPriceUnit) grpPriceUnit.classList.add('d-none');
                if (grpCalcUnit) grpCalcUnit.classList.add('d-none');

                // Reset internal visibility of BySize group
                if (divHeight) divHeight.classList.remove('d-none');
                if (divWidth) divWidth.classList.remove('d-none');
                if (m2Display) m2Display.classList.remove('d-none');
                if (totalM2Card) totalM2Card.classList.remove('d-none'); // Default Show

                if (mode === 'by_size') {
                    if (grpBySize) grpBySize.classList.remove('d-none');
                    if (grpPriceM2) grpPriceM2.classList.remove('d-none');
                    if (grpCalcUnit) grpCalcUnit.classList.remove('d-none');

                    if (stockLabel) stockLabel.innerText = "Total Boxes";
                    setRequired(['height', 'width', 'pieces_per_box', 'boxes_quantity', 'price_per_m2',
                        'purchase_price_per_m2'
                    ], true);
                    setRequired(['piece_quantity', 'sale_price_per_box', 'purchase_price_per_piece'], false);

                } else if (mode === 'by_cartons') {
                    // Reuse by-size group for Pcs/Box and Box Qty
                    if (grpBySize) grpBySize.classList.remove('d-none');

                    // STRICTLY HIDE Height, Width, M2 displays
                    if (divHeight) divHeight.classList.add('d-none');
                    if (divWidth) divWidth.classList.add('d-none');
                    if (m2Display) m2Display.classList.add('d-none');
                    if (totalM2Card) totalM2Card.classList.add('d-none'); // HIDE M2 Card

                    if (grpLoose) grpLoose.classList.remove('d-none');
                    if (grpPriceUnit) grpPriceUnit.classList.remove('d-none');

                    unitLabels.forEach(l => l.innerText = "Piece"); // Sale per piece logic
                    if (stockLabel) stockLabel.innerText = "Total Pieces";

                    setRequired(['pieces_per_box', 'boxes_quantity', 'sale_price_per_box',
                        'purchase_price_per_piece'
                    ], true);
                    setRequired(['height', 'width', 'piece_quantity', 'price_per_m2', 'purchase_price_per_m2'],
                        false);

                } else if (mode === 'by_pieces') {
                    if (grpPieceOnly) grpPieceOnly.classList.remove('d-none');
                    if (grpPriceUnit) grpPriceUnit.classList.remove('d-none');

                    // Hide M2 in piece mode too as no measurements
                    if (totalM2Card) totalM2Card.classList.add('d-none');

                    unitLabels.forEach(l => l.innerText = "Piece");
                    if (stockLabel) stockLabel.innerText = "Total Pieces";

                    setRequired(['piece_quantity', 'sale_price_per_box', 'purchase_price_per_piece'], true);
                    // Add m2 prices to false list
                    setRequired(['height', 'width', 'pieces_per_box', 'boxes_quantity', 'price_per_m2',
                        'purchase_price_per_m2'
                    ], false);
                    setRequired(['height', 'width', 'pieces_per_box', 'boxes_quantity'], false);
                }

                calculate();
            }

            // --- Reset Helper ---
            function resetInputs() {
                const idsOrNames = [
                    'height', 'width', 'pieces_per_box', 'boxes_quantity',
                    'loose_pieces', 'piece_quantity',
                    'price_per_m2', 'purchase_price_per_m2',
                    'sale_price_per_box', 'purchase_price_per_piece'
                ];
                idsOrNames.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = '';
                });
                calculate();
            }

            function setRequired(ids, isReq) {
                ids.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        if (isReq) el.setAttribute('required', 'required');
                        else el.removeAttribute('required');
                    }
                });
            }

            function calculate() {
                const mode = document.querySelector('input[name="size_mode"]:checked').value;
                const v = (id) => parseFloat(document.getElementById(id)?.value) || 0;

                let stock = 0;
                let saleVal = 0;

                if (mode === 'by_size') {
                    const h = v('height');
                    const w = v('width');
                    const pcs = v('pieces_per_box');
                    const boxes = v('boxes_quantity');
                    const pSaleM2 = v('price_per_m2');
                    const pPurchM2 = v('purchase_price_per_m2');

                    stock = boxes;

                    const m2Piece = (h * w) / 10000;
                    const m2Box = m2Piece * pcs;
                    const totalM2 = m2Piece * pcs * boxes;
                    saleVal = totalM2 * pSaleM2;

                    setText('m2_per_piece', m2Piece.toFixed(4));
                    setText('m2_per_box', (m2Piece * pcs).toFixed(4));
                    setText('total_m2_display', totalM2.toFixed(3));

                    // Calculate Unit Prices
                    const salePerPiece = m2Piece * pSaleM2;
                    const salePerBox = m2Box * pSaleM2;
                    const purchPerPiece = m2Piece * pPurchM2;
                    const purchPerBox = m2Box * pPurchM2;

                    setText('calc_sale_piece', salePerPiece.toFixed(2));
                    setText('calc_sale_box', salePerBox.toFixed(2));
                    setText('calc_purch_piece', purchPerPiece.toFixed(2));
                    setText('calc_purch_box', purchPerBox.toFixed(2));

                } else if (mode === 'by_cartons') {
                    const pcs = v('pieces_per_box');
                    const boxes = v('boxes_quantity');
                    const loose = v('loose_pieces');
                    const pSale = v('sale_price_per_box');

                    stock = (pcs * boxes) + loose;
                    saleVal = stock * pSale;

                } else if (mode === 'by_pieces') {
                    const qty = v('piece_quantity');
                    const pSale = v('sale_price_per_box');

                    stock = qty;
                    saleVal = qty * pSale;
                }

                setText('total_stock_display', stock);
                setText('sale_total_display', saleVal.toLocaleString(undefined, {
                    minimumFractionDigits: 2
                }));
            }

            function setText(id, val) {
                const el = document.getElementById(id);
                if (el) el.innerText = val;
            }

            // Events
            modeRadios.forEach(r => r.addEventListener('change', function() {
                resetInputs(); // Clear fields on change
                updateMode();
            }));
            form.querySelectorAll('input').forEach(i => i.addEventListener('input', calculate));

            // Init
            updateMode();

            // --- Image Upload ---
            const imgInput = document.getElementById('imageInput');
            const preview = document.getElementById('preview');
            const ph = document.getElementById('uploadPlaceholder');
            const clr = document.getElementById('clearImageBtn');

            imgInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const r = new FileReader();
                    r.onload = (e) => {
                        preview.src = e.target.result;
                        preview.classList.remove('d-none');
                        ph.classList.add('d-none');
                        clr.classList.remove('d-none');
                    };
                    r.readAsDataURL(this.files[0]);
                }
            });

            clr.addEventListener('click', (e) => {
                e.stopPropagation();
                imgInput.value = '';
                preview.classList.add('d-none');
                ph.classList.remove('d-none');
                clr.classList.add('d-none');
            });

            // --- Plugins ---
            $('#category-dropdown').on('change', function() {
                var cid = $(this).val();
                if (cid) {
                    $.get('/get-subcategories/' + cid, function(d) {
                        $('#subcategory-dropdown').empty().append(
                            '<option value="">Select Subcategory</option>');
                        $.each(d, function(_, v) {
                            $('#subcategory-dropdown').append('<option value="' + v.id +
                                '">' + v.name + '</option>');
                        });
                    });
                }
            });
            $('#color-select').select2({
                placeholder: "Select Colors",
                tags: true
            });

            // Barcode
            const barIn = document.getElementById('barcodeInput');
            const barBtn = document.getElementById('generateBarcodeBtn');
            if (!barIn.value) fetch('{{ route('generate-barcode-image') }}').then(r => r.json()).then(d => barIn
                .value = d.barcode_number);
            barBtn.addEventListener('click', () => fetch('{{ route('generate-barcode-image') }}').then(r => r
            .json()).then(d => barIn.value = d.barcode_number));

            // Optimized Single-Step AJAX Submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const btn = form.querySelector('button[type="submit"]');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="las la-spinner la-spin"></i> Saving...';
                btn.disabled = true;

                const formData = new FormData(form);
                const url = form.action;

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(r => r.json().then(data => ({
                        status: r.status,
                        body: data
                    })))
                    .then(({
                        status,
                        body
                    }) => {
                        if (status === 422 || body.status === 'error') {
                            // Validation errors
                            const errorMsg = body.errors ?
                                Object.values(body.errors).flat().join('<br>') :
                                (body.message || 'Validation failed');

                            Swal.fire({
                                icon: 'error',
                                title: 'Please fix the following:',
                                html: '<div class="text-start small">' + errorMsg + '</div>',
                                confirmButtonColor: '#4f46e5'
                            });
                        } else if (status === 200 || body.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: body.message || 'Product created successfully!',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(body.message || 'Unknown error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong. Please try again.',
                            confirmButtonColor: '#4f46e5'
                        });
                    })
                    .finally(() => {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    });
            });
        });
    </script>
@endsection
