@extends('admin_panel.layout.app')

@section('content')
    <style>
        :root {
            --dash-primary: #6366f1;
            --dash-success: #22c55e;
            --dash-warning: #f59e0b;
            --dash-danger: #ef4444;
            --dash-info: #0ea5e9;
            --dash-purple: #8b5cf6;
            --dash-bg: #f8fafc;
            --dash-card: #ffffff;
            --dash-border: #e2e8f0;
            --dash-text: #1e293b;
            --dash-muted: #64748b;
        }

        .dashboard-container {
            padding: 0;
        }

        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
            border-radius: 20px;
            padding: 32px 40px;
            color: white;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .welcome-section::after {
            content: '';
            position: absolute;
            bottom: -30%;
            right: 10%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .welcome-content {
            position: relative;
            z-index: 1;
        }

        .welcome-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .welcome-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 20px;
        }

        .welcome-date {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.9rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--dash-card);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--dash-border);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .stat-card.primary::before {
            background: linear-gradient(180deg, #6366f1, #8b5cf6);
        }

        .stat-card.success::before {
            background: linear-gradient(180deg, #22c55e, #16a34a);
        }

        .stat-card.warning::before {
            background: linear-gradient(180deg, #f59e0b, #d97706);
        }

        .stat-card.danger::before {
            background: linear-gradient(180deg, #ef4444, #dc2626);
        }

        .stat-card.info::before {
            background: linear-gradient(180deg, #0ea5e9, #0284c7);
        }

        .stat-card.purple::before {
            background: linear-gradient(180deg, #8b5cf6, #7c3aed);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .stat-card.primary .stat-icon {
            background: #eef2ff;
            color: #6366f1;
        }

        .stat-card.success .stat-icon {
            background: #dcfce7;
            color: #22c55e;
        }

        .stat-card.warning .stat-icon {
            background: #fef3c7;
            color: #f59e0b;
        }

        .stat-card.danger .stat-icon {
            background: #fee2e2;
            color: #ef4444;
        }

        .stat-card.info .stat-icon {
            background: #e0f2fe;
            color: #0ea5e9;
        }

        .stat-card.purple .stat-icon {
            background: #f3e8ff;
            color: #8b5cf6;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.8rem;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .stat-trend.up {
            background: #dcfce7;
            color: #16a34a;
        }

        .stat-trend.down {
            background: #fee2e2;
            color: #dc2626;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--dash-text);
            line-height: 1.2;
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--dash-muted);
            margin-top: 4px;
        }

        /* Chart Cards */
        .chart-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }

        .chart-card {
            background: var(--dash-card);
            border-radius: 16px;
            border: 1px solid var(--dash-border);
            overflow: hidden;
        }

        .chart-card.full-width {
            grid-column: span 2;
        }

        .chart-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--dash-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dash-text);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-title i {
            color: var(--dash-primary);
        }

        .chart-filter {
            display: flex;
            gap: 8px;
        }

        .filter-btn {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            border: 1px solid var(--dash-border);
            background: white;
            color: var(--dash-muted);
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: var(--dash-primary);
            color: white;
            border-color: var(--dash-primary);
        }

        .chart-body {
            padding: 24px;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .action-card {
            background: var(--dash-card);
            border: 1px solid var(--dash-border);
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            transition: all 0.2s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.15);
            border-color: var(--dash-primary);
        }

        .action-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 1.2rem;
        }

        .action-card.sales .action-icon {
            background: #dcfce7;
            color: #22c55e;
        }

        .action-card.purchase .action-icon {
            background: #e0f2fe;
            color: #0ea5e9;
        }

        .action-card.products .action-icon {
            background: #fef3c7;
            color: #f59e0b;
        }

        .action-card.hr .action-icon {
            background: #f3e8ff;
            color: #8b5cf6;
        }

        .action-title {
            font-weight: 600;
            color: var(--dash-text);
            font-size: 0.95rem;
        }

        .action-desc {
            font-size: 0.8rem;
            color: var(--dash-muted);
            margin-top: 4px;
        }

        /* Summary Cards Row */
        .summary-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }

        .summary-card {
            background: var(--dash-card);
            border: 1px solid var(--dash-border);
            border-radius: 14px;
            padding: 20px;
        }

        .summary-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .summary-title {
            font-size: 0.85rem;
            color: var(--dash-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .summary-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dash-text);
        }

        .summary-change {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            margin-top: 6px;
        }

        .summary-change.positive {
            color: #22c55e;
        }

        .summary-change.negative {
            color: #ef4444;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .chart-section {
                grid-template-columns: 1fr;
            }

            .chart-card.full-width {
                grid-column: span 1;
            }

            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }

            .summary-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }

            .summary-row {
                grid-template-columns: 1fr;
            }

            .welcome-section {
                padding: 24px;
            }
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container dashboard-container">

                <!-- Welcome Section -->
                <div class="welcome-section">
                    <div class="welcome-content">
                        <h1 class="welcome-title">Welcome back, {{ auth()->user()->name ?? 'Admin' }}! 👋</h1>
                        <p class="welcome-subtitle">Here's what's happening with your business today.</p>
                        <div class="welcome-date">
                            <i class="fa fa-calendar-alt"></i>
                            {{ now()->format('l, F d, Y') }}
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    @can('sales.create')
                        <a href="{{ route('sale.index') }}" class="action-card sales">
                            <div class="action-icon"><i class="fa fa-shopping-cart"></i></div>
                            <div class="action-title">New Sale</div>
                            <div class="action-desc">Create invoice</div>
                        </a>
                    @endcan

                    @can('purchases.create')
                        <a href="{{ route('Purchase.home') }}" class="action-card purchase">
                            <div class="action-icon"><i class="fa fa-truck"></i></div>
                            <div class="action-title">New Purchase</div>
                            <div class="action-desc">Add stock</div>
                        </a>
                    @endcan

                    @can('products.view')
                        <a href="{{ route('product') }}" class="action-card products">
                            <div class="action-icon"><i class="fa fa-box"></i></div>
                            <div class="action-title">Products</div>
                            <div class="action-desc">Manage inventory</div>
                        </a>
                    @endcan

                    @can('hr.employees.view')
                        <a href="{{ route('hr.employees.index') }}" class="action-card hr">
                            <div class="action-icon"><i class="fa fa-users"></i></div>
                            <div class="action-title">HR Module</div>
                            <div class="action-desc">Manage employees</div>
                        </a>
                    @endcan
                </div>

                <!-- Financial Health (Accounting Based) -->
                @if (isset($financialSummary) && !empty($financialSummary))
                    <h5 class="mb-3 text-muted d-flex align-items-center gap-2"
                        style="font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fas fa-heartbeat me-1" style="color: #6366f1;"></i>
                        Financial Health (This Month)
                        <button type="button"
                            class="btn btn-sm btn-outline-info d-flex align-items-center gap-1 rounded-pill px-3 shadow-none"
                            data-toggle="modal" data-target="#financialHealthModal" title="What do these mean?">
                            <i class="fas fa-info-circle"></i> Info
                        </button>
                    </h5>

                    @php
                        $sales = abs($financialSummary['sales'] ?? 0);
                        $cogs = abs($financialSummary['cogs'] ?? 0);
                        $expenses = abs($financialSummary['expenses'] ?? 0);
                        $profit = $financialSummary['net_profit'] ?? 0;
                        $recv = abs($financialSummary['receivables'] ?? 0);
                        $advances = abs($financialSummary['customer_advances'] ?? 0);
                        $pay = abs($financialSummary['payables'] ?? 0);
                        $isLoss = $profit < 0;
                        $totalExpense = $cogs + $expenses;
                    @endphp

                    <div class="stats-grid mb-4" style="grid-template-columns: repeat(4, 1fr);">

                        {{-- CARD 1: Sales Revenue --}}
                        <div class="stat-card success" style="cursor:default;">
                            <div class="stat-header">
                                <div class="stat-icon"><i class="fa fa-hand-holding-usd"></i></div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="stat-trend up">Revenue</div>
                                    <button class="btn btn-sm btn-light rounded-pill px-2 py-0 shadow-sm"
                                        style="font-size:0.7rem;border:1px solid #e2e8f0;" data-fh-modal="salesModal"
                                        title="Detailed Analytics">
                                        <i class="fas fa-chart-pie me-1"></i>Details
                                    </button>
                                </div>
                            </div>
                            <div class="stat-value" style="font-size:1.4rem;">Rs {{ number_format($sales, 0) }}</div>
                            <div class="stat-label mb-2">Sales Revenue</div>
                            <div id="mini-sales" style="height:55px;"></div>
                        </div>

                        {{-- CARD 2: COGS --}}
                        <div class="stat-card warning">
                            <div class="stat-header">
                                <div class="stat-icon" style="background:#fef3c7;color:#f59e0b;"><i
                                        class="fa fa-box-open"></i></div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="stat-trend down">Cost</div>
                                    <button class="btn btn-sm btn-light rounded-pill px-2 py-0 shadow-sm"
                                        style="font-size:0.7rem;border:1px solid #e2e8f0;" data-fh-modal="cogsModal"
                                        title="Detailed Analytics">
                                        <i class="fas fa-chart-pie me-1"></i>Details
                                    </button>
                                </div>
                            </div>
                            <div class="stat-value" style="font-size:1.4rem;">Rs {{ number_format($cogs, 0) }}</div>
                            <div class="stat-label mb-2">Cost of Goods Sold</div>
                            <div id="mini-cogs" style="height:55px;"></div>
                        </div>

                        {{-- CARD 3: Expenses --}}
                        <div class="stat-card danger">
                            <div class="stat-header">
                                <div class="stat-icon"><i class="fa fa-money-bill-wave"></i></div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="stat-trend down">Expense</div>
                                    <button class="btn btn-sm btn-light rounded-pill px-2 py-0 shadow-sm"
                                        style="font-size:0.7rem;border:1px solid #e2e8f0;" data-fh-modal="expenseModal"
                                        title="Detailed Analytics">
                                        <i class="fas fa-chart-pie me-1"></i>Details
                                    </button>
                                </div>
                            </div>
                            <div class="stat-value" style="font-size:1.4rem;">Rs {{ number_format($expenses, 0) }}</div>
                            <div class="stat-label mb-2">Operating Expenses</div>
                            <div id="mini-expenses" style="height:55px;"></div>
                        </div>

                        {{-- CARD 4: Net Profit/Loss --}}
                        <div class="stat-card {{ $isLoss ? 'danger' : 'success' }}"
                            style="background:{{ $isLoss ? '#fff5f5' : '#f0fdf4' }};border:1px solid {{ $isLoss ? '#fecaca' : '#bbf7d0' }};">
                            <div class="stat-header">
                                <div class="stat-icon"
                                    style="background:{{ $isLoss ? '#fef2f2' : '#dcfce7' }};color:{{ $isLoss ? '#ef4444' : '#22c55e' }};">
                                    <i class="fa {{ $isLoss ? 'fa-arrow-down' : 'fa-chart-line' }}"></i>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="stat-trend {{ $isLoss ? 'down' : 'up' }}">Bottom Line</div>
                                    <button class="btn btn-sm btn-light rounded-pill px-2 py-0 shadow-sm"
                                        style="font-size:0.7rem;border:1px solid #e2e8f0;" data-fh-modal="profitModal"
                                        title="Detailed Analytics">
                                        <i class="fas fa-chart-pie me-1"></i>Details
                                    </button>
                                </div>
                            </div>
                            <div class="stat-value"
                                style="font-size:1.4rem;color:{{ $isLoss ? '#ef4444' : '#22c55e' }};">
                                Rs {{ number_format(abs($profit), 0) }}
                            </div>
                            <div class="stat-label mb-2 fw-bold text-dark">{{ $isLoss ? 'Net Loss' : 'Net Profit' }}</div>
                            <div id="mini-profit" style="height:55px;"></div>
                        </div>

                        {{-- CARD 5: Receivables --}}
                        <div class="stat-card info">
                            <div class="stat-header">
                                <div class="stat-icon"><i class="fa fa-user-clock"></i></div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="stat-trend">Assets</div>
                                    <a href="{{ route('customers.ledger') }}"
                                        class="btn btn-sm btn-light rounded-pill px-2 py-0 shadow-sm"
                                        style="font-size:0.7rem;border:1px solid #e2e8f0;" title="Go to Customer Ledger">
                                        <i class="fas fa-external-link-alt me-1"></i>Ledger
                                    </a>
                                    <button class="btn btn-sm btn-light rounded-pill px-2 py-0 shadow-sm"
                                        style="font-size:0.7rem;border:1px solid #e2e8f0;" data-fh-modal="recvModal"
                                        title="Detailed Analytics">
                                        <i class="fas fa-chart-pie me-1"></i>Details
                                    </button>
                                </div>
                            </div>
                            <div class="stat-value" style="font-size:1.4rem;">Rs {{ number_format($recv, 0) }}</div>
                            <div class="stat-label mb-2">Total Receivables (Due)</div>
                            <div id="mini-recv" style="height:55px;"></div>
                        </div>

                        {{-- CARD 6: Payables --}}
                        <div class="stat-card warning">
                            <div class="stat-header">
                                <div class="stat-icon"><i class="fa fa-file-invoice"></i></div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="stat-trend">Liability</div>
                                    <a href="{{ route('vendors-ledger') }}"
                                        class="btn btn-sm btn-light rounded-pill px-2 py-0 shadow-sm"
                                        style="font-size:0.7rem;border:1px solid #e2e8f0;" title="Go to Vendor Ledger">
                                        <i class="fas fa-external-link-alt me-1"></i>Ledger
                                    </a>
                                    <button class="btn btn-sm btn-light rounded-pill px-2 py-0 shadow-sm"
                                        style="font-size:0.7rem;border:1px solid #e2e8f0;" data-fh-modal="payModal"
                                        title="Detailed Analytics">
                                        <i class="fas fa-chart-pie me-1"></i>Details
                                    </button>
                                </div>
                            </div>
                            <div class="stat-value" style="font-size:1.4rem;">Rs {{ number_format($pay, 0) }}</div>
                            <div class="stat-label mb-2">Total Payables (Owe)</div>
                            <div id="mini-pay" style="height:55px;"></div>
                        </div>

                        {{-- CARD 7: Customer Advances --}}
                        <div class="stat-card purple">
                            <div class="stat-header">
                                <div class="stat-icon"><i class="fa fa-hand-holding-heart"></i></div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="stat-trend">Liability</div>
                                    <a href="{{ route('customers.ledger') }}"
                                        class="btn btn-sm btn-light rounded-pill px-2 py-0 shadow-sm"
                                        style="font-size:0.7rem;border:1px solid #e2e8f0;" title="Go to Customer Ledger">
                                        <i class="fas fa-external-link-alt me-1"></i>Ledger
                                    </a>
                                    <button class="btn btn-sm btn-light rounded-pill px-2 py-0 shadow-sm"
                                        style="font-size:0.7rem;border:1px solid #e2e8f0;" data-fh-modal="advModal"
                                        title="Detailed Analytics">
                                        <i class="fas fa-chart-pie me-1"></i>Details
                                    </button>
                                </div>
                            </div>
                            <div class="stat-value" style="font-size:1.4rem;">Rs {{ number_format($advances, 0) }}</div>
                            <div class="stat-label mb-2">Customer Advances</div>
                            <div id="mini-adv" style="height:55px;"></div>
                        </div>

                    </div>

                    {{-- ===== DETAIL MODALS ===== --}}

                    @php
                        $grossMargin = $sales > 0 ? round((($sales - $cogs) / $sales) * 100, 1) : 0;
                        $profitMargin = $sales > 0 ? round(($profit / $sales) * 100, 1) : 0;
                        $recvRatio = $recv + $pay > 0 ? round(($recv / ($recv + $pay)) * 100, 1) : 50;
                        $payRatio = 100 - $recvRatio;
                    @endphp

                    {{-- SALES Detail Modal --}}
                    <div class="modal fade" id="salesModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-xl">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-header"
                                    style="background: linear-gradient(135deg,#22c55e,#16a34a); color:white;">
                                    <h5 class="modal-title fw-bold"><i class="fas fa-hand-holding-usd me-2"></i>Sales
                                        Revenue — Detailed Analytics</h5>
                                    <button type="button" class="close" data-dismiss="modal"
                                        style="background:none;border:none;color:white;font-size:1.5rem;"><span>&times;</span></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 text-center"
                                                style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#22c55e;">Rs
                                                    {{ number_format($sales, 0) }}</div>
                                                <div class="text-muted small">Total Sales Revenue</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 text-center"
                                                style="background:#fff7ed;border:1px solid #fed7aa;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#f59e0b;">Rs
                                                    {{ number_format($cogs, 0) }}</div>
                                                <div class="text-muted small">Cost of Goods (COGS)</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 text-center"
                                                style="background:#eef2ff;border:1px solid #c7d2fe;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#6366f1;">
                                                    {{ $grossMargin }}%</div>
                                                <div class="text-muted small">Gross Margin</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="fw-semibold mb-2 text-muted small text-uppercase">Revenue Breakdown
                                            </div>
                                            <div id="modal-sales-donut" style="height:260px;"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="fw-semibold mb-2 text-muted small text-uppercase">Revenue vs Cost
                                                Bar</div>
                                            <div id="modal-sales-bar" style="height:260px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <a href="{{ route('customers.ledger') }}"
                                        class="btn btn-success rounded-pill px-4"><i
                                            class="fas fa-external-link-alt me-1"></i> Customer Ledger</a>
                                    <button type="button" class="btn btn-secondary rounded-pill px-4"
                                        data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- COGS Detail Modal --}}
                    <div class="modal fade" id="cogsModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-xl">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-header"
                                    style="background: linear-gradient(135deg,#f59e0b,#d97706); color:white;">
                                    <h5 class="modal-title fw-bold"><i class="fas fa-box-open me-2"></i>Cost of Goods Sold
                                        — Detailed Analytics</h5>
                                    <button type="button" class="close" data-dismiss="modal"
                                        style="background:none;border:none;color:white;font-size:1.5rem;"><span>&times;</span></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 text-center"
                                                style="background:#fff7ed;border:1px solid #fed7aa;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#f59e0b;">Rs
                                                    {{ number_format($cogs, 0) }}</div>
                                                <div class="text-muted small">Total COGS</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 text-center"
                                                style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#22c55e;">Rs
                                                    {{ number_format($sales, 0) }}</div>
                                                <div class="text-muted small">Sales Revenue</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 text-center"
                                                style="background:#eef2ff;border:1px solid #c7d2fe;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#6366f1;">
                                                    {{ $sales > 0 ? round(($cogs / $sales) * 100, 1) : 0 }}%</div>
                                                <div class="text-muted small">COGS as % of Sales</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="fw-semibold mb-2 text-muted small text-uppercase">Cost vs Revenue
                                                Donut</div>
                                            <div id="modal-cogs-donut" style="height:260px;"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="fw-semibold mb-2 text-muted small text-uppercase">Expense Structure
                                            </div>
                                            <div id="modal-cogs-bar" style="height:260px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary rounded-pill px-4"
                                        data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- EXPENSE Detail Modal --}}
                    <div class="modal fade" id="expenseModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-xl">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-header"
                                    style="background: linear-gradient(135deg,#ef4444,#dc2626); color:white;">
                                    <h5 class="modal-title fw-bold"><i class="fas fa-money-bill-wave me-2"></i>Operating
                                        Expenses — Detailed Analytics</h5>
                                    <button type="button" class="close" data-dismiss="modal"
                                        style="background:none;border:none;color:white;font-size:1.5rem;"><span>&times;</span></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 text-center"
                                                style="background:#fef2f2;border:1px solid #fecaca;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#ef4444;">Rs
                                                    {{ number_format($expenses, 0) }}</div>
                                                <div class="text-muted small">Operating Expenses</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 text-center"
                                                style="background:#fff7ed;border:1px solid #fed7aa;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#f59e0b;">Rs
                                                    {{ number_format($cogs, 0) }}</div>
                                                <div class="text-muted small">COGS</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 text-center"
                                                style="background:#fef2f2;border:1px solid #fecaca;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#dc2626;">Rs
                                                    {{ number_format($totalExpense, 0) }}</div>
                                                <div class="text-muted small">Total Outflow</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="fw-semibold mb-2 text-muted small text-uppercase">Expense
                                                Distribution</div>
                                            <div id="modal-expense-donut" style="height:260px;"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="fw-semibold mb-2 text-muted small text-uppercase">Income vs Total
                                                Expense</div>
                                            <div id="modal-expense-bar" style="height:260px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary rounded-pill px-4"
                                        data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PROFIT Detail Modal --}}
                    <div class="modal fade" id="profitModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-xl">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-header"
                                    style="background: linear-gradient(135deg,{{ $isLoss ? '#ef4444,#dc2626' : '#22c55e,#16a34a' }}); color:white;">
                                    <h5 class="modal-title fw-bold"><i
                                            class="fas fa-chart-line me-2"></i>{{ $isLoss ? 'Net Loss' : 'Net Profit' }} —
                                        Detailed Analytics</h5>
                                    <button type="button" class="close" data-dismiss="modal"
                                        style="background:none;border:none;color:white;font-size:1.5rem;"><span>&times;</span></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 text-center"
                                                style="background:#eef2ff;border:1px solid #c7d2fe;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#6366f1;">
                                                    {{ $profitMargin }}%</div>
                                                <div class="text-muted small">Net Profit Margin</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 text-center"
                                                style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#22c55e;">Rs
                                                    {{ number_format($sales, 0) }}</div>
                                                <div class="text-muted small">Revenue</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 text-center"
                                                style="background:#fef2f2;border:1px solid #fecaca;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#ef4444;">Rs
                                                    {{ number_format($totalExpense, 0) }}</div>
                                                <div class="text-muted small">Total Costs</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="fw-semibold mb-2 text-muted small text-uppercase">P&L Waterfall
                                                (Donut)</div>
                                            <div id="modal-profit-donut" style="height:260px;"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="fw-semibold mb-2 text-muted small text-uppercase">Revenue vs Costs
                                                vs Profit</div>
                                            <div id="modal-profit-bar" style="height:260px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary rounded-pill px-4"
                                        data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RECEIVABLES Detail Modal --}}
                    <div class="modal fade" id="recvModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-xl">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-header"
                                    style="background:linear-gradient(135deg,#0ea5e9,#0284c7);color:white;">
                                    <h5 class="modal-title fw-bold"><i class="fas fa-user-clock me-2"></i>Total
                                        Receivables — Detailed Analytics</h5>
                                    <button type="button" class="close" data-dismiss="modal"
                                        style="background:none;border:none;color:white;font-size:1.5rem;"><span>&times;</span></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 text-center"
                                                style="background:#e0f2fe;border:1px solid #bae6fd;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#0ea5e9;">Rs
                                                    {{ number_format($recv, 0) }}</div>
                                                <div class="text-muted small">Money Owed to You</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 text-center"
                                                style="background:#fef3c7;border:1px solid #fde68a;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#f59e0b;">Rs
                                                    {{ number_format($pay, 0) }}</div>
                                                <div class="text-muted small">Money You Owe</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 text-center"
                                                style="background:#eef2ff;border:1px solid #c7d2fe;">
                                                <div
                                                    style="font-size:1.6rem;font-weight:800;color:#{{ $recv >= $pay ? '22c55e' : 'ef4444' }};">
                                                    Rs {{ number_format(abs($recv - $pay), 0) }}</div>
                                                <div class="text-muted small">Net Position</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="fw-semibold mb-2 text-muted small text-uppercase">Receivable vs
                                                Payable (Donut)</div>
                                            <div id="modal-recv-donut" style="height:260px;"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="fw-semibold mb-2 text-muted small text-uppercase">Comparison Bar
                                            </div>
                                            <div id="modal-recv-bar" style="height:260px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <a href="{{ route('customers.ledger') }}"
                                        class="btn btn-info rounded-pill px-4 text-white"><i
                                            class="fas fa-external-link-alt me-1"></i> Customer Ledger</a>
                                    <button type="button" class="btn btn-secondary rounded-pill px-4"
                                        data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PAYABLES Detail Modal --}}
                    <div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-xl">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-header"
                                    style="background:linear-gradient(135deg,#f59e0b,#d97706);color:white;">
                                    <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice me-2"></i>Total Payables
                                        — Detailed Analytics</h5>
                                    <button type="button" class="close" data-dismiss="modal"
                                        style="background:none;border:none;color:white;font-size:1.5rem;"><span>&times;</span></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 text-center"
                                                style="background:#fef3c7;border:1px solid #fde68a;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#f59e0b;">Rs
                                                    {{ number_format($pay, 0) }}</div>
                                                <div class="text-muted small">Vendor Payables</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 text-center"
                                                style="background:#e0f2fe;border:1px solid #bae6fd;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#0ea5e9;">Rs
                                                    {{ number_format($recv, 0) }}</div>
                                                <div class="text-muted small">Customer Receivables</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 text-center"
                                                style="background:#eef2ff;border:1px solid #c7d2fe;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#6366f1;">
                                                    {{ $pay > 0 ? round(($pay / max($recv, 1)) * 100, 1) : 0 }}%</div>
                                                <div class="text-muted small">Payables Coverage</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="fw-semibold mb-2 text-muted small text-uppercase">Payable vs
                                                Receivable (Donut)</div>
                                            <div id="modal-pay-donut" style="height:260px;"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="fw-semibold mb-2 text-muted small text-uppercase">Comparison Bar
                                            </div>
                                            <div id="modal-pay-bar" style="height:260px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <a href="{{ route('vendors-ledger') }}" class="btn btn-warning rounded-pill px-4"><i
                                            class="fas fa-external-link-alt me-1"></i> Vendor Ledger</a>
                                    <button type="button" class="btn btn-secondary rounded-pill px-4"
                                        data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Customer Advances Modal --}}
                    <div class="modal fade" id="advModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-header"
                                    style="background: linear-gradient(135deg,#8b5cf6,#7c3aed); color:white;">
                                    <h5 class="modal-title fw-bold"><i class="fas fa-hand-holding-heart me-2"></i>Customer
                                        Advances (Liabilities)</h5>
                                    <button type="button" class="close" data-dismiss="modal"
                                        style="background:none;border:none;color:white;font-size:1.5rem;"><span>&times;</span></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="p-3 rounded-3 text-center mb-4"
                                        style="background:#f3e8ff;border:1px solid #d8b4fe;">
                                        <div style="font-size:1.6rem;font-weight:800;color:#8b5cf6;">Rs
                                            {{ number_format($advances, 0) }}</div>
                                        <div class="text-muted small">Total Customer Advances Held</div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Customer Name</th>
                                                    <th class="text-center">Advance Amount</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if (isset($financialSummary['customer_advances_list']) && count($financialSummary['customer_advances_list']) > 0)
                                                    @foreach ($financialSummary['customer_advances_list'] as $advCustomer)
                                                        <tr>
                                                            <td class="fw-bold">{{ $advCustomer['name'] }}</td>
                                                            <td class="text-center text-success fw-bold">Rs
                                                                {{ number_format($advCustomer['amount'], 0) }}</td>
                                                            <td class="text-center">
                                                                <a href="{{ route('customers.ledger', ['customer_id' => $advCustomer['id']]) }}"
                                                                    target="_blank"
                                                                    class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">View
                                                                    Ledger</a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-3">No customer
                                                            advances currently held.</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                    <p class="text-muted small mt-3 mb-0 text-center"><i class="fas fa-info-circle"></i>
                                        This represents money customers have paid you in advance, or credit left on their
                                        account. It is a liability.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                @endif

                <!-- Main Stats (Legacy/Ops) -->
                <div class="stats-grid">
                    @can('sales.view')
                        <div class="stat-card success">
                            <div class="stat-header">
                                <div class="stat-icon"><i class="fa fa-shopping-cart"></i></div>
                                <div class="stat-trend up"><i class="fa fa-arrow-up"></i> Sales</div>
                            </div>
                            <div class="stat-value">Rs {{ number_format($totalSales, 0) }}</div>
                            <div class="stat-label">Total Sales</div>
                        </div>
                    @endcan

                    @can('purchases.view')
                        <div class="stat-card primary">
                            <div class="stat-header">
                                <div class="stat-icon"><i class="fa fa-file-invoice-dollar"></i></div>
                                <div class="stat-trend up"><i class="fa fa-arrow-up"></i> Purchases</div>
                            </div>
                            <div class="stat-value">Rs {{ number_format($totalPurchases, 0) }}</div>
                            <div class="stat-label">Total Purchases</div>
                        </div>
                    @endcan

                    @can('sales.returns.view')
                        <div class="stat-card danger">
                            <div class="stat-header">
                                <div class="stat-icon"><i class="fa fa-undo-alt"></i></div>
                                <div class="stat-trend down"><i class="fa fa-arrow-down"></i> Returns</div>
                            </div>
                            <div class="stat-value">Rs {{ number_format($totalSalesReturns, 0) }}</div>
                            <div class="stat-label">Sales Returns</div>
                        </div>
                    @endcan

                    @can('purchase.returns.view')
                        <div class="stat-card warning">
                            <div class="stat-header">
                                <div class="stat-icon"><i class="fa fa-undo"></i></div>
                                <div class="stat-trend down"><i class="fa fa-arrow-down"></i> Returns</div>
                            </div>
                            <div class="stat-value">Rs {{ number_format($totalPurchaseReturns, 0) }}</div>
                            <div class="stat-label">Purchase Returns</div>
                        </div>
                    @endcan
                </div>

                <!-- Inventory Summary -->
                <div class="summary-row">
                    @can('categories.view')
                        <div class="summary-card">
                            <div class="summary-header">
                                <span class="summary-title">Categories</span>
                                <div class="summary-icon" style="background: #eef2ff; color: #6366f1;"><i
                                        class="fa fa-layer-group"></i></div>
                            </div>
                            <div class="summary-value">{{ $categoryCount }}</div>
                            <div class="summary-change positive"><i class="fa fa-folder"></i> Product groups</div>
                        </div>
                    @endcan

                    @can('subcategories.view')
                        <div class="summary-card">
                            <div class="summary-header">
                                <span class="summary-title">Subcategories</span>
                                <div class="summary-icon" style="background: #dcfce7; color: #22c55e;"><i
                                        class="fa fa-sitemap"></i></div>
                            </div>
                            <div class="summary-value">{{ $subcategoryCount }}</div>
                            <div class="summary-change positive"><i class="fa fa-tags"></i> Sub-groups</div>
                        </div>
                    @endcan

                    @can('products.view')
                        <div class="summary-card">
                            <div class="summary-header">
                                <span class="summary-title">Products</span>
                                <div class="summary-icon" style="background: #fef3c7; color: #f59e0b;"><i
                                        class="fa fa-box-open"></i></div>
                            </div>
                            <div class="summary-value">{{ $productCount }}</div>
                            <div class="summary-change positive"><i class="fa fa-cubes"></i> In inventory</div>
                        </div>
                    @endcan

                    @can('customers.view')
                        <div class="summary-card">
                            <div class="summary-header">
                                <span class="summary-title">Customers</span>
                                <div class="summary-icon" style="background: #e0f2fe; color: #0ea5e9;"><i
                                        class="fa fa-users"></i></div>
                            </div>
                            <div class="summary-value">{{ $customerscount }}</div>
                            <div class="summary-change positive"><i class="fa fa-user-plus"></i> Registered</div>
                        </div>
                    @endcan
                </div>

                <!-- Charts Section -->
                <div class="chart-section">
                    @can('sales.view')
                        <div class="chart-card full-width">
                            <div class="chart-header">
                                <div class="chart-title">
                                    <i class="fa fa-chart-line"></i> Sales Analytics
                                </div>
                                <div class="chart-filter" id="salesFilterBtns">
                                    <button class="filter-btn active" data-filter="daily">Daily</button>
                                    <button class="filter-btn" data-filter="weekly">Weekly</button>
                                    <button class="filter-btn" data-filter="monthly">Monthly</button>
                                </div>
                            </div>
                            <div class="chart-body">
                                <div id="salesReportChart" style="height: 350px;"></div>
                            </div>
                        </div>
                    @endcan

                    @can('purchases.view')
                        <div class="chart-card full-width">
                            <div class="chart-header">
                                <div class="chart-title">
                                    <i class="fa fa-chart-area"></i> Purchase Analytics
                                </div>
                                <div class="chart-filter" id="purchaseFilterBtns">
                                    <button class="filter-btn active" data-filter="daily">Daily</button>
                                    <button class="filter-btn" data-filter="weekly">Weekly</button>
                                    <button class="filter-btn" data-filter="monthly">Monthly</button>
                                </div>
                            </div>
                            <div class="chart-body">
                                <div id="purchaseReportChart" style="height: 350px;"></div>
                            </div>
                        </div>
                    @endcan
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const salesStats = @json($salesChartStats);
            const purchaseStats = @json($purchaseChartStats);

            // Sales Chart
            const salesOptions = {
                chart: {
                    type: 'area',
                    height: 350,
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'inherit',
                    dropShadow: {
                        enabled: true,
                        top: 3,
                        left: 2,
                        blur: 4,
                        opacity: 0.1
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                colors: ['#22c55e'],
                series: salesStats.daily.series,
                xaxis: {
                    categories: salesStats.daily.categories,
                    labels: {
                        style: {
                            colors: '#64748b',
                            fontSize: '12px'
                        }
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#64748b',
                            fontSize: '12px'
                        },
                        formatter: val => 'Rs ' + val.toLocaleString()
                    }
                },
                dataLabels: {
                    enabled: false
                },
                markers: {
                    size: 5,
                    colors: ['#fff'],
                    strokeColors: '#22c55e',
                    strokeWidth: 2,
                    hover: {
                        size: 7
                    }
                },
                fill: {
                    type: "gradient",
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                grid: {
                    borderColor: '#e2e8f0',
                    strokeDashArray: 4
                },
                tooltip: {
                    theme: "light",
                    y: {
                        formatter: val => "Rs " + val.toLocaleString()
                    }
                }
            };

            const salesChart = new ApexCharts(document.querySelector("#salesReportChart"), salesOptions);
            salesChart.render();

            // Sales Filter
            document.querySelectorAll('#salesFilterBtns .filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('#salesFilterBtns .filter-btn').forEach(b => b
                        .classList.remove('active'));
                    this.classList.add('active');
                    const selected = this.dataset.filter;
                    salesChart.updateOptions({
                        series: salesStats[selected].series,
                        xaxis: {
                            categories: salesStats[selected].categories
                        }
                    });
                });
            });

            // Purchase Chart
            const purchaseOptions = {
                chart: {
                    type: 'area',
                    height: 350,
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'inherit',
                    dropShadow: {
                        enabled: true,
                        top: 3,
                        left: 2,
                        blur: 4,
                        opacity: 0.1
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                colors: ['#6366f1'],
                series: purchaseStats.daily.series,
                xaxis: {
                    categories: purchaseStats.daily.categories,
                    labels: {
                        style: {
                            colors: '#64748b',
                            fontSize: '12px'
                        }
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#64748b',
                            fontSize: '12px'
                        },
                        formatter: val => 'Rs ' + val.toLocaleString()
                    }
                },
                dataLabels: {
                    enabled: false
                },
                markers: {
                    size: 5,
                    colors: ['#fff'],
                    strokeColors: '#6366f1',
                    strokeWidth: 2,
                    hover: {
                        size: 7
                    }
                },
                fill: {
                    type: "gradient",
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                grid: {
                    borderColor: '#e2e8f0',
                    strokeDashArray: 4
                },
                tooltip: {
                    theme: "light",
                    y: {
                        formatter: val => "Rs " + val.toLocaleString()
                    }
                }
            };

            const purchaseChart = new ApexCharts(document.querySelector("#purchaseReportChart"), purchaseOptions);
            purchaseChart.render();

            // Purchase Filter
            document.querySelectorAll('#purchaseFilterBtns .filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('#purchaseFilterBtns .filter-btn').forEach(b => b
                        .classList.remove('active'));
                    this.classList.add('active');
                    const selected = this.dataset.filter;
                    purchaseChart.updateOptions({
                        series: purchaseStats[selected].series,
                        xaxis: {
                            categories: purchaseStats[selected].categories
                        }
                    });
                });
            });

            // ================ FINANCIAL HEALTH MINI CHARTS + MODAL CHARTS ================
            @if (isset($financialSummary) && !empty($financialSummary))
                @php
                    $fhSales = floatval($financialSummary['sales'] ?? 0);
                    $fhCogs = floatval($financialSummary['cogs'] ?? 0);
                    $fhExpenses = floatval($financialSummary['expenses'] ?? 0);
                    $fhRecv = floatval($financialSummary['receivables'] ?? 0);
                    $fhAdvances = floatval($financialSummary['customer_advances'] ?? 0);
                    $fhPay = floatval($financialSummary['payables'] ?? 0);
                    $fhProfit = floatval($financialSummary['net_profit'] ?? 0);
                    $fhIsLoss = $fhProfit < 0;
                @endphp
                const fhSales = {{ $fhSales }};
                const fhCogs = {{ $fhCogs }};
                const fhExpenses = {{ $fhExpenses }};
                const fhRecv = {{ $fhRecv }};
                const fhAdvances = {{ $fhAdvances }};
                const fhPay = {{ $fhPay }};
                const isLoss = {{ $fhIsLoss ? 'true' : 'false' }};

                function mkSparkBar(el, val1, val2, c1, c2, labels) {
                    return new ApexCharts(document.querySelector(el), {
                        chart: {
                            type: 'bar',
                            height: 55,
                            sparkline: {
                                enabled: true
                            }
                        },
                        series: [{
                            name: labels[0],
                            data: [val1]
                        }, {
                            name: labels[1],
                            data: [val2]
                        }],
                        colors: [c1, c2],
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '50%',
                                borderRadius: 4
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: v => 'Rs ' + v.toLocaleString()
                            }
                        }
                    });
                }

                function mkSparkLine(el, vals, color) {
                    return new ApexCharts(document.querySelector(el), {
                        chart: {
                            type: 'line',
                            height: 55,
                            sparkline: {
                                enabled: true
                            }
                        },
                        series: [{
                            data: vals
                        }],
                        stroke: {
                            curve: 'smooth',
                            width: 2
                        },
                        colors: [color],
                        tooltip: {
                            y: {
                                formatter: v => 'Rs ' + v.toLocaleString()
                            }
                        }
                    });
                }

                // Mini sparklines inside each card
                mkSparkLine('#mini-sales', [0, fhCogs, fhSales], '#22c55e').render();
                mkSparkLine('#mini-cogs', [0, fhCogs], '#f59e0b').render();
                mkSparkLine('#mini-expenses', [0, fhExpenses], '#ef4444').render();
                mkSparkLine('#mini-profit', [fhSales, fhSales - fhCogs, fhSales - fhCogs - fhExpenses], isLoss ?
                    '#ef4444' :
                    '#22c55e').render();
                mkSparkLine('#mini-recv', [0, fhRecv], '#0ea5e9').render();
                mkSparkLine('#mini-adv', [0, fhAdvances], '#8b5cf6').render();
                mkSparkLine('#mini-pay', [0, fhPay], '#f59e0b').render();

                // Helper: render or skip if empty
                function safePie(el, labels, vals, colors) {
                    const total = vals.reduce((a, b) => a + b, 0);
                    if (total <= 0) {
                        document.querySelector(el).innerHTML =
                            '<p class="text-center text-muted pt-5 small">No data available</p>';
                        return;
                    }
                    new ApexCharts(document.querySelector(el), {
                        chart: {
                            type: 'donut',
                            height: 260,
                            fontFamily: 'inherit'
                        },
                        series: vals,
                        labels: labels,
                        colors: colors,
                        legend: {
                            position: 'bottom'
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '60%'
                                }
                            }
                        },
                        dataLabels: {
                            formatter: (v) => v.toFixed(1) + '%'
                        },
                        tooltip: {
                            y: {
                                formatter: v => 'Rs ' + v.toLocaleString()
                            }
                        }
                    }).render();
                }

                function safeBar(el, cats, series) {
                    new ApexCharts(document.querySelector(el), {
                        chart: {
                            type: 'bar',
                            height: 260,
                            toolbar: {
                                show: false
                            },
                            fontFamily: 'inherit'
                        },
                        series: series,
                        xaxis: {
                            categories: cats,
                            labels: {
                                style: {
                                    colors: '#64748b',
                                    fontSize: '12px'
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: v => 'Rs ' + Number(v).toLocaleString()
                            }
                        },
                        colors: series.map(s => s.color || '#6366f1'),
                        plotOptions: {
                            bar: {
                                columnWidth: '50%',
                                borderRadius: 5
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        tooltip: {
                            y: {
                                formatter: v => 'Rs ' + v.toLocaleString()
                            }
                        },
                        legend: {
                            show: true
                        }
                    }).render();
                }

                // Render modal charts on open
                let rendered = {};
                document.querySelectorAll('[data-fh-modal]').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.fhModal;
                        $('#' + id).modal('show');
                        if (rendered[id]) return;
                        rendered[id] = true;

                        // wait for modal to finish animating before drawing charts
                        setTimeout(function() {

                            if (id === 'salesModal') {
                                safePie('#modal-sales-donut', ['COGS', 'Gross Profit'], [
                                    fhCogs, Math
                                    .max(fhSales -
                                        fhCogs, 0)
                                ], ['#f59e0b', '#22c55e']);
                                safeBar('#modal-sales-bar', ['Sales', 'COGS',
                                    'Gross Profit'
                                ], [{
                                    name: 'Value',
                                    data: [fhSales, fhCogs, Math.max(fhSales -
                                        fhCogs, 0)],
                                    color: undefined
                                }]);
                            }
                            if (id === 'cogsModal') {
                                safePie('#modal-cogs-donut', ['COGS', 'Remaining Revenue'],
                                    [fhCogs,
                                        Math.max(
                                            fhSales - fhCogs, 0)
                                    ], ['#f59e0b', '#22c55e']);
                                safeBar('#modal-cogs-bar', ['COGS', 'Operating Exp',
                                    'Total Cost'
                                ], [{
                                    name: 'Amount',
                                    data: [fhCogs, fhExpenses, fhCogs +
                                        fhExpenses
                                    ],
                                    color: undefined
                                }]);
                            }
                            if (id === 'expenseModal') {
                                safePie('#modal-expense-donut', ['COGS',
                                    'Operating Expenses'
                                ], [fhCogs,
                                    fhExpenses
                                ], ['#f59e0b', '#ef4444']);
                                safeBar('#modal-expense-bar', ['Revenue', 'COGS',
                                        'Op.Expenses', 'Net'
                                    ],
                                    [{
                                        name: 'Amount',
                                        data: [fhSales, fhCogs, fhExpenses, Math
                                            .abs(fhSales -
                                                fhCogs -
                                                fhExpenses)
                                        ],
                                        color: undefined
                                    }]);
                            }
                            if (id === 'profitModal') {
                                const profitVal = fhSales - fhCogs - fhExpenses;
                                safePie('#modal-profit-donut', ['COGS', 'Expenses', isLoss ?
                                        'Loss' :
                                        'Net Profit'
                                    ],
                                    [fhCogs, fhExpenses, Math.abs(profitVal)],
                                    ['#f59e0b', '#ef4444', isLoss ? '#dc2626' :
                                        '#22c55e'
                                    ]);
                                safeBar('#modal-profit-bar', ['Revenue', 'Total Costs',
                                        'Net'
                                    ],
                                    [{
                                        name: 'Rs',
                                        data: [fhSales, fhCogs + fhExpenses, Math
                                            .abs(
                                                profitVal)
                                        ],
                                        color: undefined
                                    }]);
                            }
                            if (id === 'recvModal') {
                                safePie('#modal-recv-donut', ['Receivables', 'Payables'], [
                                    fhRecv,
                                    fhPay
                                ], [
                                    '#0ea5e9', '#f59e0b'
                                ]);
                                safeBar('#modal-recv-bar', ['Receivables', 'Payables'],
                                    [{
                                        name: 'Amount',
                                        data: [fhRecv, fhPay],
                                        color: undefined
                                    }]);
                            }
                            if (id === 'payModal') {
                                safePie('#modal-pay-donut', ['Payables', 'Receivables'], [
                                    fhPay,
                                    fhRecv
                                ], [
                                    '#f59e0b', '#0ea5e9'
                                ]);
                                safeBar('#modal-pay-bar', ['Payables', 'Receivables'],
                                    [{
                                        name: 'Amount',
                                        data: [fhPay, fhRecv],
                                        color: undefined
                                    }]);
                            }
                        }, 350); // end setTimeout
                    }); // end click handler
                }); // end forEach
            @endif

        }); // end DOMContentLoaded
    </script>

    {{-- Financial Health Info Modal --}}
    <div class="modal fade" id="financialHealthModal" tabindex="-1" aria-hidden="true" style="z-index: 1050;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-info ms-2"><i class="fas fa-info-circle me-2"></i> Dashboard
                        Financial Health</h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close"
                        style="background:none;border:none;font-size:1.5rem;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 pt-3 text-dark">
                    <p class="small text-muted mb-3">These top 5 cards represent your true accounting picture for the
                        current month. They are automatically calculated from your Journal Entries to give you an accurate
                        snapshot of the business.</p>

                    <div class="table-responsive small mb-0">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Indicator</th>
                                    <th>What it means</th>
                                    <th>How it is calculated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Sales Revenue</strong></td>
                                    <td>Total value of goods successfully sold.</td>
                                    <td class="text-success text-nowrap">Gross Sales - Sales Returns</td>
                                </tr>
                                <tr>
                                    <td><strong>Purchase Expense</strong></td>
                                    <td>Total cost of acquiring inventory.</td>
                                    <td class="text-danger text-nowrap">Gross Purchases - Purchase Returns</td>
                                </tr>
                                <tr>
                                    <td><strong>Net Profit / Loss</strong></td>
                                    <td>The actual money you made (or lost) this month.</td>
                                    <td class="text-primary fw-bold text-nowrap">Sales Revenue - Purchase Expense</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Receivables</strong></td>
                                    <td>Unpaid debts owed <strong>to you</strong> by customers.</td>
                                    <td class="text-muted text-nowrap">Sum of all Customer Ledgers (Dr)</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Payables</strong></td>
                                    <td>Unpaid debts you owe <strong>to suppliers</strong>.</td>
                                    <td class="text-muted text-nowrap">Sum of all Vendor Ledgers (Cr)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4">
                    <button type="button" class="btn btn-primary fw-medium px-4 rounded-pill shadow-sm"
                        data-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>
@endsection
