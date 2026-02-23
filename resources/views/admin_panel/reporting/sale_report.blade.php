@extends('admin_panel.layout.app')

@section('content')
    <style>
        /* ── Sale Report Styles ────────────────────────── */
        .rpt-page {
            padding: 20px;
        }

        .rpt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .rpt-header h4 {
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0;
            color: #1e293b;
        }

        .rpt-header p {
            margin: 0;
            color: #64748b;
            font-size: .85rem;
        }

        /* Filters */
        .filter-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 18px;
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .filter-group label {
            font-size: .78rem;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .filter-group select,
        .filter-group input {
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            padding: 7px 10px;
            font-size: .88rem;
            color: #1e293b;
            outline: none;
            background: #f8fafc;
            min-width: 160px;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            border-color: #0ea5e9;
            background: #fff;
        }

        .btn-search {
            background: #0ea5e9;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 8px 20px;
            font-size: .88rem;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-search:hover {
            background: #0284c7;
        }

        .btn-reset {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            padding: 8px 16px;
            font-size: .88rem;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-reset:hover {
            background: #e2e8f0;
        }

        .btn-csv {
            background: #10b981;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 8px 16px;
            font-size: .88rem;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-csv:hover {
            background: #059669;
        }

        .btn-print {
            background: #6366f1;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 8px 16px;
            font-size: .88rem;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-print:hover {
            background: #4f46e5;
        }

        /* KPI Cards */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .kpi-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px 16px;
        }

        .kpi-card .kpi-label {
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #94a3b8;
            margin-bottom: 4px;
        }

        .kpi-card .kpi-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
        }

        .kpi-card.kpi-net .kpi-value {
            color: #0ea5e9;
        }

        .kpi-card.kpi-paid .kpi-value {
            color: #10b981;
        }

        .kpi-card.kpi-due .kpi-value {
            color: #ef4444;
        }

        .kpi-card.kpi-ret .kpi-value {
            color: #f59e0b;
        }

        /* Table */
        .table-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .table-card table {
            width: 100%;
            border-collapse: collapse;
            font-size: .85rem;
        }

        .table-card thead tr {
            background: #f0f9ff;
        }

        .table-card thead th {
            padding: 10px 12px;
            text-align: left;
            font-size: .75rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: .4px;
            white-space: nowrap;
            border-bottom: 2px solid #e2e8f0;
        }

        .table-card tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background .12s;
        }

        .table-card tbody tr:hover {
            background: #f0f9ff;
        }

        .table-card tbody td {
            padding: 9px 12px;
            vertical-align: middle;
            color: #334155;
        }

        .table-card tfoot td {
            padding: 10px 12px;
            font-weight: 700;
            background: #f8fafc;
            font-size: .85rem;
            color: #1e293b;
            border-top: 2px solid #e2e8f0;
        }

        /* Status badges */
        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: .73rem;
            font-weight: 700;
        }

        .badge-posted {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-booked {
            background: #fef3c7;
            color: #d97706;
        }

        .badge-cancelled {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-returned {
            background: #ede9fe;
            color: #7c3aed;
        }

        /* Items expand */
        .btn-expand {
            background: none;
            border: none;
            cursor: pointer;
            color: #0ea5e9;
            font-size: .82rem;
            font-weight: 600;
            padding: 0;
        }

        .items-row {
            display: none;
        }

        .items-row.open {
            display: table-row;
        }

        .items-mini-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .8rem;
            margin: 6px 0;
        }

        .items-mini-table th {
            background: #f0f9ff;
            padding: 6px 10px;
            text-align: left;
            color: #475569;
            font-size: .73rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .items-mini-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .loader-wrap {
            text-align: center;
            padding: 40px;
            display: none;
        }

        .loader-wrap .spinner {
            width: 36px;
            height: 36px;
            border: 4px solid #e0f2fe;
            border-top-color: #0ea5e9;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .empty-state {
            text-align: center;
            padding: 60px;
            color: #94a3b8;
        }

        .empty-state svg {
            width: 48px;
            height: 48px;
            margin-bottom: 12px;
            opacity: .4;
        }

        .empty-state p {
            font-size: .95rem;
        }

        .text-right {
            text-align: right;
        }

        .num {
            font-variant-numeric: tabular-nums;
        }

        @media print {

            .filter-card,
            .btn-search,
            .btn-reset,
            .btn-csv,
            .btn-print,
            .rpt-actions,
            .kpi-row {
                display: none !important;
            }

            .rpt-page,
            .table-card {
                display: block !important;
                width: 100% !important;
                border: none !important;
            }

            .print-header {
                display: block !important;
            }
        }
    </style>

    <div class="rpt-page">
        <!-- Print Header (only visible when printing) -->
        <div class="print-header" style="display:none; margin-bottom:16px;">
            <h2 style="margin:0;font-size:18px;font-weight:700;">🧾 Sale Report</h2>
            <p id="printSubtitle" style="margin:4px 0 0;font-size:12px;color:#555;"></p>
        </div>
        {{-- Header --}}
        <div class="rpt-header">
            <div>
                <h4>🧾 Sale Report</h4>
                <p>Complete sales analysis with customer, items, returns & payment status</p>
            </div>
            <div class="rpt-actions" style="display:flex;gap:8px;">
                <button class="btn-csv" id="btnExportCsv">⬇ Export CSV</button>
                <button class="btn-print" onclick="printReport()">🖨 Print</button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="filter-card">
            <div class="filter-row">
                <div class="filter-group">
                    <label>Start Date</label>
                    <input type="date" id="start_date">
                </div>
                <div class="filter-group">
                    <label>End Date</label>
                    <input type="date" id="end_date">
                </div>
                <div class="filter-group">
                    <label>Customer</label>
                    <select id="filterCustomer">
                        <option value="all">All Customers</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Warehouse</label>
                    <select id="filterWarehouse">
                        <option value="all">All Warehouses</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Status</label>
                    <select id="filterStatus">
                        <option value="all">All Status</option>
                        <option value="posted">Posted</option>
                        <option value="booked">Booked</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="returned">Returned</option>
                    </select>
                </div>
                <div class="filter-group" style="flex-direction:row;gap:8px;align-items:flex-end;">
                    <button class="btn-search" id="btnSearch">🔍 Search</button>
                    <button class="btn-reset" id="btnReset">↺ Reset</button>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="kpi-row" id="kpiRow" style="display:none;">
            <div class="kpi-card">
                <div class="kpi-label">Total Invoices</div>
                <div class="kpi-value" id="kpiCount">0</div>
            </div>
            <div class="kpi-card kpi-net">
                <div class="kpi-label">Total Net Amount</div>
                <div class="kpi-value" id="kpiNet">0</div>
            </div>
            <div class="kpi-card kpi-paid">
                <div class="kpi-label">Amount Received</div>
                <div class="kpi-value" id="kpiPaid">0</div>
            </div>
            <div class="kpi-card kpi-due">
                <div class="kpi-label">Amount Due</div>
                <div class="kpi-value" id="kpiDue">0</div>
            </div>
            <div class="kpi-card kpi-ret">
                <div class="kpi-label">Total Returned</div>
                <div class="kpi-value" id="kpiReturned">0</div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-card">
            <div class="loader-wrap" id="loader">
                <div class="spinner"></div>
                <p style="margin-top:10px;color:#94a3b8;font-size:.88rem;">Loading report data…</p>
            </div>

            <div class="empty-state" id="emptyState">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p>Select a date range and click <strong>Search</strong> to view the sale report.</p>
            </div>

            <div style="overflow-x:auto;display:none;" id="tableWrap">
                <table id="saleTable">
                    <thead>
                        <tr>
                            <th style="width:36px;">#</th>
                            <th>Date</th>
                            <th>Invoice No</th>
                            <th>Customer</th>
                            <th>Warehouse</th>
                            <th class="text-right">Subtotal</th>
                            <th class="text-right">Discount</th>
                            <th class="text-right">Net Amount</th>
                            <th class="text-right">Paid</th>
                            <th class="text-right">Due</th>
                            <th class="text-right">Returned</th>
                            <th>Status</th>
                            <th>Items</th>
                        </tr>
                    </thead>
                    <tbody id="saleBody"></tbody>
                    <tfoot id="saleFoot"></tfoot>
                </table>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const fmt = (n) => parseFloat(n || 0).toLocaleString('en-PK', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            function statusBadge(s) {
                const map = {
                    posted: '<span class="badge badge-posted">Posted</span>',
                    booked: '<span class="badge badge-booked">Booked</span>',
                    cancelled: '<span class="badge badge-cancelled">Cancelled</span>',
                    returned: '<span class="badge badge-returned">Returned</span>',
                };
                return map[s] || `<span class="badge">${s || '-'}</span>`;
            }

            function itemDisplay(it) {
                // Show quantity in a human-readable format based on size_mode
                if (it.size_mode === 'by_carton' || it.size_mode === 'by_cartons') {
                    return `${parseFloat(it.qty).toFixed(0)} boxes (${it.total_pieces} pcs)`;
                } else if (it.size_mode === 'by_size') {
                    return `${it.total_pieces} pcs`;
                }
                return parseFloat(it.qty).toFixed(2) + ' pcs';
            }

            function renderTable(data, totals) {
                document.getElementById('kpiCount').textContent = data.length;
                document.getElementById('kpiNet').textContent = 'Rs ' + fmt(totals.net);
                document.getElementById('kpiPaid').textContent = 'Rs ' + fmt(totals.paid);
                document.getElementById('kpiDue').textContent = 'Rs ' + fmt(totals.due);
                document.getElementById('kpiReturned').textContent = 'Rs ' + fmt(totals.returned);
                document.getElementById('kpiRow').style.display = '';

                let html = '';
                data.forEach((s, i) => {
                    const retBadge = s.return_count > 0 ?
                        `<span class="badge badge-returned" style="margin-left:4px;">${s.return_count} Return${s.return_count > 1 ? 's' : ''}</span>` :
                        '';

                    html += `
            <tr>
                <td>${i + 1}</td>
                <td>${s.created_at ? s.created_at.split(' ')[0] : '-'}</td>
                <td><strong>${s.invoice_no}</strong>${retBadge}</td>
                <td>${s.customer_name}<br><small style="color:#94a3b8;">${s.customer_phone}</small></td>
                <td>${s.warehouse_name}</td>
                <td class="text-right num">${fmt(s.subtotal)}</td>
                <td class="text-right num">${fmt(s.discount)}</td>
                <td class="text-right num" style="font-weight:700;">${fmt(s.total_net)}</td>
                <td class="text-right num" style="color:#10b981;">${fmt(s.paid)}</td>
                <td class="text-right num" style="color:#ef4444;">${fmt(s.due)}</td>
                <td class="text-right num" style="color:#f59e0b;">${s.total_returned > 0 ? fmt(s.total_returned) : '-'}</td>
                <td>${statusBadge(s.sale_status)}</td>
                <td><button class="btn-expand" data-idx="${i}">▶ ${s.items.length} item${s.items.length !== 1 ? 's' : ''}</button></td>
            </tr>
            <tr class="items-row" id="items-${i}">
                <td colspan="13" style="padding:0 12px 10px 32px;">
                    <table class="items-mini-table">
                        <thead><tr>
                            <th>Item Code</th><th>Item Name</th><th>Quantity</th>
                            <th class="text-right">Price/Box</th><th class="text-right">Price/Pcs</th>
                            <th class="text-right">Line Total</th>
                        </tr></thead>
                        <tbody>
                            ${(s.items || []).map(it => `
                                                        <tr>
                                                            <td>${it.item_code || '-'}</td>
                                                            <td>${it.item_name || '-'}</td>
                                                            <td>${itemDisplay(it)}</td>
                                                            <td class="text-right">${it.price > 0 ? fmt(it.price) : '-'}</td>
                                                            <td class="text-right">${it.price_per_piece > 0 ? fmt(it.price_per_piece) : '-'}</td>
                                                            <td class="text-right" style="font-weight:600;">${fmt(it.total)}</td>
                                                        </tr>`).join('')}
                        </tbody>
                    </table>
                </td>
            </tr>`;
                });

                // Grand total footer
                const foot = `<tr>
            <td colspan="5" class="text-right">Grand Total (${data.length} invoices):</td>
            <td></td><td></td>
            <td class="text-right">${fmt(totals.net)}</td>
            <td class="text-right" style="color:#10b981;">${fmt(totals.paid)}</td>
            <td class="text-right" style="color:#ef4444;">${fmt(totals.due)}</td>
            <td class="text-right" style="color:#f59e0b;">${fmt(totals.returned)}</td>
            <td colspan="2"></td>
        </tr>`;

                document.getElementById('saleBody').innerHTML = html;
                document.getElementById('saleFoot').innerHTML = foot;

                // expand toggle
                document.querySelectorAll('.btn-expand').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const idx = this.dataset.idx;
                        const row = document.getElementById('items-' + idx);
                        const open = row.classList.toggle('open');
                        this.textContent = (open ? '▼ ' : '▶ ') + this.textContent.slice(2);
                    });
                });
            }

            let lastData = [];

            function fetchReport() {
                const start = document.getElementById('start_date').value;
                const end = document.getElementById('end_date').value;
                const customer = document.getElementById('filterCustomer').value;
                const wh = document.getElementById('filterWarehouse').value;
                const status = document.getElementById('filterStatus').value;

                document.getElementById('loader').style.display = '';
                document.getElementById('tableWrap').style.display = 'none';
                document.getElementById('emptyState').style.display = 'none';
                document.getElementById('kpiRow').style.display = 'none';

                const params = new URLSearchParams({
                    start_date: start,
                    end_date: end,
                    customer_id: customer,
                    warehouse_id: wh,
                    status
                });

                fetch(`{{ route('report.sale.fetch') }}?${params}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(r => r.json())
                    .then(res => {
                        document.getElementById('loader').style.display = 'none';

                        // Populate dropdowns once
                        if (res.customers && document.getElementById('filterCustomer').options.length === 1) {
                            res.customers.forEach(c => {
                                const o = new Option(c.customer_name, c.id);
                                document.getElementById('filterCustomer').add(o);
                            });
                        }
                        if (res.warehouses && document.getElementById('filterWarehouse').options.length === 1) {
                            res.warehouses.forEach(w => {
                                const o = new Option(w.warehouse_name, w.id);
                                document.getElementById('filterWarehouse').add(o);
                            });
                        }

                        lastData = res.data || [];
                        if (!lastData.length) {
                            document.getElementById('emptyState').style.display = '';
                            document.getElementById('emptyState').querySelector('p').textContent =
                                'No sale records found for the selected filters.';
                            return;
                        }

                        document.getElementById('tableWrap').style.display = '';
                        renderTable(lastData, {
                            net: res.grand_net,
                            paid: res.grand_paid,
                            due: res.grand_due,
                            returned: res.grand_returned,
                        });
                    })
                    .catch(err => {
                        document.getElementById('loader').style.display = 'none';
                        console.error(err);
                        alert('Error fetching sale report. Please try again.');
                    });
            }

            document.getElementById('btnSearch').addEventListener('click', fetchReport);

            document.getElementById('btnReset').addEventListener('click', function() {
                document.getElementById('start_date').value = '';
                document.getElementById('end_date').value = '';
                document.getElementById('filterCustomer').value = 'all';
                document.getElementById('filterWarehouse').value = 'all';
                document.getElementById('filterStatus').value = 'all';
                document.getElementById('tableWrap').style.display = 'none';
                document.getElementById('emptyState').style.display = '';
                document.getElementById('kpiRow').style.display = 'none';
                document.getElementById('emptyState').querySelector('p').innerHTML =
                    'Select a date range and click <strong>Search</strong> to view the sale report.';
                lastData = [];
            });

            // CSV Export
            document.getElementById('btnExportCsv').addEventListener('click', function() {
                if (!lastData.length) {
                    alert('No data to export. Run a search first.');
                    return;
                }
                let csv =
                    'Invoice No,Date,Customer,Warehouse,Subtotal,Discount,Net Amount,Paid,Due,Returned,Status,Item Code,Item Name,Qty,Total Pieces,Price/Box,Price/Pcs,Line Total\n';
                lastData.forEach(s => {
                    if (s.items.length === 0) {
                        csv +=
                            `"${s.invoice_no}","${(s.created_at||'').split(' ')[0]}","${s.customer_name}","${s.warehouse_name}",${s.subtotal},${s.discount},${s.total_net},${s.paid},${s.due},${s.total_returned},"${s.sale_status}","","","","","","",""` +
                            '\n';
                    } else {
                        s.items.forEach((it, ii) => {
                            const fin = ii === 0 ?
                                `${s.subtotal},${s.discount},${s.total_net},${s.paid},${s.due},${s.total_returned},"${s.sale_status}"` :
                                ',,,,,,,';
                            csv +=
                                `"${ii === 0 ? s.invoice_no : ''}","${ii === 0 ? (s.created_at||'').split(' ')[0] : ''}","${ii === 0 ? s.customer_name : ''}","${ii === 0 ? s.warehouse_name : ''}",${fin},"${it.item_code}","${it.item_name}",${it.qty},${it.total_pieces},${it.price},${it.price_per_piece},${it.total}` +
                                '\n';
                        });
                    }
                });
                const blob = new Blob([csv], {
                    type: 'text/csv;charset=utf-8;'
                });
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'sale_report_{{ now()->format('Ymd') }}.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            });

            // Use LOCAL date (not UTC) to avoid timezone cutoff at night
            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            const today = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate());
            const firstOfMonth = today.slice(0, 7) + '-01';
            document.getElementById('start_date').value = firstOfMonth;
            document.getElementById('end_date').value = today;
            fetchReport();
        })();

        function printReport() {
            document.querySelectorAll('.items-row').forEach(r => r.classList.add('open'));
            const start = document.getElementById('start_date').value;
            const end = document.getElementById('end_date').value;
            const cust = document.getElementById('filterCustomer');
            const cName = cust ? cust.options[cust.selectedIndex]?.text : 'All Customers';
            const sub = document.getElementById('printSubtitle');
            if (sub) sub.textContent =
                `Period: ${start} to ${end}  |  Customer: ${cName}  |  Printed: {{ now()->format('d M Y H:i') }}`;
            window.print();
            setTimeout(() => document.querySelectorAll('.items-row').forEach(r => r.classList.remove('open')), 1000);
        }
    </script>
@endsection
