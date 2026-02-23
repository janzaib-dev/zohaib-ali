{{-- @include('admin_panel.layout.header') --}}

{{-- @yield('content')
@include('admin_panel.layout.footer') --}}



<!DOCTYPE html>
<html class="no-js" lang="zxx">

<head>
    <style>
        /* ERP Mega Menu & Normal Submenu Compact Styling */
        .nav-item .submenu,
        .mega-menu .submenu {
            background: #fff;
            padding: 12px;
            /* compact padding */
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .mega-menu .category-heading {
            font-size: 13px;
            font-weight: 600;
            color: #34495e;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #eaeaea;
        }

        .nav-item .submenu-item li,
        .mega-menu .submenu-item li {
            margin-bottom: 4px;
            /* less spacing */
        }

        .nav-item .submenu-item li a,
        .mega-menu .submenu-item li a {
            display: flex;
            align-items: center;
            font-size: 15px;
            /* smaller font */
            color: #555;
            padding: 4px 8px;
            /* compact padding */
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .nav-item .submenu-item li a i,
        .mega-menu .submenu-item li a i {
            font-size: 14px;
            margin-right: 6px;
            color: #2980b9;
            min-width: 18px;
            text-align: center;
        }

        .nav-item .submenu-item li a:hover,
        .mega-menu .submenu-item li a:hover {
            background: #f1f7fd;
            color: #2980b9;
            font-weight: 500;
        }

        /* Dynamic Mega Menu Styling */
        .mega-menu {
            position: relative;
        }

        .mega-menu .submenu {
            width: max-content !important;
            max-width: 95vw;
            min-width: 220px;
            left: 0;
            right: auto;
        }

        .mega-menu .col-group-wrapper {
            display: flex;
            flex-wrap: nowrap;
            margin: 0 -8px;
            /* Offset padding */
        }

        .mega-menu .col-group {
            width: 240px;
            /* Consistent column width */
            flex: 0 0 auto;
            border-right: 1px solid #f0f0f0;
            padding: 0 16px;
        }

        .mega-menu .col-group:last-child {
            border-right: none;
        }

        /* Override Bootstrap col widths inside mega menu */
        .mega-menu .col-md-3 {
            flex: none;
            max-width: none;
        }

        /* ── GLOBAL PRINT STYLES ─────────────────────────────────────────── */
        @media print {

            /* 1. Hide Chrome elements */
            nav.rt_nav_header,
            .nav-bottom,
            footer,
            .navbar-toggler,
            .top_nav,
            .filter-card,
            .filter-bar,
            .topbar-actions,
            .rpt-actions,
            .kpi-row,
            .kpi-strip,
            .led-filters,
            .btn-search,
            .btn-reset,
            .btn-csv,
            .btn-print,
            .btn-inv,
            .btn-exp,
            .btn-expand,
            .btn-srp,
            .total-strip,
            .loader-overlay,
            .led-loader,
            .dataTables_filter,
            .dataTables_length,
            .dataTables_info,
            .dataTables_paginate,
            .empty-state,
            .container-scroller>nav {
                display: none !important;
            }

            /* 2. Page layout resets */
            body,
            html {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .container-scroller,
            .main-content,
            .main-content-inner,
            .container-fluid {
                display: block !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }

            /* 3. Reveal table and details */
            .items-row,
            .detail-row,
            .wh-row {
                display: table-row !important;
            }

            #ledgerResult,
            #tableWrap,
            #stockTableWrap,
            .print-header {
                display: block !important;
            }

            /* 4. Table visual cleanup */
            table {
                border-collapse: collapse !important;
                width: 100% !important;
                font-size: 11px !important;
            }

            th,
            td {
                border: 1px solid #999 !important;
                padding: 4px 8px !important;
            }

            thead th {
                background-color: #f0f0f0 !important;
                color: #000 !important;
                font-weight: 700 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .table-card,
            .tbl-card {
                box-shadow: none !important;
                border-radius: 0 !important;
                border: none !important;
                margin: 0 !important;
            }

            /* 5. Header formatting */
            .print-header {
                display: block !important;
                margin-bottom: 20px;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
            }

            tr {
                page-break-inside: avoid;
            }
        }
    </style>
    <!--=========================*
                Met Data
    *===========================-->
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Zare Bootstrap 4 Admin Template">

    <!--=========================*
              Page Title
    *===========================-->
    <title>Home 2 | Zare Bootstrap 4 Admin Template</title>

    <!--=========================*
                Favicon
    *===========================-->

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/owl.theme.default.min.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}"> --}}
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/themify-icons.css') }}"> --}}
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/ionicons.min.css') }}"> --}}
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/et-line.css') }}"> --}}
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/feather.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('assets/css/flag-icon.min.css') }}">
    <script src="{{ asset('assets/js/modernizr-2.8.3.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/metisMenu.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/slicknav.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/am-charts/css/am-charts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/charts/morris-bundle/morris.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/charts/c3charts/c3.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/data-table/css/jquery.dataTables.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/data-table/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/data-table/css/responsive.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/data-table/css/responsive.jqueryui.min.css') }}">
    {{-- Removed Duplicate External CDN Scripts (BS5/jQuery) to prevent conflicts with Template BS4 --}}
    {{-- Online Links --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/brands.min.css"
        integrity="sha512-58P9Hy7II0YeXLv+iFiLCv1rtLW47xmiRpC1oFafeKNShp8V5bKV/ciVtYqbk2YfxXQMt58DjNfkXFOn62xE+g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/brands.min.css"
        integrity="sha512-58P9Hy7II0YeXLv+iFiLCv1rtLW47xmiRpC1oFafeKNShp8V5bKV/ciVtYqbk2YfxXQMt58DjNfkXFOn62xE+g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    @vite(['resources/js/app.js'])
</head>

<body>
    <!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

    <!--=========================*
         Page Container
*===========================-->
    <div class="container-scroller">
        <!--=========================*
              Navigation
    *===========================-->
        <nav class="rt_nav_header horizontal-layout col-lg-12 col-12 p-0">
            <div class="top_nav flex-grow-1">
                <div class="container d-flex flex-row h-100 align-items-center">
                    <!--=========================*
                              Logo
                *===========================-->
                    <div class="text-center rt_nav_wrapper d-flex align-items-center">
                        {{-- <a class="nav_logo rt_logo" href="index.html"><img  src="{{asset('assets/images/WIJDAN-removebg-preview.png')}}" alt="logo" /></a> --}}
                        <a class="nav_logo rt_logo text-success" href="index.html">Prowaves</a>
                        {{-- <a class="nav_logo nav_logo_mob" href="index.html"><img src="{{asset('assets/images/WIJDAN-removebg-preview.png')}}" alt="logo"/></a> --}}
                    </div>
                    <!--=========================*
                           End Logo
               *===========================-->
                    <div class="nav_wrapper_main d-flex align-items-center justify-content-between flex-grow-1">
                        <ul class="navbar-nav navbar-nav-right mr-0 ml-auto">
                            <!-- My Attendance Quick Access -->
                            <li class="nav-item mr-3">
                                <a href="{{ route('my-attendance') }}" class="nav-link"
                                    style="background: linear-gradient(135deg, #22c55e, #16a34a); color: white; border-radius: 8px; padding: 8px 16px;">
                                    <i class="fa fa-fingerprint"></i> My Attendance
                                </a>
                            </li>

                            <!-- Notification Bell -->
                            <li class="nav-item dropdown mr-2" id="notificationLi">
                                <a class="nav-link count-indicator dropdown-toggle position-relative"
                                    id="notificationDropdown" href="#" data-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="fas fa-bell text-secondary"
                                        style="font-size: 20px; transition: color 0.3s;"></i>
                                    <span class="badge badge-danger notification-badge"
                                        style="display: none; position: absolute; top: -2px; right: -2px; font-size: 9px; padding: 3px 5px; border-radius: 50%; box-shadow: 0 2px 5px rgba(220,53,69,0.5);">0</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list shadow-lg border-0"
                                    aria-labelledby="notificationDropdown"
                                    style="width: 320px; border-radius: 12px; margin-top: 10px; overflow: hidden;">
                                    <div class="dropdown-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center"
                                        style="border-radius: 12px 12px 0 0;">
                                        <p class="mb-0 font-weight-bold text-dark">NOTIFICATIONS</p>
                                    </div>
                                    <div id="notificationList" style="max-height: 350px; overflow-y: auto;">
                                        <!-- Items will be injected here -->
                                        <div class="text-center p-4">
                                            <div class="spinner-border text-primary spinner-border-sm" role="status">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Sticky Footer Button -->
                                    <div class="dropdown-footer text-center bg-light border-top p-2"
                                        style="position: sticky; bottom: 0; z-index: 10;">
                                        <a href="{{ route('notifications.index') }}"
                                            class="btn btn-primary btn-sm btn-block shadow-sm font-weight-bold">View
                                            All Notifications</a>
                                    </div>
                                </div>
                            </li>

                            <style>
                                /* Standard Click Dropdown Styling */

                                /* Scrollbar */
                                #notificationList::-webkit-scrollbar {
                                    width: 5px;
                                }

                                #notificationList::-webkit-scrollbar-thumb {
                                    background: #e0e0e0;
                                    border-radius: 10px;
                                }

                                #notificationList::-webkit-scrollbar-track {
                                    background: transparent;
                                }

                                /* Items */
                                .notification-item {
                                    transition: all 0.2s ease;
                                    border-left: 3px solid transparent;
                                }

                                .notification-item:hover {
                                    background-color: #f8f9fa;
                                    border-left: 3px solid #3b82f6;
                                    /* Blue accent */
                                }

                                /* Navigation Bell Hover */
                                #notificationLi .nav-link:hover .fa-bell {
                                    color: #3b82f6 !important;
                                    /* Blue on hover */
                                }
                            </style>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Poll every 30s
                                    fetchNotifications();
                                    setInterval(fetchNotifications, 30000);
                                });

                                function fetchNotifications() {
                                    if (typeof $ === 'undefined') return;

                                    $.get("{{ route('notifications.fetch') }}", function(data) {
                                        // Update Badge
                                        if (data.count > 0) {
                                            $('.notification-badge').text(data.count).show();
                                            $('.notification-badge').addClass('animate__animated animate__pulse');
                                        } else {
                                            $('.notification-badge').hide();
                                        }

                                        // Update List
                                        let html = '';
                                        if (data.notifications.length === 0) {
                                            html = `
                                                <div class="text-center p-5">
                                                    <i class="fas fa-bell-slash text-muted mb-2" style="font-size: 24px;"></i>
                                                    <p class="text-muted small mb-0">No new notifications</p>
                                                </div>`;
                                        } else {
                                            data.notifications.forEach(n => {
                                                // Modern colors: Soft background with strong icon color
                                                let iconBg = '#e3f2fd'; // Light Blue
                                                let iconColor = '#2196f3'; // Blue
                                                let iconClass = 'fa-info';

                                                if (n.type === 'sale_return') {
                                                    iconBg = '#fff3e0'; // Light Orange
                                                    iconColor = '#ff9800'; // Orange
                                                    iconClass = 'fa-undo';
                                                }

                                                html += `
                                                <a class="dropdown-item p-3 notification-item" href="${n.action_url || '#'}" style="white-space: normal;">
                                                    <div class="d-flex align-items-start">
                                                        <div class="me-3 mt-1" style="min-width: 36px;">
                                                            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                                 style="width:36px; height:36px; background-color: ${iconBg}; color: ${iconColor};">
                                                                <i class="fas ${iconClass}" style="font-size:14px;"></i>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="font-weight-bold text-dark mb-1" style="font-size:14px; line-height:1.2;">${n.title}</h6>
                                                            <p class="text-muted small mb-1" style="font-size:12px; line-height:1.4; color: #6c757d;">
                                                                ${n.message.substring(0, 60)}${n.message.length > 60 ? '...' : ''}
                                                            </p>
                                                            <p class="text-secondary small mb-0" style="font-size:10px; font-weight: 500;">
                                                                <i class="far fa-clock me-1"></i> ${new Date(n.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </a>`;
                                            });
                                        }
                                        $('#notificationList').html(html);
                                    });
                                }
                            </script>

                            <li class="nav-item nav-profile dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown"
                                    id="profileDropdown">
                                    <span class="profile_name">{{ Auth::user()->name }} <i
                                            class="fas fa-chevron-down"></i></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right navbar-dropdown pt-2"
                                    aria-labelledby="profileDropdown">
                                    <span role="separator" class="divider"></span>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-power-off text-dark mr-3"></i> Logout
                                        </button>
                                    </form>
                                    {{-- </a> --}}
                                </div>
                            </li>
                            <!--==================================*
                                 End Profile Menu
                        *====================================-->
                        </ul>
                        <!--=========================*
                               Mobile Menu
                   *===========================-->
                        <button class="navbar-toggler align-self-center" type="button" data-toggle="minimize">
                            <span class="fas fa-bars text-white"></span>
                        </button>
                        <!--=========================*
                           End Mobile Menu
                   *===========================-->
                    </div>
                </div>
            </div>
            <div class="nav-bottom">
                <div class="container">
                    <ul class="nav page-navigation">
                        <!--=========================*
                              Home
                    *===========================-->
                        <li class="nav-item">
                            <a href="{{ url('/home') }}" class="nav-link"><i
                                    class="menu_icon fas fa-home"></i><span class="menu-title">Dashboard</span></a>

                        </li>
                        <!--=========================*
                              UI Features
                    *===========================-->
                        <li class="nav-item mega-menu">
                            @canany(['products.view', 'discount.products.view', 'categories.view', 'subcategories.view',
                                'brands.view', 'units.view', 'vendors.view', 'warehouse.view', 'warehouse.stock.view',
                                'stock.transfer.view', 'sales.view', 'customers.view', 'sales.officers.view'])
                                <a href="#" class="nav-link">
                                    <i class="menu_icon fas fa-cogs"></i>
                                    <span class="menu-title">Management</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="submenu">
                                    <div class="col-group-wrapper row">
                                        <!-- Products & Categories -->
                                        @canany(['products.view', 'discount.products.view', 'categories.view',
                                            'subcategories.view', 'brands.view', 'units.view'])
                                            <div class="col-group col-md-3">
                                                <p class="category-heading">Products & Categories</p>
                                                <ul class="submenu-item">

                                                    @can('products.view')
                                                        <li><a href="{{ route('product') }}"><i class="fas fa-box"></i>
                                                                Products</a></li>
                                                    @endcan

                                                    @can('discount.products.view')
                                                        <li><a href="{{ route('discount.index') }}"><i class="fas fa-tags"></i>
                                                                Discount Products</a></li>
                                                    @endcan

                                                    @can('categories.view')
                                                        <li><a href="{{ route('Category.home') }}"><i class="fas fa-list"></i>
                                                                Category</a></li>
                                                    @endcan

                                                    @can('subcategories.view')
                                                        <li><a href="{{ route('subcategory.home') }}"><i
                                                                    class="fas fa-th-list"></i> Sub Category</a></li>
                                                    @endcan

                                                    @can('brands.view')
                                                        <li><a href="{{ route('Brand.home') }}"><i class="fas fa-trademark"></i>
                                                                Brands</a></li>
                                                    @endcan

                                                    @can('units.view')
                                                        <li><a href="{{ route('Unit.home') }}"><i
                                                                    class="fas fa-balance-scale"></i> Units</a></li>
                                                    @endcan

                                                </ul>
                                            </div>
                                        @endcanany
                                        <!-- Purchase & Inventory -->
                                        @canany(['vendors.view', 'purchases.view'])
                                            <div class="col-group col-md-3">
                                                <p class="category-heading">Purchase & Inventory</p>
                                                <ul class="submenu-item">
                                                    @can('vendors.view')
                                                        <li><a href="{{ url('vendor') }}"><i class="fas fa-truck"></i> Vendor</a>
                                                        </li>
                                                    @endcan
                                                    @can('purchases.view')
                                                        <li><a href="{{ route('Purchase.home') }}"><i
                                                                    class="fas fa-shopping-cart"></i> Purchase</a>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        @endcanany
                                        <!-- Accounts -->
                                        @canany(['warehouse.view', 'warehouse.stock.view', 'stock.transfer.view'])
                                            <div class="col-group col-md-3">
                                                <p class="category-heading">Accounts</p>
                                                <ul class="submenu-item">
                                                    @can('warehouse.view')
                                                        <li><a href="{{ url('warehouse') }}"><i class="fas fa-warehouse"></i>
                                                                Warehouse</a></li>
                                                    @endcan
                                                    @can('warehouse.stock.view')
                                                        <li><a href="{{ url('warehouse_stocks') }}"><i class="fas fa-boxes"></i>
                                                                Warehouse Stock</a></li>
                                                    @endcan
                                                    @can('stock.transfer.view')
                                                        <li><a href="{{ url('stock_transfers') }}"><i
                                                                    class="fas fa-exchange-alt"></i> Stock Transfer</a></li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        @endcanany
                                        <!-- Customers & Sales -->
                                        @canany(['sales.view', 'customers.view', 'sales.officers.view',
                                            'receipts.voucher.view'])
                                            <div class="col-group col-md-3">
                                                <p class="category-heading">Sales & Customers</p>
                                                <ul class="submenu-item">
                                                    @can('sales.view')
                                                        <li><a href="{{ url('sale') }}"><i class="fas fa-receipt"></i>
                                                                Sales</a></li>
                                                    @endcan
                                                    @can('customers.view')
                                                        <li><a href="{{ url('customers') }}"><i class="fas fa-user"></i>
                                                                Customer</a></li>
                                                    @endcan
                                                    @can('sales.officers.view')
                                                        <li><a href="{{ url('sales-officers') }}"><i class="fas fa-user-tie"></i>
                                                                Sales Officer</a></li>
                                                    @endcan
                                                    @can('receipts.voucher.view')
                                                        <li><a href="{{ route('all_recepit_vochers') }}"><i
                                                                    class="fas fa-file-invoice-dollar"></i>
                                                                Receipt Vouchers</a></li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        @endcanany
                                    </div>
                                </div>
                            @endcanany
                        </li>


                        <!-- Vouchers Menu -->
                        <li class="nav-item">
                            @canany(['chart.of.accounts.view', 'expense.voucher.view', 'receipts.voucher.view',
                                'journal.voucher.view', 'payment.voucher.view', 'income.voucher.view'])
                                <a href="#" class="nav-link">
                                    <i class="menu_icon fas fa-clipboard-list"></i>
                                    <span class="menu-title">Vouchers</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="submenu">
                                    <ul class="submenu-item">
                                        @can('chart.of.accounts.view')
                                            <li><a href="{{ route('view_all') }}"><i class="fa-solid fa-money-bill-wave"></i>
                                                    Char Of Accounts</a></li>
                                        @endcan
                                        @can('expense.voucher.view')
                                            <li><a href="{{ route('all_expense_vochers') }}"><i
                                                        class="fa-solid fa-money-bill-wave"></i> Expense Voucher</a></li>
                                        @endcan
                                        @can('receipts.voucher.view')
                                            <li><a href="{{ route('all_recepit_vochers') }}"><i
                                                        class="fa-solid fa-wallet"></i> Receipts Voucher</a></li>
                                        @endcan
                                        @can('journal.voucher.view')
                                            <li><a href="{{ route('vouchers.index', 'journal voucher') }}"><i
                                                        class="fa-solid fa-wallet"></i> Journal Voucher</a></li>
                                        @endcan
                                        @can('payment.voucher.view')
                                            <li><a href="{{ route('all_Payment_vochers') }}"><i
                                                        class="fa-solid fa-wallet"></i> Payment Voucher</a></li>
                                        @endcan
                                        @can('income.voucher.view')
                                            <li><a href="{{ route('vouchers.index', 'income voucher') }}"><i
                                                        class="fa-solid fa-wallet"></i> Income Voucher</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            @endcanany
                        </li>
                        <li class="nav-item">
                            @canany(['item.stock.report.view', 'purchase.report.view', 'sale.report.view',
                                'customer.ledger.view', 'inventory.onhand.view'])
                                <a href="#" class="nav-link">
                                    <i class="menu_icon fas fa-clipboard-list"></i>
                                    <span class="menu-title">Reports</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="submenu">
                                    <ul class="submenu-item">
                                        @can('item.stock.report.view')
                                            <li><a href="{{ route('report.item_stock') }}"><i class="fa-solid fa-users"></i>
                                                    Item Stock Report</a></li>
                                        @endcan
                                        @can('purchase.report.view')
                                            <li><a href="{{ route('report.purchase') }}"><i class="fa-solid fa-users"></i>
                                                    Purchase Report</a></li>
                                        @endcan
                                        @can('sale.report.view')
                                            <li><a href="{{ route('report.sale') }}"><i class="fa-solid fa-users"></i> Sale
                                                    Report</a></li>
                                        @endcan
                                        @can('customer.ledger.view')
                                            <li><a href="{{ route('report.customer.ledger') }}"><i
                                                        class="fa-solid fa-users"></i> Customer Ledger</a></li>
                                        @endcan


                                        @can('inventory.onhand.view')
                                            <li><a href="{{ route('reports.onhand') }}"><i class="fas fa-warehouse"></i>
                                                    Inventory On-Hand</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            @endcanany
                        </li>
                        <!-- HR Management Menu -->
                        <li class="nav-item">
                            @canany(['hr.departments.view', 'hr.designations.view', 'hr.employees.view',
                                'hr.attendance.view', 'hr.payroll.view', 'hr.leaves.view', 'hr.salary.structure.view',
                                'hr.shifts.view', 'hr.holidays.view', 'hr.loans.view', 'hr.biometric.devices.view'])
                                <a href="#" class="nav-link">
                                    <i class="menu_icon fas fa-users-cog"></i>
                                    <span class="menu-title">HR Management</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="submenu">
                                    <ul class="submenu-item">
                                        @can('hr.departments.view')
                                            <li><a href="{{ route('hr.departments.index') }}"><i
                                                        class="fa-solid fa-building"></i> Departments</a></li>
                                        @endcan
                                        @can('hr.designations.view')
                                            <li><a href="{{ route('hr.designations.index') }}"><i
                                                        class="fa-solid fa-id-badge"></i> Designations</a></li>
                                        @endcan
                                        @can('hr.employees.view')
                                            <li><a href="{{ route('hr.employees.index') }}"><i
                                                        class="fa-solid fa-user-tie"></i> Employees</a></li>
                                        @endcan
                                        @can('hr.attendance.view')
                                            <li><a href="{{ route('hr.attendance.index') }}"><i
                                                        class="fa-solid fa-clock"></i> Attendance</a></li>
                                        @endcan
                                        @can('hr.payroll.view')
                                            <li><a href="{{ route('hr.payroll.index') }}"><i
                                                        class="fa-solid fa-money-check-alt"></i> Payroll</a></li>
                                        @endcan
                                        @can('hr.leaves.view')
                                            <li><a href="{{ route('hr.leaves.index') }}"><i
                                                        class="fa-solid fa-calendar-minus"></i> Leaves</a></li>
                                        @endcan
                                        @can('hr.salary.structure.view')
                                            <li><a href="{{ route('hr.salary-structure.index') }}"><i
                                                        class="fa-solid fa-coins"></i> Salary Structure</a></li>
                                        @endcan
                                        @can('hr.shifts.view')
                                            <li><a href="{{ route('hr.shifts.index') }}"><i class="fa-solid fa-clock"></i>
                                                    Shifts</a></li>
                                        @endcan
                                        @can('hr.holidays.view')
                                            <li><a href="{{ route('hr.holidays.index') }}"><i
                                                        class="fa-solid fa-calendar-alt"></i> Holidays</a></li>
                                        @endcan
                                        @can('hr.loans.view')
                                            <li><a href="{{ route('hr.loans.index') }}"><i
                                                        class="fa-solid fa-hand-holding-dollar"></i> Loans</a></li>
                                        @endcan
                                        @can('hr.biometric.devices.view')
                                            <li><a href="{{ route('hr.biometric-devices.index') }}"><i
                                                        class="fa-solid fa-fingerprint"></i> Biometric Devices</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            @endcanany
                        </li>
                        <!-- User Management Menu -->
                        <li class="nav-item">
                            @canany(['users.view', 'roles.view', 'permissions.view', 'branches.view'])
                                <a href="#" class="nav-link">
                                    <i class="menu_icon fas fa-clipboard-list"></i>
                                    <span class="menu-title">User Management</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="submenu">
                                    <ul class="submenu-item">
                                        @can('users.view')
                                            <li><a href="{{ route('users.index') }}"><i class="fa-solid fa-users"></i>
                                                    Users</a></li>
                                        @endcan
                                        @can('roles.view')
                                            <li><a href="{{ route('roles.index') }}"><i class="fa-solid fa-user-lock"></i>
                                                    Roles</a></li>
                                        @endcan
                                        @can('permissions.view')
                                            <li><a href="{{ route('permissions.index') }}"><i
                                                        class="fa-solid fa-user-lock"></i> Permissions</a></li>
                                        @endcan
                                        @can('branches.view')
                                            <li><a href="{{ route('branch.index') }}"><i class="fa-solid fa-code-branch"></i>
                                                    Branches</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            @endcanany
                        </li>

                        <!-- Settings -->
                        @if (auth()->check() && (auth()->user()->email === 'admin@admin.com' || auth()->user()->hasRole('Super Admin')))
                            <li class="nav-item">
                                <a href="{{ route('settings.index') }}" class="nav-link">
                                    <i class="menu_icon fas fa-cog"></i>
                                    <span class="menu-title">Settings</span>
                                </a>
                            </li>
                        @endif

                    </ul>
                </div>
            </div>
        </nav>

        @yield('content')

        <footer>
            <div class="footer-area">
                <p>&copy; Copyright 2025. All right reserved. Zohiab Ali Company.</p>
            </div>
        </footer>
    </div>
    <!-- Jquery Js -->
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <!-- bootstrap 4 js -->
    <script src="{{ asset('assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <!-- Owl Carousel Js -->
    <script src="{{ asset('assets/js/owl.carousel.min.js') }}"></script>
    <!-- Metis Menu Js -->
    <script src="{{ asset('assets/js/metisMenu.min.js') }}"></script>
    <!-- SlimScroll Js -->
    <script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}"></script>
    <!-- Slick Nav -->
    <script src="{{ asset('assets/js/jquery.slicknav.min.js') }}"></script>

    <!-- start amchart js -->
    <script src="{{ asset('assets/vendors/am-charts/js/ammap.js') }}"></script>
    <script src="{{ asset('assets/vendors/am-charts/js/worldLow.js') }}"></script>
    <script src="{{ asset('assets/vendors/am-charts/js/continentsLow.js') }}"></script>
    <script src="{{ asset('assets/vendors/am-charts/js/light.js') }}"></script>
    <!-- maps js -->
    <script src="{{ asset('assets/js/am-maps.js') }}"></script>

    <!-- Morris Chart -->
    <script src="{{ asset('assets/vendors/charts/morris-bundle/raphael.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/charts/morris-bundle/morris.js') }}"></script>

    <!-- Chart Js -->
    <script src="{{ asset('assets/vendors/charts/charts-bundle/Chart.bundle.js') }}"></script>

    <!-- C3 Chart -->
    <script src="{{ asset('assets/vendors/charts/c3charts/c3.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/charts/c3charts/d3-5.4.0.min.js') }}"></script>

    <!-- Data Table js -->
    <script src="{{ asset('assets/vendors/data-table/js/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('assets/vendors/data-table/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/data-table/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/data-table/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/data-table/js/responsive.bootstrap.min.js') }}"></script>

    <!-- Sparkline Chart -->
    <script src="{{ asset('assets/vendors/charts/sparkline/jquery.sparkline.js') }}"></script>

    <!-- Home Script -->
    <script src="{{ asset('assets/js/home.js') }}"></script>

    <!-- Main Js -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @yield('js')

    <!-- Global SweetAlert Toast/Popup -->
    <script>
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: "{{ session('error') }}",
            });
        @endif
    </script>

    {{-- Anti-Ghost Mode: Disconnect BrowserSync to stop cross-tab navigation sync --}}
    <script>
        if (window.___browserSync___) {
            console.log('BrowserSync detected. Disconnecting socket to stop Ghost Mode sync.');
            window.___browserSync___.socket.disconnect();
        }
    </script>
</body>

</html>
