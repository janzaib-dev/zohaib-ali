@extends('admin_panel.layout.app')

@section('content')
    {{-- 
        SUCCESS: Horizontal Layout Redesign
        Features: 
        - Top Section: Identity (Image + Details side-by-side)
        - Middle Section: Measurements & Stock
        - Bottom Section: Financials & Action
    --}}
    
    {{-- External Resources --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-light: #eef2ff;
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --radius-md: 10px;
            --radius-lg: 16px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            padding-bottom: 40px;
        }

        .page-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* --- Global Cards --- */
        .section-card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.06);
            border: 1px solid var(--border-color);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .card-header-pro {
            padding: 16px 24px;
            border-bottom: 1px solid var(--border-color);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title-pro {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .card-body-pro {
            padding: 24px;
        }

        /* --- Form Styling --- */
        .form-label-pro {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 6px;
            letter-spacing: 0.02em;
        }

        .form-control-pro {
            display: block;
            width: 100%;
            padding: 10px 14px;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-main);
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control-pro:focus {
            border-color: var(--primary);
            outline: 0;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-select-pro {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }

        /* --- Section 1: Identity Grid --- */
        .identity-wrapper {
            display: flex;
            gap: 24px;
        }
        
        .image-section {
            width: 280px;
            flex-shrink: 0;
        }

        .details-section {
            flex: 1;
        }

        .img-uploader {
            width: 100%;
            aspect-ratio: 1/1; /* Square for product */
            border: 2px dashed #cbd5e1;
            border-radius: var(--radius-lg);
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.2s;
        }

        .img-uploader:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .img-uploader img {
            width: 100%;
            height: 100%;
            object-fit: contain; /* Show full product */
            padding: 10px;
        }

        /* --- Section 2: Specs --- */
        .specs-grid {
            display: grid;
            grid-template-columns: 250px 1fr 300px;
            gap: 24px;
            align-items: start;
        }

        /* Mode Switcher Vertical */
        .mode-switcher-vertical {
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: #f8fafc;
            padding: 12px;
            border-radius: var(--radius-md);
        }
        .mode-btn-v {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid transparent;
        }
        .mode-btn-v:hover { background: #fff; }
        .mode-btn-v.active {
            background: #fff;
            color: var(--primary);
            border-color: var(--border-color);
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .mode-btn-v i { font-size: 1.2rem; }

        /* Stats Box */
        .stats-summary-box {
            background: #f8fafc;
            border-radius: var(--radius-md);
            padding: 20px;
            border: 1px solid var(--border-color);
        }
        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .stat-item:last-child { margin-bottom: 0; padding-bottom: 0; border: none; }
        .stat-label { font-size: 0.85rem; color: var(--text-muted); }
        .stat-value { font-size: 1.1rem; font-weight: 700; color: var(--text-main); }


        /* --- Section 3: Financials --- */
        .financials-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 300px; /* Split inputs, calcs, and total */
            gap: 24px;
        }

        .total-value-display {
            background: #0f172a;
            color: #fff;
            padding: 24px;
            border-radius: var(--radius-lg);
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .btn-save-floating {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: var(--primary);
            color: white;
            padding: 16px 32px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            border: none;
            box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.5);
            z-index: 100;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-save-floating:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(79, 70, 229, 0.6);
            background: var(--primary-hover);
            color: #fff;
        }

        /* --- Responsive --- */
        @media (max-width: 991px) {
            .identity-wrapper { flex-direction: column; }
            .image-section { width: 100%; }
            .img-uploader { aspect-ratio: 16/9; }
            .specs-grid { grid-template-columns: 1fr; }
            .financials-grid { grid-template-columns: 1fr; }
            .mode-switcher-vertical { flex-direction: row; overflow-x: auto; }
            .btn-save-floating { width: calc(100% - 48px); justify-content: center; text-align: center; }
        }
    </style>

    <div class="page-container">
        
        {{-- Page Title --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('product') }}" class="btn btn-white border shadow-sm rounded-circle p-0" style="width: 40px; height: 40px; display: grid; place-items: center;">
                    <i class="las la-arrow-left"></i>
                </a>
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Create Product</h4>
                    <small class="text-muted">Add new item to inventory system</small>
                </div>
            </div>
        </div>

        <form id="productForm" action="{{ route('store-product') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- SECTION 1: IDENTITY --}}
            <div class="section-card">
                <div class="card-header-pro">
                    <h5 class="card-title-pro"><i class="las la-tag text-primary"></i> Product Identity</h5>
                </div>
                <div class="card-body-pro">
                    <div class="identity-wrapper">
                        {{-- Image (Left) --}}
                        <div class="image-section">
                            <input type="file" id="imageInput" name="image" class="d-none" accept="image/*">
                            <div class="img-uploader" onclick="document.getElementById('imageInput').click()">
                                <button type="button" id="clearImageBtn" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 d-none rounded-circle" style="width:24px;height:24px;padding:0;z-index: 10;">&times;</button>
                                <img id="preview" class="d-none">
                                <div id="uploadPlaceholder" class="text-center">
                                    <div class="bg-white p-3 rounded-circle shadow-sm d-inline-block mb-3">
                                        <i class="las la-camera fs-1 text-primary"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Upload Image</h6>
                                    <small class="text-muted">Click to browse</small>
                                </div>
                            </div>
                        </div>

                        {{-- Details (Right) --}}
                        <div class="details-section">
                            <div class="row g-3">
                                {{-- Row 1: Name, Item Code & Barcode --}}
                                <div class="col-md-6">
                                    <label class="form-label-pro">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control-pro fs-6 fw-bold" name="product_name" required placeholder="e.g. Ceramic Floor Tile 60x60">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label-pro">Item Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control-pro" name="item_code" required placeholder="e.g. ITEM-001">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label-pro">Barcode Auto-Gen</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control-pro" id="barcodeInput" name="barcode_path">
                                        <button type="button" class="btn btn-light border" id="generateBarcodeBtn"><i class="las la-magic"></i></button>
                                    </div>
                                </div>

                                {{-- Row 2: Categorization --}}
                                <div class="col-md-3">
                                    <label class="form-label-pro">Category <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-1">
                                        <select class="form-select form-control-pro form-select-pro" id="category-dropdown" name="category_id" required>
                                            <option value="">Select...</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-light border px-2" data-toggle="modal" data-target="#categoryModal">+</button>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label-pro">Sub Category</label>
                                    <div class="d-flex gap-1">
                                        <select class="form-select form-control-pro form-select-pro" id="subcategory-dropdown" name="sub_category_id">
                                            <option value="">Select...</option>
                                        </select>
                                        <button type="button" class="btn btn-light border px-2" data-toggle="modal" data-target="#subcategoryModal">+</button>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label-pro">Brand</label>
                                    <select class="form-select form-control-pro form-select-pro" name="brand_id" required>
                                        <option value="">Select...</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label-pro">Model / Series</label>
                                    <input type="text" class="form-control-pro" name="model" placeholder="Optional">
                                </div>

                                {{-- Row 3: Colors & HS --}}
                                <div class="col-md-8">
                                    <label class="form-label-pro">Colors</label>
                                    <select class="form-control-pro" name="color[]" id="color-select" multiple="multiple" style="width: 100%">
                                        <option value="Black">Black</option>
                                        <option value="White">White</option>
                                        <option value="Red">Red</option>
                                        <option value="Blue">Blue</option>
                                        <option value="Beige">Beige</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-pro">HS Code</label>
                                    <input type="text" class="form-control-pro" name="hs_code" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: MEASUREMENTS & STOCK --}}
            <div class="section-card">
                <div class="card-header-pro">
                    <h5 class="card-title-pro"><i class="las la-ruler-combined text-info"></i> Dimensions & Stock</h5>
                </div>
                <div class="card-body-pro">
                    <div class="specs-grid">
                        
                        {{-- Col 1: Mode Switcher (Left Nav Style) --}}
                        <div class="mode-switcher-vertical">
                            <input type="radio" class="d-none" name="size_mode" id="mode_size" value="by_size" checked>
                            <label class="mode-btn-v active" for="mode_size" onclick="selectMode(this)">
                                <i class="las la-compress-arrows-alt"></i>
                                <div>
                                    <div class="fw-bold">By Size</div>
                                    <small class="text-muted d-block" style="font-size: 0.7em;">Tiles, Flooring</small>
                                </div>
                            </label>

                            <input type="radio" class="d-none" name="size_mode" id="mode_carton" value="by_cartons">
                            <label class="mode-btn-v" for="mode_carton" onclick="selectMode(this)">
                                <i class="las la-box"></i>
                                <div>
                                    <div class="fw-bold">By Carton</div>
                                    <small class="text-muted d-block" style="font-size: 0.7em;">Boxed Items</small>
                                </div>
                            </label>

                            <input type="radio" class="d-none" name="size_mode" id="mode_piece" value="by_pieces">
                            <label class="mode-btn-v" for="mode_piece" onclick="selectMode(this)">
                                <i class="las la-puzzle-piece"></i>
                                <div>
                                    <div class="fw-bold">By Piece</div>
                                    <small class="text-muted d-block" style="font-size: 0.7em;">Single Units</small>
                                </div>
                            </label>
                        </div>

                        {{-- Col 2: Inputs (Dynamic) --}}
                        <div class="specs-inputs">
                            
                            {{-- By Size Inputs --}}
                            <div class="group-by-size">
                                <div class="row g-3 mb-4">
                                    <div class="col-6" id="div_height">
                                        <label class="form-label-pro">Height (cm)</label>
                                        <input type="number" class="form-control-pro" name="height" id="height" step="0.01" placeholder="0">
                                    </div>
                                    <div class="col-6" id="div_width">
                                        <label class="form-label-pro">Width (cm)</label>
                                        <input type="number" class="form-control-pro" name="width" id="width" step="0.01" placeholder="0">
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label-pro">Pcs / Box</label>
                                        <input type="number" class="form-control-pro bg-light" name="pieces_per_box" id="pieces_per_box" placeholder="0">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-pro text-primary">In-Stock Boxes</label>
                                        <input type="number" class="form-control-pro border-primary text-primary fw-bold" name="boxes_quantity" id="boxes_quantity" placeholder="0">
                                    </div>
                                </div>
                            </div>

                            {{-- Extra / Loose --}}
                            <div class="group-loose d-none mt-3">
                                <label class="form-label-pro text-warning">Loose Pieces (Extra)</label>
                                <input type="number" class="form-control-pro border-warning" name="loose_pieces" id="loose_pieces">
                            </div>

                            {{-- Piece Only --}}
                            <div class="group-piece-only d-none mt-3">
                                <label class="form-label-pro text-primary">Total Quantity (Heading)</label>
                                <input type="number" class="form-control-pro border-primary text-primary fw-bold fs-5" name="piece_quantity" id="piece_quantity" placeholder="0">
                            </div>
                        </div>

                        {{-- Col 3: Calculated Stats --}}
                        <div class="stats-summary-box">
                            <h6 class="text-uppercase text-muted fw-bold mb-3 small">Stock Summary</h6>
                            
                            <div class="stat-item">
                                <span class="stat-label" id="stock_unit_label">Total Boxes</span>
                                <span class="stat-value" id="total_stock_display">0</span>
                            </div>

                            <div class="stat-item" id="total_m2_card">
                                <span class="stat-label">Total Area</span>
                                <div>
                                    <span class="stat-value text-info" id="total_m2_display">0.00</span>
                                    <small class="text-muted ms-1">m²</small>
                                </div>
                            </div>

                            <div id="m2_display_container" class="mt-3 pt-3 border-top">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">m² per Piece:</small>
                                    <small class="fw-bold" id="m2_per_piece">0</small>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">m² per Box:</small>
                                    <small class="fw-bold" id="m2_per_box">0</small>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- SECTION 3: FINANCIALS --}}
            <div class="section-card">
                <div class="card-header-pro">
                    <h5 class="card-title-pro"><i class="las la-wallet text-success"></i> Pricing & Value</h5>
                </div>
                <div class="card-body-pro">
                    <div class="financials-grid">

                        {{-- Col 1: Inputs --}}
                        <div class="pricing-inputs">
                            <div class="group-price-m2">
                                <h6 class="form-label-pro text-primary mb-3">Rate per SQM (M²)</h6>
                                <div class="mb-3">
                                    <label class="form-label-pro text-success">Sale Price</label>
                                    <input type="number" class="form-control-pro fw-bold text-success" name="price_per_m2" id="price_per_m2" step="0.01">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label-pro text-secondary">Purchase Price</label>
                                    <input type="number" class="form-control-pro text-muted" name="purchase_price_per_m2" id="purchase_price_per_m2" step="0.01">
                                </div>
                            </div>

                            <div class="group-price-unit d-none">
                                <h6 class="form-label-pro text-primary mb-3">Rate per Unit</h6>
                                <div class="mb-3">
                                    <label class="form-label-pro text-success">Sale Price <span class="unit-label text-muted fw-normal">(pc)</span></label>
                                    <input type="number" class="form-control-pro fw-bold text-success" name="sale_price_per_box" id="sale_price_per_box" step="0.01">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label-pro text-secondary">Purchase Price <span class="unit-label text-muted fw-normal">(pc)</span></label>
                                    <input type="number" class="form-control-pro text-muted" name="purchase_price_per_piece" id="purchase_price_per_piece" step="0.01">
                                </div>
                            </div>
                        </div>

                        {{-- Col 2: Info (Calculated) --}}
                        <div class="calculated-info" id="calc_unit_prices">
                            <h6 class="form-label-pro text-primary mb-3">Calculated Unit Prices</h6>
                            
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-light text-center">
                                        <small class="d-block text-muted">Sale / Pc</small>
                                        <strong class="text-success" id="calc_sale_piece">0.00</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-light text-center">
                                        <small class="d-block text-muted">Sale / Box</small>
                                        <strong class="text-success" id="calc_sale_box">0.00</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-light text-center">
                                        <small class="d-block text-muted">Buy / Pc</small>
                                        <span class="text-dark" id="calc_purch_piece">0.00</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-light text-center">
                                        <small class="d-block text-muted">Buy / Box</small>
                                        <span class="text-dark" id="calc_purch_box">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Spacer Col (Empty) --}}
                        <div class="d-none d-lg-block"></div>

                        {{-- Col 3: Grand Total --}}
                        <div class="total-section">
                            <div class="total-value-display">
                                <small class="text-uppercase opacity-75 letter-spacing-1 mb-1">Estimated Stock Value</small>
                                <div>
                                    <span class="fs-5 opacity-75">PKR</span>
                                    <span class="display-5 fw-bold" id="sale_total_display">0.00</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Floating Save Button --}}
            <button type="submit" class="btn-save-floating">
                <i class="las la-check-circle fs-4"></i>
                <span>SAVE PRODUCT</span>
            </button>
        </form>

        {{-- Modals --}}
        {{-- Modals --}}
        <div id="categoryModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-md);">
                    <form action="{{ route('store.category') }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h6 class="modal-title fw-bold">New Category</h6>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="mb-3">
                                <label class="form-label-pro">Category Name</label>
                                <input type="text" name="name" class="form-control-pro" required placeholder="e.g. Ceramics">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill">Create Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="subcategoryModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-md);">
                    <form action="{{ route('store.subcategory') }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h6 class="modal-title fw-bold">New Subcategory</h6>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="mb-3">
                                <label class="form-label-pro">Parent Category</label>
                                <select name="category_id" class="form-select form-control-pro">
                                    @foreach ($categories as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-pro">Name</label>
                                <input type="text" name="name" class="form-control-pro" required placeholder="e.g. Floor Tiles">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill">Create Subcategory</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('js')
    <script>
        function selectMode(labelEl) {
            document.querySelectorAll('.mode-btn-v').forEach(btn => btn.classList.remove('active'));
            labelEl.classList.add('active');
        }

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
                const modeEl = document.querySelector('input[name="size_mode"]:checked');
                if(!modeEl) return;
                const mode = modeEl.value;

                // Sync UI Button
                document.querySelectorAll('.mode-btn-v').forEach(btn => btn.classList.remove('active'));
                const labelFor = document.querySelector(`label[for="${modeEl.id}"]`);
                if(labelFor) labelFor.classList.add('active');

                // Hide ALL
                if (grpBySize) grpBySize.classList.add('d-none');
                if (grpLoose) grpLoose.classList.add('d-none');
                if (grpPieceOnly) grpPieceOnly.classList.add('d-none');
                if (grpPriceM2) grpPriceM2.classList.add('d-none');
                if (grpPriceUnit) grpPriceUnit.classList.add('d-none');
                if (grpCalcUnit) grpCalcUnit.classList.add('d-none');

                // Reset internal visibility
                if (divHeight) divHeight.classList.remove('d-none');
                if (divWidth) divWidth.classList.remove('d-none');
                if (m2Display) m2Display.classList.remove('d-none');
                if (totalM2Card) totalM2Card.classList.remove('d-none'); 

                if (mode === 'by_size') {
                    if (grpBySize) grpBySize.classList.remove('d-none');
                    if (grpPriceM2) grpPriceM2.classList.remove('d-none');
                    if (grpCalcUnit) grpCalcUnit.classList.remove('d-none');

                    if (stockLabel) stockLabel.innerText = "Total Boxes";
                    setRequired(['height', 'width', 'pieces_per_box', 'boxes_quantity', 'price_per_m2', 'purchase_price_per_m2'], true);
                    setRequired(['piece_quantity', 'sale_price_per_box', 'purchase_price_per_piece'], false);

                } else if (mode === 'by_cartons') {
                    if (grpBySize) grpBySize.classList.remove('d-none');
                    if (divHeight) divHeight.classList.add('d-none');
                    if (divWidth) divWidth.classList.add('d-none');
                    if (m2Display) m2Display.classList.add('d-none');
                    if (totalM2Card) totalM2Card.classList.add('d-none');

                    if (grpLoose) grpLoose.classList.remove('d-none');
                    if (grpPriceUnit) grpPriceUnit.classList.remove('d-none');

                    unitLabels.forEach(l => l.innerText = "(pc)");
                    if (stockLabel) stockLabel.innerText = "Total Pieces";

                    setRequired(['pieces_per_box', 'boxes_quantity', 'sale_price_per_box', 'purchase_price_per_piece'], true);
                    setRequired(['height', 'width', 'piece_quantity', 'price_per_m2', 'purchase_price_per_m2'], false);

                } else if (mode === 'by_pieces') {
                    if (grpPieceOnly) grpPieceOnly.classList.remove('d-none');
                    if (grpPriceUnit) grpPriceUnit.classList.remove('d-none');
                    if (totalM2Card) totalM2Card.classList.add('d-none');

                    unitLabels.forEach(l => l.innerText = "(pc)");
                    if (stockLabel) stockLabel.innerText = "Total Pieces";

                    setRequired(['piece_quantity', 'sale_price_per_box', 'purchase_price_per_piece'], true);
                    setRequired(['height', 'width', 'pieces_per_box', 'boxes_quantity', 'price_per_m2', 'purchase_price_per_m2'], false);
                }

                calculate();
            }

            function resetInputs() {
                const idsOrNames = ['height', 'width', 'pieces_per_box', 'boxes_quantity', 'loose_pieces', 'piece_quantity', 'price_per_m2', 'purchase_price_per_m2', 'sale_price_per_box', 'purchase_price_per_piece'];
                idsOrNames.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = '';
                });
                calculate();
            }

            function setRequired(ids, isReq) {
                ids.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) isReq ? el.setAttribute('required', 'required') : el.removeAttribute('required');
                });
            }

            function calculate() {
                const modeEl = document.querySelector('input[name="size_mode"]:checked');
                if(!modeEl) return;
                const mode = modeEl.value;

                const v = (id) => parseFloat(document.getElementById(id)?.value) || 0;
                let stock = 0;
                let saleVal = 0;

                if (mode === 'by_size') {
                    const h = v('height');
                    const w = v('width');
                    const pcs = v('pieces_per_box');
                    const boxes = v('boxes_quantity');
                    const pSaleM2 = v('price_per_m2');
                    
                    stock = boxes;

                    const m2Piece = (h * w) / 10000;
                    const m2Box = m2Piece * pcs;
                    const totalM2 = m2Piece * pcs * boxes;
                    saleVal = totalM2 * pSaleM2;

                    setText('m2_per_piece', m2Piece.toFixed(4));
                    setText('m2_per_box', m2Box.toFixed(4));
                    setText('total_m2_display', totalM2.toFixed(3));
                    
                    setText('calc_sale_piece', (m2Piece * pSaleM2).toFixed(2));
                    setText('calc_sale_box', (m2Box * pSaleM2).toFixed(2));
                    setText('calc_purch_piece', (m2Piece * v('purchase_price_per_m2')).toFixed(2));
                    setText('calc_purch_box', (m2Box * v('purchase_price_per_m2')).toFixed(2));

                } else if (mode === 'by_cartons') {
                    stock = (v('pieces_per_box') * v('boxes_quantity')) + v('loose_pieces');
                    saleVal = stock * v('sale_price_per_box');

                } else if (mode === 'by_pieces') {
                    stock = v('piece_quantity');
                    saleVal = stock * v('sale_price_per_box');
                }

                setText('total_stock_display', stock);
                setText('sale_total_display', saleVal.toLocaleString(undefined, { minimumFractionDigits: 2 }));
            }

            function setText(id, val) {
                const el = document.getElementById(id);
                if (el) el.innerText = val;
            }

            // Events
            modeRadios.forEach(r => r.addEventListener('change', function() {
                resetInputs(); 
                updateMode();
            }));
            form.querySelectorAll('input').forEach(i => i.addEventListener('input', calculate));

            updateMode();

            // Image Handler
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

            // AJAX Submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = document.querySelector('.btn-save-floating');
                const originalContent = btn.innerHTML;
                btn.innerHTML = '<i class="las la-spinner la-spin"></i> Saving...';
                btn.disabled = true;

                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
                    body: formData
                })
                .then(r => r.json().then(data => ({status: r.status, body: data})))
                .then(({status, body}) => {
                    if (status === 200 || body.status === 'success') {
                         Swal.fire({
                            icon: 'success', title: 'Saved!',
                            text: 'Product created successfully', timer: 1500, showConfirmButton: false
                        }).then(() => window.location.reload());
                    } else {
                        const msg = body.errors ? Object.values(body.errors).flat().join('<br>') : (body.message || 'Error');
                        Swal.fire({icon: 'error', title: 'Error', html: msg});
                    }
                })
                .catch(err => Swal.fire({icon: 'error', title: 'Error', text: 'Server Error'}))
                .finally(() => {
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                });
            });

            // Barcode
            const barIn = document.getElementById('barcodeInput');
            const barBtn = document.getElementById('generateBarcodeBtn');
            const barcodeUrl = '{{ route('generate-barcode-image') }}';
            
            if (!barIn.value) fetch(barcodeUrl).then(r => r.json()).then(d => barIn.value = d.barcode_number);
            barBtn.addEventListener('click', () => fetch(barcodeUrl).then(r => r.json()).then(d => barIn.value = d.barcode_number));

            // Select2
             $('#color-select').select2({ placeholder: "Select Colors", tags: true });
             $('#category-dropdown').on('change', function() {
                var cid = $(this).val();
                if (cid) {
                    $.get('/get-subcategories/' + cid, function(d) {
                        $('#subcategory-dropdown').empty().append('<option value="">Select...</option>');
                        $.each(d, function(_, v) {
                            $('#subcategory-dropdown').append('<option value="' + v.id + '">' + v.name + '</option>');
                        });
                    });
                }
            });
        });
    </script>
@endsection
