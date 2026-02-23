@extends('admin_panel.layout.app')

@section('content')
    <style>
        :root {
            --c-primary: #2563eb;
            --c-success: #16a34a;
            --c-warning: #d97706;
            --c-danger: #dc2626;
            --c-purple: #7c3aed;
            --c-cyan: #0891b2;
            --card-shadow: 0 1px 4px rgba(0, 0, 0, .07), 0 6px 20px rgba(0, 0, 0, .06);
        }

        .rpt-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 55%, #38bdf8 100%);
            border-radius: 14px;
            padding: 22px 28px;
            margin-bottom: 22px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 6px 24px rgba(37, 99, 235, .32);
        }

        .rpt-header h3 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 700;
        }

        .rpt-header p {
            margin: 3px 0 0;
            font-size: .84rem;
            opacity: .82;
        }

        .rpt-header-icon {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            background: rgba(255, 255, 255, .16);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }

        .kpi-card {
            background: #fff;
            border-radius: 12px;
            padding: 16px 18px;
            box-shadow: var(--card-shadow);
            border-left: 4px solid transparent;
            display: flex;
            flex-direction: column;
            gap: 5px;
            transition: transform .15s;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
        }

        .kpi-card.blue {
            border-color: var(--c-primary);
        }

        .kpi-card.green {
            border-color: var(--c-success);
        }

        .kpi-card.amber {
            border-color: var(--c-warning);
        }

        .kpi-card.red {
            border-color: var(--c-danger);
        }

        .kpi-card.purple {
            border-color: var(--c-purple);
        }

        .kpi-label {
            font-size: .73rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #64748b;
        }

        .kpi-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.1;
        }

        .kpi-sub {
            font-size: .73rem;
            color: #94a3b8;
        }

        .kpi-icon {
            font-size: 1.2rem;
        }

        .kpi-card.blue .kpi-icon {
            color: var(--c-primary);
        }

        .kpi-card.green .kpi-icon {
            color: var(--c-success);
        }

        .kpi-card.amber .kpi-icon {
            color: var(--c-warning);
        }

        .kpi-card.red .kpi-icon {
            color: var(--c-danger);
        }

        .kpi-card.purple .kpi-icon {
            color: var(--c-purple);
        }

        .filter-card {
            background: #fff;
            border-radius: 12px;
            padding: 16px 20px;
            box-shadow: var(--card-shadow);
            margin-bottom: 18px;
        }

        .filter-title {
            font-size: .73rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .7px;
            color: #475569;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .filter-card .form-control,
        .filter-card .form-select {
            border-radius: 8px;
            border: 1.5px solid #e2e8f0;
            font-size: .86rem;
            padding: 7px 11px;
            height: auto;
            transition: border-color .2s, box-shadow .2s;
        }

        .filter-card .form-control:focus {
            border-color: var(--c-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
            outline: none;
        }

        label.form-label {
            font-size: .78rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .btn-srp {
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: .85rem;
            padding: 8px 18px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: box-shadow .2s, transform .1s;
        }

        .btn-srp:hover {
            transform: translateY(-1px);
        }

        .btn-srp.blue {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
        }

        .btn-srp.blue:hover {
            box-shadow: 0 4px 14px rgba(37, 99, 235, .38);
        }

        .btn-srp.ghost {
            background: #f1f5f9;
            color: #475569;
            border: 1.5px solid #e2e8f0;
        }

        .btn-srp.ghost:hover {
            background: #e2e8f0;
        }

        .btn-srp.green {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: #fff;
        }

        .btn-srp.green:hover {
            box-shadow: 0 4px 14px rgba(22, 163, 74, .38);
        }

        .btn-srp.purple {
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: #fff;
        }

        .btn-srp.purple:hover {
            box-shadow: 0 4px 14px rgba(124, 58, 237, .38);
        }

        .table-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px 20px 14px;
            box-shadow: var(--card-shadow);
        }

        #stockTable thead th {
            background: #1e3a8a;
            color: #fff;
            font-size: .73rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            white-space: nowrap;
            padding: 10px 11px;
            border: none;
        }

        #stockTable thead th:first-child {
            border-radius: 8px 0 0 0;
        }

        #stockTable thead th:last-child {
            border-radius: 0 8px 0 0;
        }

        #stockTable tbody tr {
            font-size: .82rem;
            transition: background .1s;
        }

        #stockTable tbody tr:hover {
            background: #eff6ff !important;
        }

        #stockTable tbody td {
            padding: 8px 11px;
            vertical-align: middle;
            border-color: #f1f5f9;
        }

        #stockTable tfoot th {
            background: #f8fafc;
            font-size: .8rem;
            font-weight: 700;
            color: #1e293b;
            padding: 9px 11px;
            border-top: 2px solid #e2e8f0;
        }

        .mode-badge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: .68rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .mode-badge.by_size {
            background: #ede9fe;
            color: #5b21b6;
        }

        .mode-badge.by_carton {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .mode-badge.by_piece {
            background: #dcfce7;
            color: #15803d;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: .71rem;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 20px;
        }

        .status-badge.normal {
            background: #dcfce7;
            color: #15803d;
        }

        .status-badge.low_stock {
            background: #fef9c3;
            color: #a16207;
        }

        .status-badge.out_of_stock {
            background: #fee2e2;
            color: #b91c1c;
        }

        .balance-main {
            font-weight: 700;
            font-size: .9rem;
        }

        .balance-sub {
            font-size: .71rem;
            color: #94a3b8;
            line-height: 1.3;
        }

        .wh-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            min-width: 170px;
        }

        .wh-pill {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: .72rem;
            padding: 3px 8px;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }

        .wh-pill .wh-name {
            font-weight: 600;
            color: #1e3a8a;
        }

        .wh-pill .wh-qty {
            font-weight: 700;
            color: #6d28d9;
        }

        .wh-no-data {
            font-size: .75rem;
            color: #94a3b8;
            font-style: italic;
        }

        .amt-chip {
            font-weight: 600;
            font-size: .82rem;
        }

        .amt-chip.pur {
            color: #1d4ed8;
        }

        .amt-chip.sal {
            color: #15803d;
        }

        .amt-chip.val {
            color: #6d28d9;
            font-size: .88rem;
        }

        .total-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 18px;
        }

        .total-tile {
            border-radius: 10px;
            padding: 11px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .total-tile.pur {
            background: #eff6ff;
        }

        .total-tile.sal {
            background: #f0fdf4;
        }

        .total-tile.val {
            background: #faf5ff;
        }

        .total-tile .tt-label {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .total-tile.pur .tt-label {
            color: #3b82f6;
        }

        .total-tile.sal .tt-label {
            color: #16a34a;
        }

        .total-tile.val .tt-label {
            color: #7c3aed;
        }

        .total-tile .tt-val {
            font-size: 1.05rem;
            font-weight: 700;
        }

        .total-tile.pur .tt-val {
            color: #1e3a8a;
        }

        .total-tile.sal .tt-val {
            color: #14532d;
        }

        .total-tile.val .tt-val {
            color: #4c1d95;
        }

        .total-tile .tt-icon {
            font-size: 1.2rem;
        }

        .loader-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, .65);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .loader-box {
            background: #fff;
            border-radius: 14px;
            padding: 30px 38px;
            text-align: center;
            box-shadow: 0 8px 40px rgba(0, 0, 0, .12);
        }

        .loader-box .spinner-border {
            width: 2.5rem;
            height: 2.5rem;
            color: var(--c-primary);
            border-width: 3px;
        }

        .loader-box p {
            margin: 10px 0 0;
            font-size: .88rem;
            color: #64748b;
            font-weight: 600;
        }

        @media(max-width:1000px) {
            .kpi-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media(max-width:640px) {
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .total-strip {
                grid-template-columns: 1fr;
            }
        }

        @media print {

            .filter-card,
            .btn-srp,
            .total-strip,
            .rpt-header {
                display: none !important;
            }

            .main-content,
            .main-content-inner,
            .container-fluid,
            #stockTableWrap {
                display: block !important;
                width: 100% !important;
            }

            .print-header {
                display: block !important;
            }

            #stockTable thead th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>

    <!-- Loader -->
    <div class="loader-overlay" id="loaderOverlay">
        <div class="loader-box">
            <div class="spinner-border" role="status"></div>
            <p>Loading stock data…</p>
        </div>
    </div>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container-fluid">

                <!-- Print Header (only visible when printing) -->
                <div class="print-header" style="display:none; margin-bottom:16px;">
                    <h2 style="margin:0;font-size:18px;font-weight:700;">📊 Item Stock Report</h2>
                    <p style="margin:4px 0 0;font-size:12px;color:#555;">Printed: {{ now()->format('d M Y H:i') }}</p>
                </div>

                <!-- Page Header -->
                <div class="rpt-header">
                    <div>
                        <h3><i class="fas fa-layer-group me-2"></i> Item Stock Report</h3>
                        <p>Full inventory — stock by size mode, per-warehouse breakdown, movements &amp; valuations</p>
                        <p style="opacity:.62;font-size:.76rem;margin-top:4px;">Generated: <span id="reportDate"></span></p>
                    </div>
                    <div class="rpt-header-icon"><i class="fas fa-chart-bar"></i></div>
                </div>

                <!-- KPI Cards -->
                <div class="kpi-grid">
                    <div class="kpi-card blue">
                        <span class="kpi-icon"><i class="fas fa-boxes"></i></span>
                        <span class="kpi-label">Total Products</span>
                        <span class="kpi-value" id="kpiTotal">—</span>
                        <span class="kpi-sub">In this view</span>
                    </div>
                    <div class="kpi-card purple">
                        <span class="kpi-icon"><i class="fas fa-gem"></i></span>
                        <span class="kpi-label">Stock Value</span>
                        <span class="kpi-value" id="kpiValue">—</span>
                        <span class="kpi-sub">PKR</span>
                    </div>
                    <div class="kpi-card green">
                        <span class="kpi-icon"><i class="fas fa-warehouse"></i></span>
                        <span class="kpi-label">Warehouses</span>
                        <span class="kpi-value" id="kpiWarehouses">—</span>
                        <span class="kpi-sub">Distinct locations</span>
                    </div>
                    <div class="kpi-card amber">
                        <span class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></span>
                        <span class="kpi-label">Low Stock</span>
                        <span class="kpi-value" id="kpiLow">—</span>
                        <span class="kpi-sub">At/below alert qty</span>
                    </div>
                    <div class="kpi-card red">
                        <span class="kpi-icon"><i class="fas fa-times-circle"></i></span>
                        <span class="kpi-label">Out of Stock</span>
                        <span class="kpi-value" id="kpiOut">—</span>
                        <span class="kpi-sub">Zero balance</span>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filter-card">
                    <div class="filter-title"><i class="fas fa-filter"></i> Filters &amp; Actions</div>
                    <div class="row g-2 align-items-end">

                        <div class="col-md-4">
                            <label class="form-label">Product</label>
                            <select id="product_id" class="form-control select2-product">
                                <option value="all">— All Products —</option>
                                @foreach ($products as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->item_code }} — {{ $prod->item_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Warehouse</label>
                            <select id="filterWarehouse" class="form-control">
                                <option value="all">All Warehouses</option>
                                {{-- Populated dynamically after first fetch --}}
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Size Mode</label>
                            <select id="filterMode" class="form-control">
                                <option value="all">All Modes</option>
                                <option value="by_size">📐 By Size (m²)</option>
                                <option value="by_carton">📦 By Carton</option>
                                <option value="by_piece">🔢 By Piece</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select id="filterStatus" class="form-control">
                                <option value="all">All Status</option>
                                <option value="normal">✅ Normal</option>
                                <option value="low_stock">⚠️ Low Stock</option>
                                <option value="out_of_stock">❌ Out of Stock</option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex flex-column gap-1">
                            <div class="d-flex gap-1">
                                <button type="button" id="btnSearch" class="btn-srp blue flex-fill">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <button type="button" id="btnReset" class="btn-srp ghost">
                                    <i class="fas fa-redo"></i>
                                </button>
                            </div>
                            <div class="d-flex gap-1">
                                <button type="button" id="btnExportCsv" class="btn-srp green flex-fill">
                                    <i class="fas fa-file-csv"></i> CSV
                                </button>
                                <button type="button" onclick="printReport()" class="btn-srp purple">
                                    <i class="fas fa-print"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Totals Strip -->
                <div class="total-strip" id="totalsStrip" style="display:none;">
                    <div class="total-tile pur">
                        <i class="fas fa-shopping-cart tt-icon" style="color:#3b82f6;"></i>
                        <div>
                            <div class="tt-label">Total Purchase Amount</div>
                            <div class="tt-val" id="stripPurchase">PKR 0.00</div>
                        </div>
                    </div>
                    <div class="total-tile sal">
                        <i class="fas fa-receipt tt-icon" style="color:#16a34a;"></i>
                        <div>
                            <div class="tt-label">Total Sale Amount</div>
                            <div class="tt-val" id="stripSale">PKR 0.00</div>
                        </div>
                    </div>
                    <div class="total-tile val">
                        <i class="fas fa-gem tt-icon" style="color:#7c3aed;"></i>
                        <div>
                            <div class="tt-label">Grand Stock Value</div>
                            <div class="tt-val" id="stripValue">PKR 0.00</div>
                        </div>
                    </div>
                </div>

                <!-- Main Table -->
                <div class="table-card">
                    <div class="table-responsive">
                        <table id="stockTable" class="table table-bordered" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Brand / Cat.</th>
                                    <th>Mode</th>
                                    <th>Stock Detail</th>
                                    <th>Warehouse Stock</th>
                                    <th>Status</th>
                                    <th>Init.</th>
                                    <th>Purchased</th>
                                    <th>Pur.Return</th>
                                    <th>Sold</th>
                                    <th>Sale Return</th>
                                    <th>Balance</th>
                                    <th>Pur.Amt</th>
                                    <th>Sale Amt</th>
                                    <th>Price</th>
                                    <th>Stock Value</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody"></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="8" class="text-end">Grand Totals:</th>
                                    <th id="ftInit">0</th>
                                    <th id="ftPurchased">0</th>
                                    <th id="ftPurRet">0</th>
                                    <th id="ftSold">0</th>
                                    <th id="ftSaleRet">0</th>
                                    <th id="ftBalance">0</th>
                                    <th id="ftPurAmt">0.00</th>
                                    <th id="ftSaleAmt">0.00</th>
                                    <th></th>
                                    <th id="ftStockVal">0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('js')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {

            document.getElementById('reportDate').textContent = new Date().toLocaleString();

            // ── Select2 ─────────────────────────────────────────────────────────
            $('.select2-product').select2({
                placeholder: '— All Products —',
                allowClear: true,
                width: '100%'
            });

            // ── DataTable ────────────────────────────────────────────────────────
            var dt = $('#stockTable').DataTable({
                paging: true,
                searching: true,
                info: true,
                ordering: true,
                pageLength: 25,
                order: [
                    [7, 'asc']
                ],
                language: {
                    search: '',
                    searchPlaceholder: '🔍 Quick search…',
                    lengthMenu: 'Show _MENU_ rows',
                    info: 'Showing _START_–_END_ of _TOTAL_ items',
                    paginate: {
                        previous: '‹',
                        next: '›'
                    }
                },
                columnDefs: [{
                        targets: [0],
                        className: 'text-center',
                        width: '38px'
                    },
                    {
                        targets: [4, 7],
                        className: 'text-center'
                    },
                    {
                        targets: [8, 9, 10, 11, 12, 13, 14, 15, 17],
                        className: 'text-right'
                    },
                ],
                drawCallback: updateFooter
            });

            // ── Formatters ───────────────────────────────────────────────────────
            function fmt(v, dec) {
                dec = (dec === undefined) ? 2 : dec;
                return parseFloat(v || 0).toLocaleString('en-PK', {
                    minimumFractionDigits: dec,
                    maximumFractionDigits: dec
                });
            }

            function fmtPKR(v) {
                return 'PKR ' + fmt(v, 2);
            }

            // ── Size mode badge ──────────────────────────────────────────────────
            var modeLabels = {
                by_size: '📐 By Size',
                by_carton: '📦 By Carton',
                by_piece: '🔢 By Piece'
            };

            function modeBadge(m) {
                return '<span class="mode-badge ' + m + '">' + (modeLabels[m] || m) + '</span>';
            }

            // ── Status badge ─────────────────────────────────────────────────────
            var statusMap = {
                normal: ['<i class="fas fa-check-circle"></i> Normal', 'normal'],
                low_stock: ['<i class="fas fa-exclamation-triangle"></i> Low', 'low_stock'],
                out_of_stock: ['<i class="fas fa-times-circle"></i> Out of Stock', 'out_of_stock'],
            };

            function statusBadge(s) {
                var info = statusMap[s] || statusMap.normal;
                return '<span class="status-badge ' + info[1] + '">' + info[0] + '</span>';
            }

            // ── Stock detail cell — per size_mode ────────────────────────────────
            function stockDetailCell(r) {
                var db = r.display_balance || {};
                var mode = db.mode || r.size_mode;

                if (mode === 'by_size') {
                    var m2 = parseFloat(db.total_m2 || 0).toFixed(4);
                    var col = r.stock_status === 'out_of_stock' ? '#dc2626' : r.stock_status === 'low_stock' ?
                        '#d97706' : '#6d28d9';
                    return '<div class="balance-main" style="color:' + col + ';">' + m2 + ' m²</div>' +
                        '<div class="balance-sub">' + fmt(db.boxes, 0) + ' box + ' + fmt(db.loose, 0) + ' pcs<br>' +
                        '<span style="color:#94a3b8;">' + fmt(r.height, 0) + '×' + fmt(r.width, 0) + ' cm · ' +
                        parseFloat(r.total_m2_box || 0).toFixed(4) + ' m²/box</span></div>';

                } else if (mode === 'by_carton') {
                    var col2 = r.stock_status === 'out_of_stock' ? '#dc2626' : r.stock_status === 'low_stock' ?
                        '#d97706' : '#15803d';
                    return '<div class="balance-main" style="color:' + col2 + ';">' + fmt(db.boxes, 0) +
                        ' Box</div>' +
                        '<div class="balance-sub">+' + fmt(db.loose, 0) + ' loose pcs<br>' +
                        '<span style="color:#94a3b8;">' + r.pieces_per_box + ' pcs/box · ' + fmt(db.pieces, 0) +
                        ' total pcs</span></div>';

                } else {
                    var col3 = r.stock_status === 'out_of_stock' ? '#dc2626' : r.stock_status === 'low_stock' ?
                        '#d97706' : '#15803d';
                    return '<div class="balance-main" style="color:' + col3 + ';">' + fmt(db.pieces || r.balance,
                            0) + ' Pcs</div>' +
                        '<div class="balance-sub"><span style="color:#94a3b8;">Unit: ' + r.unit + '</span></div>';
                }
            }

            // ── Balance ledger cell ──────────────────────────────────────────────
            function balanceCell(r) {
                var b = parseFloat(r.balance || 0);
                var col = r.stock_status === 'out_of_stock' ? '#dc2626' : r.stock_status === 'low_stock' ?
                    '#d97706' : '#15803d';
                return '<span style="font-weight:700;color:' + col + ';">' + fmt(b, 2) + '</span><br>' +
                    '<span class="balance-sub">pcs</span>';
            }

            // ── Warehouse pills ──────────────────────────────────────────────────
            function whCell(r) {
                var whs = r.warehouses || [];
                if (!whs.length) {
                    return '<span class="wh-no-data"><i class="fas fa-times-circle" style="color:#fca5a5;"></i> No stock</span>';
                }
                var pills = whs.map(function(w) {
                    return '<div class="wh-pill">' +
                        '<i class="fas fa-warehouse" style="color:#64748b;font-size:.7rem;"></i>' +
                        '<span class="wh-name">' + w.warehouse_name + '</span>:' +
                        '<span class="wh-qty">' + w.display + '</span>' +
                        '</div>';
                }).join('');
                return '<div class="wh-pills">' + pills + '</div>';
            }

            // ── Price cell ───────────────────────────────────────────────────────
            function priceCell(r) {
                var mode = (r.display_balance || {}).mode || r.size_mode;
                if (mode === 'by_size' && r.price_per_m2 > 0)
                    return '<span style="font-size:.8rem;">' + fmtPKR(r.price_per_m2) +
                        '<br><span style="color:#64748b;">/m²</span></span>';
                if (r.sale_price_per_piece > 0)
                    return '<span style="font-size:.8rem;">' + fmtPKR(r.sale_price_per_piece) +
                        '<br><span style="color:#64748b;">/pc</span></span>';
                return '<span style="font-size:.8rem;">' + fmtPKR(r.sale_price_per_box) +
                    '<br><span style="color:#64748b;">/box</span></span>';
            }

            // ── State ────────────────────────────────────────────────────────────
            var _allRows = [];
            var _warehousesLoaded = false;

            // ── Render rows ──────────────────────────────────────────────────────
            function renderRows(rows) {
                _allRows = rows;
                dt.clear();

                var kTotal = rows.length,
                    kVal = 0,
                    kLow = 0,
                    kOut = 0;
                var whSet = new Set();
                var gInit = 0,
                    gPur = 0,
                    gPurRet = 0,
                    gSold = 0,
                    gSaleRet = 0,
                    gBal = 0,
                    gPurAmt = 0,
                    gSaleAmt = 0,
                    gVal = 0;

                rows.forEach(function(r, i) {
                    kVal += parseFloat(r.stock_value || 0);
                    if (r.stock_status === 'low_stock') kLow++;
                    if (r.stock_status === 'out_of_stock') kOut++;
                    (r.warehouses || []).forEach(function(w) {
                        whSet.add(w.warehouse_name);
                    });

                    gInit += parseFloat(r.initial_stock || 0);
                    gPur += parseFloat(r.purchased || 0);
                    gPurRet += parseFloat(r.purchase_return_qty || 0);
                    gSold += parseFloat(r.sold || 0);
                    gSaleRet += parseFloat(r.sale_return_qty || 0);
                    gBal += parseFloat(r.balance || 0);
                    gPurAmt += parseFloat(r.purchase_amount || 0);
                    gSaleAmt += parseFloat(r.sale_amount || 0);
                    gVal += parseFloat(r.stock_value || 0);

                    dt.row.add([
                        i + 1,
                        '<strong style="color:#1e3a8a;">' + r.item_code + '</strong>',
                        '<div style="font-weight:600;color:#0f172a;max-width:180px;">' + r
                        .item_name + '</div>' +
                        (r.color && r.color !== '-' ?
                            '<span style="font-size:.72rem;color:#64748b;">🎨 ' + r.color +
                            '</span>' : ''),
                        '<span style="font-weight:600;font-size:.82rem;">' + r.brand +
                        '</span><br>' +
                        '<span style="font-size:.72rem;color:#64748b;">' + r.category +
                        (r.sub_category && r.sub_category !== '-' ? ' › ' + r.sub_category : '') +
                        '</span>',
                        modeBadge(r.size_mode),
                        stockDetailCell(r),
                        whCell(r),
                        statusBadge(r.stock_status),
                        '<span style="font-size:.82rem;">' + fmt(r.initial_stock, 0) + '</span>',
                        '<span style="color:#1d4ed8;font-weight:600;">' + fmt(r.purchased, 0) +
                        '</span>',
                        r.purchase_return_qty > 0 ? '<span style="color:#dc2626;">-' + fmt(r
                            .purchase_return_qty, 0) + '</span>' :
                        '<span style="color:#94a3b8;">0</span>',
                        '<span style="color:#b45309;font-weight:600;">' + fmt(r.sold, 0) +
                        '</span>',
                        r.sale_return_qty > 0 ? '<span style="color:#7c3aed;">+' + fmt(r
                            .sale_return_qty, 0) + '</span>' :
                        '<span style="color:#94a3b8;">0</span>',
                        balanceCell(r),
                        '<span class="amt-chip pur">' + fmtPKR(r.purchase_amount) + '</span>',
                        '<span class="amt-chip sal">' + fmtPKR(r.sale_amount) + '</span>',
                        priceCell(r),
                        '<strong class="amt-chip val">' + fmtPKR(r.stock_value) + '</strong>',
                    ]).draw(false);
                });

                // KPIs
                $('#kpiTotal').text(kTotal.toLocaleString());
                $('#kpiValue').text('PKR ' + fmt(kVal, 0));
                $('#kpiWarehouses').text(whSet.size);
                $('#kpiLow').text(kLow);
                $('#kpiOut').text(kOut);
                // Totals strip
                $('#stripPurchase').text(fmtPKR(gPurAmt));
                $('#stripSale').text(fmtPKR(gSaleAmt));
                $('#stripValue').text(fmtPKR(gVal));
                $('#totalsStrip').show();
                // Footer
                window._gTotals = {
                    gInit,
                    gPur,
                    gPurRet,
                    gSold,
                    gSaleRet,
                    gBal,
                    gPurAmt,
                    gSaleAmt,
                    gVal
                };
                updateFooter();
            }

            function updateFooter() {
                var g = window._gTotals;
                if (!g) return;
                $('#ftInit').text(fmt(g.gInit, 0));
                $('#ftPurchased').text(fmt(g.gPur, 0));
                $('#ftPurRet').text('-' + fmt(g.gPurRet, 0));
                $('#ftSold').text(fmt(g.gSold, 0));
                $('#ftSaleRet').text('+' + fmt(g.gSaleRet, 0));
                $('#ftBalance').text(fmt(g.gBal, 0));
                $('#ftPurAmt').text(fmtPKR(g.gPurAmt));
                $('#ftSaleAmt').text(fmtPKR(g.gSaleAmt));
                $('#ftStockVal').text(fmtPKR(g.gVal));
            }

            // ── Populate warehouse dropdown ──────────────────────────────────────
            function populateWarehouseFilter(warehouses) {
                if (_warehousesLoaded) return;
                _warehousesLoaded = true;
                var $sel = $('#filterWarehouse');
                $sel.find('option:not(:first)').remove();
                warehouses.forEach(function(w) {
                    $sel.append('<option value="' + w.id + '">' + w.warehouse_name + '</option>');
                });
            }

            // ── Fetch data ───────────────────────────────────────────────────────
            function fetchReport() {
                var productId = $('#product_id').val() || 'all';
                var warehouseId = $('#filterWarehouse').val() || 'all';

                $('#loaderOverlay').css('display', 'flex');

                $.ajax({
                    url: "{{ route('report.item_stock.fetch') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        product_id: productId,
                        warehouse_id: warehouseId
                    },
                    success: function(res) {
                        $('#loaderOverlay').hide();

                        if (res.warehouses && res.warehouses.length) {
                            populateWarehouseFilter(res.warehouses);
                        }

                        var rows = res.data || [];

                        // Client-side mode filter
                        var mode = $('#filterMode').val();
                        if (mode && mode !== 'all') {
                            rows = rows.filter(function(r) {
                                return r.size_mode === mode;
                            });
                        }

                        // Client-side status filter
                        var status = $('#filterStatus').val();
                        if (status && status !== 'all') {
                            rows = rows.filter(function(r) {
                                return r.stock_status === status;
                            });
                        }

                        if (rows.length) {
                            renderRows(rows);
                        } else {
                            dt.clear().draw();
                            resetKpi();
                        }
                    },
                    error: function(xhr) {
                        $('#loaderOverlay').hide();
                        var msg = 'Failed to load data.';
                        try {
                            msg = JSON.parse(xhr.responseText).message || msg;
                        } catch (e) {}
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg,
                            timer: 5000,
                            showConfirmButton: false
                        });
                        console.error(xhr.responseText);
                    }
                });
            }

            function resetKpi() {
                ['kpiTotal', 'kpiValue', 'kpiWarehouses', 'kpiLow', 'kpiOut'].forEach(function(id) {
                    document.getElementById(id).textContent = '0';
                });
                $('#stripPurchase,#stripSale,#stripValue').text('PKR 0.00');
                $('#totalsStrip').hide();
                window._gTotals = null;
            }

            // ── Event bindings ───────────────────────────────────────────────────
            $('#btnSearch').on('click', fetchReport);
            $('#filterStatus,#filterMode').on('change', fetchReport);
            $('#filterWarehouse').on('change', function() {
                _warehousesLoaded = false; // allow re-fetch to reload WH list
                fetchReport();
                _warehousesLoaded = true;
            });

            $('#btnReset').on('click', function() {
                $('#product_id').val('all').trigger('change');
                $('#filterWarehouse').val('all');
                $('#filterMode').val('all');
                $('#filterStatus').val('all');
                _warehousesLoaded = false;
                fetchReport();
            });

            // ── CSV Export ───────────────────────────────────────────────────────
            $('#btnExportCsv').on('click', function() {
                if (!_allRows || !_allRows.length) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Data',
                        text: 'Run a search first.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                    return;
                }
                var headers = ['#', 'Item Code', 'Item Name', 'Brand', 'Category', 'Sub-Cat', 'Unit',
                    'Color',
                    'Size Mode', 'Height', 'Width', 'm²/Box', 'Pcs per Box',
                    'Init.Stock', 'Purchased', 'Pur.Return', 'NetPurchased', 'Sold', 'Sale Return',
                    'NetSold',
                    'Balance(pcs)', 'Stock Boxes', 'Stock Loose Pcs', 'Balance m²',
                    'Status', 'Alert Qty', 'Pur.Amount', 'Sale Amount',
                    'Sale Price/Pc', 'Sale Price/Box', 'Pur.Price/Pc', 'Pur.Price/Box', 'Price/m²',
                    'Stock Value', 'Warehouses'
                ];

                var csv = headers.join(',') + '\n';
                _allRows.forEach(function(r, i) {
                    var db = r.display_balance || {};
                    var whs = (r.warehouses || []).map(function(w) {
                        return w.warehouse_name + ':' + w.display;
                    }).join('; ');
                    var esc = function(s) {
                        return '"' + String(s || '').replace(/"/g, '""') + '"';
                    };
                    csv += [
                        i + 1, r.item_code, esc(r.item_name), esc(r.brand),
                        esc(r.category), esc(r.sub_category), r.unit, r.color,
                        r.size_mode, r.height, r.width, r.total_m2_box, r.pieces_per_box,
                        r.initial_stock, r.purchased, r.purchase_return_qty, r.net_purchased,
                        r.sold, r.sale_return_qty, r.net_sold,
                        r.balance, db.boxes || 0, db.loose || 0, db.total_m2 || '',
                        r.stock_status, r.alert_quantity,
                        r.purchase_amount, r.sale_amount,
                        r.sale_price_per_piece, r.sale_price_per_box,
                        r.purchase_price_per_piece, r.purchase_price_per_box,
                        r.price_per_m2, r.stock_value,
                        esc(whs || 'No stock')
                    ].join(',') + '\n';
                });

                var blob = new Blob(['\uFEFF' + csv], {
                    type: 'text/csv;charset=utf-8;'
                });
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'item_stock_report_' + new Date().toISOString().slice(0, 10) + '.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            });

            // ── Auto-load on page open ───────────────────────────────────────────
            fetchReport();
        });

        function printReport() {
            const wrap = document.getElementById('stockTableWrap');
            if (wrap) wrap.style.display = 'block';
            const strip = document.getElementById('totalsStrip');
            if (strip) strip.style.display = 'flex';
            window.print();
        }
    </script>
@endsection
