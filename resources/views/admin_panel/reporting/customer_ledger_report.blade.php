@extends('admin_panel.layout.app')
@section('content')
    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --bg: #f8fafc;
            --white: #ffffff;
            --brand: #4f46e5;
            --brand-light: #ede9fe;
            --green: #10b981;
            --green-lt: #d1fae5;
            --red: #ef4444;
            --red-lt: #fee2e2;
            --amber: #f59e0b;
            --amber-lt: #fef3c7;
            --sky: #0ea5e9;
            --sky-lt: #e0f2fe;
        }

        .led-page {
            padding: 20px;
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        /* ── Top bar ─────────────────────────────────────────────── */
        .led-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .led-topbar h4 {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--ink);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .led-topbar p {
            margin: 0;
            color: var(--muted);
            font-size: .85rem;
        }

        .topbar-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* Buttons */
        .btn-led {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: .83rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: filter .15s;
        }

        .btn-led:hover {
            filter: brightness(.92);
        }

        .btn-gen {
            background: var(--brand);
            color: #fff;
        }

        .btn-print {
            background: var(--sky);
            color: #fff;
        }

        .btn-csv {
            background: var(--green);
            color: #fff;
        }

        .btn-reset-form {
            background: var(--bg);
            color: var(--ink);
            border: 1px solid var(--border);
        }

        /* ── Filter card ─────────────────────────────────────────── */
        .filter-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px 22px;
            margin-bottom: 20px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 14px;
            align-items: flex-end;
        }

        .fg label {
            font-size: .73rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--muted);
            display: block;
            margin-bottom: 5px;
        }

        .fg select,
        .fg input {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 9px 12px;
            font-size: .88rem;
            color: var(--ink);
            background: var(--bg);
            outline: none;
            transition: border-color .15s, background .15s;
            box-sizing: border-box;
        }

        .fg select:focus,
        .fg input:focus {
            border-color: var(--brand);
            background: var(--white);
            box-shadow: 0 0 0 3px #ede9fe80;
        }

        .generate-btn-wrap {
            padding-bottom: 0;
        }

        @media (max-width:768px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ── Loader ──────────────────────────────────────────────── */
        .led-loader {
            display: none;
            text-align: center;
            padding: 50px;
        }

        .spinner {
            width: 38px;
            height: 38px;
            border: 4px solid var(--brand-light);
            border-top-color: var(--brand);
            border-radius: 50%;
            animation: spin .7s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ── Result area ─────────────────────────────────────────── */
        #ledgerResult {
            display: none;
        }

        /* ── Customer profile card ───────────────────────────────── */
        .cust-profile {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 12px;
            padding: 22px 26px;
            margin-bottom: 18px;
            color: white;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 16px;
            align-items: center;
        }

        .cust-profile h5 {
            font-size: 1.25rem;
            font-weight: 800;
            margin: 0 0 4px;
        }

        .cust-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 8px;
        }

        .cust-meta span {
            font-size: .8rem;
            opacity: .85;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .cust-badge {
            background: rgba(255, 255, 255, .18);
            border-radius: 8px;
            padding: 4px 12px;
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: .4px;
            border: 1px solid rgba(255, 255, 255, .25);
        }

        .period-badge {
            background: rgba(255, 255, 255, .15);
            border-radius: 8px;
            padding: 6px 14px;
            font-size: .8rem;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, .2);
            white-space: nowrap;
            margin-top: 4px;
        }

        /* ── KPI row ─────────────────────────────────────────────── */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 13px;
            margin-bottom: 20px;
        }

        .kpi-box {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 11px;
            padding: 15px 17px;
            position: relative;
            overflow: hidden;
        }

        .kpi-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .kpi-box.k-blue::before {
            background: var(--brand);
        }

        .kpi-box.k-green::before {
            background: var(--green);
        }

        .kpi-box.k-red::before {
            background: var(--red);
        }

        .kpi-box.k-amber::before {
            background: var(--amber);
        }

        .kpi-box.k-sky::before {
            background: var(--sky);
        }

        .kpi-lbl {
            font-size: .71rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .55px;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .kpi-val {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--ink);
        }

        .kpi-sub {
            font-size: .73rem;
            color: var(--muted);
            margin-top: 2px;
        }

        /* ── Table card ──────────────────────────────────────────── */
        .tbl-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .tbl-card table {
            width: 100%;
            border-collapse: collapse;
            font-size: .84rem;
        }

        .tbl-card thead tr {
            background: #f1f5f9;
        }

        .tbl-card thead th {
            padding: 11px 14px;
            text-align: left;
            font-size: .72rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .45px;
            white-space: nowrap;
            border-bottom: 2px solid var(--border);
        }

        .tbl-card thead th.tr {
            text-align: right;
        }

        .tbl-card tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background .1s;
        }

        .tbl-card tbody tr:hover {
            background: #fafaff;
        }

        .tbl-card tbody td {
            padding: 10px 14px;
            vertical-align: middle;
            color: #334155;
        }

        .tbl-card tbody td.tr {
            text-align: right;
        }

        /* Opening / closing special rows */
        .row-opening {
            background: #f0fdf4 !important;
        }

        .row-total {
            background: #f8fafc !important;
            border-top: 2px solid var(--border) !important;
        }

        .row-closing {
            background: #eff6ff !important;
            border-top: 2px solid var(--border) !important;
        }

        .row-opening td,
        .row-total td,
        .row-closing td {
            font-weight: 700 !important;
        }

        /* Transaction type icons */
        .tx-type {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 18px;
            font-size: .7rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .tx-sale {
            background: var(--brand-light);
            color: var(--brand);
        }

        .tx-receipt {
            background: var(--green-lt);
            color: #065f46;
        }

        .tx-return {
            background: var(--amber-lt);
            color: #92400e;
        }

        .tx-journal {
            background: var(--sky-lt);
            color: #0369a1;
        }

        /* Balance cell */
        .bal-dr {
            color: #dc2626;
            font-weight: 700;
        }

        .bal-cr {
            color: #16a34a;
            font-weight: 700;
        }

        .bal-zero {
            color: var(--muted);
        }

        /* Amount cols */
        .amt-dr {
            color: #dc2626;
            font-weight: 600;
        }

        .amt-cr {
            color: #16a34a;
            font-weight: 600;
        }

        .amt-nil {
            color: #cbd5e1;
        }

        /* Invoice badge */
        .inv-badge {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 2px 8px;
            font-size: .73rem;
            color: var(--ink);
            font-weight: 600;
            font-family: monospace;
        }

        /* Empty state */
        .empty-ledger {
            text-align: center;
            padding: 60px;
            color: var(--muted);
        }

        .empty-ledger svg {
            width: 52px;
            opacity: .3;
            margin-bottom: 12px;
        }

        /* Bottom bar */
        .bottom-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 14px;
            flex-wrap: wrap;
            gap: 8px;
        }

        .bottom-bar small {
            font-size: .78rem;
            color: var(--muted);
        }

        /* Print: hide all led-page children, show only ledgerResult */
        @media print {
            .led-page>* {
                display: none !important;
            }

            #ledgerResult {
                display: block !important;
            }

            /* Inside ledgerResult: hide all, show only print-header + tbl-card */
            #ledgerResult>* {
                display: none !important;
            }

            #ledgerResult>.print-header,
            #ledgerResult>.tbl-card {
                display: block !important;
            }

            .tbl-card {
                border: none !important;
            }

            .print-header {
                display: block !important;
            }
        }
    </style>

    <div class="led-page">

        {{-- Top Bar --}}
        <div class="led-topbar">
            <div>
                <h4>
                    <svg style="width:22px;height:22px;color:#4f46e5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Customer Ledger Report
                </h4>
                <p>Detailed statement of account — debits, credits & running balance</p>
            </div>
            <div class="topbar-actions" id="exportBtns" style="display:none;">
                <button class="btn-led btn-csv" id="btnCsv">⬇ Export CSV</button>
                <button class="btn-led btn-print" onclick="printReport()">🖨 Print</button>
            </div>
        </div>

        {{-- Filter Card --}}
        <div class="filter-card">
            <div class="filter-grid">
                <div class="fg">
                    <label>Customer</label>
                    <select id="sel_customer">
                        <option value="">— Select Customer —</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fg">
                    <label>Start Date</label>
                    <input type="date" id="sel_start">
                </div>
                <div class="fg">
                    <label>End Date</label>
                    <input type="date" id="sel_end">
                </div>
                <div class="fg generate-btn-wrap">
                    <button class="btn-led btn-gen w-100" id="btnGenerate"
                        style="width:100%;justify-content:center;padding:9px 20px;">
                        🔍 Generate
                    </button>
                </div>
            </div>
        </div>

        {{-- Loader --}}
        <div class="led-loader" id="ledLoader">
            <div class="spinner"></div>
            <p style="margin-top:10px;color:var(--muted);font-size:.88rem;">Building ledger report…</p>
        </div>

        {{-- Result --}}
        <div id="ledgerResult" style="display:none;">

            {{-- Print Header (only visible when printing) --}}
            <div class="print-header" style="display:none; margin-bottom:16px;">
                <h2 style="margin:0;font-size:18px;font-weight:700;">📄 Customer Ledger Report</h2>
                <p id="printLedgerSubtitle" style="margin:4px 0 0;font-size:12px;color:#555;">Printed:
                    {{ now()->format('d M Y H:i') }}</p>
            </div>

            {{-- Customer Profile --}}
            <div class="cust-profile" id="custProfile"></div>

            {{-- KPI Row --}}
            <div class="kpi-row" id="kpiRow"></div>

            {{-- Ledger Table --}}
            <div class="tbl-card">
                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:110px;">Date</th>
                                <th style="width:120px;">Ref / Invoice</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th class="tr">Debit (Dr)</th>
                                <th class="tr">Credit (Cr)</th>
                                <th class="tr" style="width:150px;">Running Balance</th>
                            </tr>
                        </thead>
                        <tbody id="ledgerBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="bottom-bar">
                <small id="genTime"></small>
                <small id="txCount"></small>
            </div>
        </div>

    </div>

    <script>
        (function() {
            const fmt = n => parseFloat(n || 0).toLocaleString('en-PK', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            // Auto set dates to current month
            // Use LOCAL date (not UTC) to avoid timezone cutoff at night
            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            const today = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate());
            const firstOfMonth = today.slice(0, 7) + '-01';
            document.getElementById('sel_start').value = firstOfMonth;
            document.getElementById('sel_end').value = today;

            function txBadge(type) {
                const map = {
                    sale: '<span class="tx-type tx-sale">💰 Sale</span>',
                    receipt: '<span class="tx-type tx-receipt">✔ Receipt</span>',
                    return: '<span class="tx-type tx-return">↩ Return</span>',
                    discount: '<span class="tx-type tx-return">% Discount</span>',
                    journal: '<span class="tx-type tx-journal">📖 Journal</span>',
                };
                return map[type] || map.journal;
            }

            function balClass(b) {
                if (Math.abs(b) < 0.01) return 'bal-zero';
                return b > 0 ? 'bal-dr' : 'bal-cr';
            }

            let lastRes = null;

            document.getElementById('btnGenerate').addEventListener('click', function() {
                const cid = document.getElementById('sel_customer').value;
                const start = document.getElementById('sel_start').value;
                const end = document.getElementById('sel_end').value;

                if (!cid || !start || !end) {
                    alert('Please select a customer and date range.');
                    return;
                }

                document.getElementById('ledLoader').style.display = 'block';
                document.getElementById('ledgerResult').style.display = 'none';
                document.getElementById('exportBtns').style.display = 'none';

                const params = new URLSearchParams({
                    customer_id: cid,
                    start_date: start,
                    end_date: end
                });

                fetch(`{{ route('report.customer.ledger.fetch') }}?${params}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(r => r.json())
                    .then(res => {
                        document.getElementById('ledLoader').style.display = 'none';
                        if (res.error) {
                            alert(res.error);
                            return;
                        }

                        lastRes = res;
                        renderLedger(res, start, end);

                        document.getElementById('ledgerResult').style.display = 'block';
                        document.getElementById('exportBtns').style.display = 'flex';
                    })
                    .catch(err => {
                        document.getElementById('ledLoader').style.display = 'none';
                        console.error(err);
                        alert('Failed to fetch ledger. Please try again.');
                    });
            });

            function renderLedger(res, start, end) {
                const c = res.customer;
                const ob = parseFloat(res.opening_balance || 0);
                const cb = parseFloat(res.closing_balance || 0);
                const txList = res.transactions || [];

                // ── Customer Profile ──────────────────────────────────────────
                document.getElementById('custProfile').innerHTML = `
            <div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px;">
                    <span style="font-size:1.4rem;font-weight:900;">${c.customer_name}</span>
                    <span class="cust-badge">${c.customer_type}</span>
                    <span class="cust-badge">${c.customer_id}</span>
                </div>
                <div class="cust-meta">
                    <span>📱 ${c.mobile}</span>
                    <span>📍 ${c.address}</span>
                    <span>💳 Opening Balance: Rs ${fmt(c.opening_balance)}</span>
                </div>
            </div>
            <div style="text-align:right;">
                <div class="period-badge">📅 ${start} → ${end}</div>
            </div>
        `;

                // ── KPI Cards ─────────────────────────────────────────────────
                const netBal = cb;
                const balLabel = netBal >= 0 ? 'Receivable (Dr)' : 'Advance/Credit (Cr)';
                const balKlass = netBal >= 0 ? 'k-red' : 'k-green';

                document.getElementById('kpiRow').innerHTML = `
            <div class="kpi-box k-blue">
                <div class="kpi-lbl">Opening Balance</div>
                <div class="kpi-val">Rs ${fmt(ob)}</div>
                <div class="kpi-sub">${ob >= 0 ? 'Receivable' : 'Credit/Advance'}</div>
            </div>
            <div class="kpi-box k-sky">
                <div class="kpi-lbl">Total Invoiced (Dr)</div>
                <div class="kpi-val" style="color:var(--red)">Rs ${fmt(res.total_debit)}</div>
                <div class="kpi-sub">Charged to customer</div>
            </div>
            <div class="kpi-box k-green">
                <div class="kpi-lbl">Total Received (Cr)</div>
                <div class="kpi-val" style="color:var(--green)">Rs ${fmt(res.total_credit)}</div>
                <div class="kpi-sub">Payments collected</div>
            </div>
            <div class="kpi-box k-amber">
                <div class="kpi-lbl">Transactions</div>
                <div class="kpi-val">${txList.length}</div>
                <div class="kpi-sub">In period</div>
            </div>
            <div class="kpi-box ${balKlass}">
                <div class="kpi-lbl">Closing Balance</div>
                <div class="kpi-val" style="color:${netBal >= 0 ? 'var(--red)' : 'var(--green)'}">Rs ${fmt(Math.abs(cb))}</div>
                <div class="kpi-sub">${balLabel}</div>
            </div>
        `;

                // ── Ledger Table ──────────────────────────────────────────────
                let html = '';

                // Opening row
                const obClass = ob >= 0 ? 'bal-dr' : (ob < 0 ? 'bal-cr' : 'bal-zero');
                const obLabel = ob >= 0 ? 'Dr' : 'Cr';
                html += `
            <tr class="row-opening">
                <td>—</td>
                <td>—</td>
                <td><span class="tx-type tx-journal">B/F</span></td>
                <td>Opening Balance Brought Forward</td>
                <td class="tr amt-nil">—</td>
                <td class="tr amt-nil">—</td>
                <td class="tr ${obClass}">
                    Rs ${fmt(Math.abs(ob))}
                    <small style="font-size:.7em;opacity:.7">${obLabel}</small>
                </td>
            </tr>`;

                if (txList.length === 0) {
                    html += `<tr><td colspan="7">
                <div class="empty-ledger">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p>No transactions found for this period.</p>
                </div>
            </td></tr>`;
                } else {
                    txList.forEach((t, i) => {
                        const dr = parseFloat(t.debit || 0);
                        const cr = parseFloat(t.credit || 0);
                        const bal = parseFloat(t.balance || 0);
                        const bc = balClass(bal);
                        const bl = bal > 0.01 ? 'Dr' : (bal < -0.01 ? 'Cr' : '—');

                        html += `
                    <tr>
                        <td style="color:var(--muted);font-size:.82rem;">${(t.date||'').split(' ')[0]}</td>
                        <td>
                            ${t.invoice && t.invoice !== '-'
                                ? `<span class="inv-badge">${t.invoice}</span>`
                                : `<span style="color:var(--muted)">—</span>`}
                        </td>
                        <td>${txBadge(t.type)}</td>
                        <td style="max-width:280px;font-size:.82rem;">${t.description}</td>
                        <td class="tr ${dr > 0 ? 'amt-dr' : 'amt-nil'}">${dr > 0 ? 'Rs ' + fmt(dr) : '—'}</td>
                        <td class="tr ${cr > 0 ? 'amt-cr' : 'amt-nil'}">${cr > 0 ? 'Rs ' + fmt(cr) : '—'}</td>
                        <td class="tr ${bc}">
                            Rs ${fmt(Math.abs(bal))}
                            <small style="font-size:.7em;opacity:.7">${bl}</small>
                        </td>
                    </tr>`;
                    });
                }

                // Period Total row
                html += `
            <tr class="row-total">
                <td colspan="3" style="text-align:right;color:var(--muted);">Period Totals:</td>
                <td style="color:var(--muted);font-size:.8rem;">${txList.length} transaction${txList.length !== 1 ? 's' : ''}</td>
                <td class="tr amt-dr">Rs ${fmt(res.total_debit)}</td>
                <td class="tr amt-cr">Rs ${fmt(res.total_credit)}</td>
                <td class="tr">—</td>
            </tr>`;

                // Closing Balance row
                const cbClass = cb >= 0 ? 'bal-dr' : 'bal-cr';
                const cbLabel = cb > 0.01 ? 'Receivable (Dr)' : (cb < -0.01 ? 'Advance (Cr)' : 'Settled');
                html += `
            <tr class="row-closing">
                <td colspan="4" style="text-align:right;">
                    Closing Balance as of <strong>${end}</strong>:
                </td>
                <td colspan="2"></td>
                <td class="tr ${cbClass}" style="font-size:1rem;">
                    Rs ${fmt(Math.abs(cb))}
                    <div style="font-size:.72rem;font-weight:500;opacity:.75;">${cbLabel}</div>
                </td>
            </tr>`;

                document.getElementById('ledgerBody').innerHTML = html;
                document.getElementById('genTime').textContent = '🕐 Generated: ' + new Date().toLocaleString('en-PK');
                document.getElementById('txCount').textContent = `${txList.length} transaction(s) in period`;
            }

            // CSV Export
            document.getElementById('btnCsv')?.addEventListener('click', function() {
                if (!lastRes) return;
                const txList = lastRes.transactions || [];
                let csv = 'Date,Ref/Invoice,Type,Description,Debit (Dr),Credit (Cr),Balance\n';
                csv +=
                    `-,-,Opening Balance,Opening Balance B/F,-,-,${parseFloat(lastRes.opening_balance).toFixed(2)}\n`;
                txList.forEach(t => {
                    csv +=
                        `"${(t.date||'').split(' ')[0]}","${t.invoice}","${t.type}","${t.description.replace(/"/g,'""')}",`;
                    csv += `${t.debit > 0 ? parseFloat(t.debit).toFixed(2) : ''},`;
                    csv += `${t.credit > 0 ? parseFloat(t.credit).toFixed(2) : ''},`;
                    csv += `${parseFloat(t.balance).toFixed(2)}\n`;
                });
                csv +=
                    `,,Period Total,,${parseFloat(lastRes.total_debit).toFixed(2)},${parseFloat(lastRes.total_credit).toFixed(2)},\n`;
                csv += `,,Closing Balance,,,,${parseFloat(lastRes.closing_balance).toFixed(2)}\n`;

                const a = document.createElement('a');
                a.href = URL.createObjectURL(new Blob([csv], {
                    type: 'text/csv;charset=utf-8;'
                }));
                const custName = (lastRes.customer?.customer_name || 'customer').replace(/\s+/g, '_');
                a.download = `ledger_${custName}_{{ now()->format('Ymd') }}.csv`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            });

        })();

        function printReport() {
            // Make sure ledger result is visible
            document.getElementById('ledgerResult').style.display = 'block';
            window.print();
        }
    </script>
@endsection
