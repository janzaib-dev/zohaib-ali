@extends('admin_panel.layout.app')

@section('content')
    <style>
        :root {
            --c-primary: #2563eb;
            --c-success: #16a34a;
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

        .filter-card .form-control {
            border-radius: 8px;
            border: 1.5px solid #e2e8f0;
            font-size: .86rem;
            padding: 7px 11px;
            height: auto;
            transition: border-color .2s, box-shadow .2s;
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
        }

        .btn-srp.blue {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
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
            padding: 10px 11px;
            border: none;
        }

        #stockTable tbody td {
            padding: 8px 11px;
            vertical-align: middle;
            font-size: .82rem;
        }

        #stockTable tfoot th {
            background: #f8fafc;
            font-size: .8rem;
            font-weight: 700;
            padding: 9px 11px;
            border-top: 2px solid #e2e8f0;
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container-fluid">

                <div class="rpt-header">
                    <div>
                        <h3><i class="fas fa-warehouse me-2"></i> Warehouse Stock Report</h3>
                        <p>Detailed view of all inventory items filtered by warehouse location</p>
                    </div>
                    <div class="rpt-header-icon"><i class="fas fa-boxes"></i></div>
                </div>

                <div class="filter-card">
                    <div class="filter-title"><i class="fas fa-filter"></i> Filters</div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Warehouse</label>
                            <select id="filterWarehouse" class="form-control">
                                <option value="all">All Warehouses</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-1">
                            <button type="button" id="btnSearch" class="btn-srp blue flex-fill">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-card">
                    <div class="table-responsive">
                        <table id="stockTable" class="table table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Warehouse Name</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Brand</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                    <th>Stock Value (PKR)</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody"></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="7" class="text-end">Grand Total:</th>
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
    <script>
        $(document).ready(function() {
            var dt = $('#stockTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                pageLength: 25,
                language: {
                    search: '',
                    searchPlaceholder: 'Quick search...'
                }
            });

            function fmt(v, dec) {
                if (!v || isNaN(v)) return (0).toFixed(dec);
                return parseFloat(v).toLocaleString('en-US', {
                    minimumFractionDigits: dec,
                    maximumFractionDigits: dec
                });
            }

            function loadData() {
                var wh = $('#filterWarehouse').val();

                $.ajax({
                    url: '{{ route('report.warehouse.fetch') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        warehouse_id: wh
                    },
                    success: function(res) {
                        dt.clear();

                        var $whDropdown = $('#filterWarehouse');
                        if ($whDropdown.find('option').length === 1) {
                            res.warehouses.forEach(function(w) {
                                $whDropdown.append('<option value="' + w.id + '">' + w
                                    .warehouse_name + '</option>');
                            });
                            // Keep selected
                            $whDropdown.val(wh);
                        }

                        res.data.forEach(function(r, idx) {
                            dt.row.add([
                                idx + 1,
                                '<strong>' + r.warehouse_name + '</strong>',
                                r.item_code,
                                r.item_name,
                                r.brand_name,
                                r.unit_name,
                                '<span class="badge bg-primary px-2 py-1">' + r
                                .display_qty + '</span>',
                                'PKR ' + fmt(r.stock_value, 2)
                            ]);
                        });

                        dt.draw();

                        $('#ftStockVal').text('PKR ' + fmt(res.grand_value, 2));
                    }
                });
            }

            // Init
            loadData();

            $('#btnSearch').click(function() {
                loadData();
            });
        });
    </script>
@endsection
