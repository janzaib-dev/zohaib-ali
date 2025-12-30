@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container">

            <div class="row g-3">
                <!-- Categories -->
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">Categories</h6>
                                <h3 class="mb-0 fw-bold">{{ $categoryCount }}</h3>
                            </div>
                            <div class="icon text-primary">
                                <i class="fas fa-layer-group fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subcategories -->
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">Subcategories</h6>
                                <h3 class="mb-0 fw-bold">{{ $subcategoryCount }}</h3>
                            </div>
                            <div class="icon text-success">
                                <i class="fas fa-sitemap fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products -->
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">Products</h6>
                                <h3 class="mb-0 fw-bold">{{ $productCount }}</h3>
                            </div>
                            <div class="icon text-danger">
                                <i class="fas fa-box-open fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Example for future (e.g. Orders) -->
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">Customers</h6>
                                <h3 class="mb-0 fw-bold">{{ $customerscount }}</h3>
                            </div>
                            <div class="icon text-warning">
                                <i class="fas fa-shopping-cart fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Total Purchases -->
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">Total Purchases</h6>
                                <h5 class="mb-0 fw-bold">Rs {{ number_format($totalPurchases, 2) }}</h5>
                            </div>
                            <div class="icon text-primary">
                                <i class="fas fa-file-invoice-dollar fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase Returns -->
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">Purchase Returns</h6>
                                <h5 class="mb-0 fw-bold">Rs {{ number_format($totalPurchaseReturns, 2) }}</h5>
                            </div>
                            <div class="icon text-danger">
                                <i class="fas fa-undo-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Sales -->
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">Total Sales</h6>
                                <h5 class="mb-0 fw-bold">Rs {{ number_format($totalSales, 2) }}</h5>
                            </div>
                            <div class="icon text-success">
                                <i class="fas fa-shopping-cart fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales Returns -->
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">Sales Returns</h6>
                                <h5 class="mb-0 fw-bold">Rs {{ number_format($totalSalesReturns, 2) }}</h5>
                            </div>
                            <div class="icon text-warning">
                                <i class="fas fa-undo fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row mt-4">
                <!-- Sales Chart -->
                <div class="col-md-12 mb-4">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Sales Report</h6>
                            <label for="salesFilter" class="form-label fw-bold">Sales Report Filter:</label>
                            <select id="salesFilter" class="form-select w-auto">
                                <option value="daily" selected>Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="card-body">
                            <div id="salesReportChart" style="height: 400px;" class="bg-white rounded shadow-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase Chart -->
                <div class="col-md-12">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Purchase Report</h6>
                            <label for="purchaseFilter" class="form-label fw-bold">Purchase Report Filter:</label>
                            <select id="purchaseFilter" class="form-select w-auto">
                                <option value="daily" selected>Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>

                        </div>
                        <div class="card-body">
                            <div id="purchaseReportChart" style="height: 400px;" class="bg-white rounded shadow-sm"></div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const salesStats = @json($salesChartStats);

        const salesOptions = {
            chart: {
                type: 'area',
                height: 400,
                toolbar: {
                    show: false
                },
                dropShadow: {
                    enabled: true,
                    top: 5,
                    left: 2,
                    blur: 4,
                    opacity: 0.2
                }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            colors: ['#4ba064'],
            series: salesStats.daily.series,
            xaxis: {
                categories: salesStats.daily.categories,
                labels: {
                    style: {
                        colors: '#6c757d',
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
                        colors: '#6c757d',
                        fontSize: '12px'
                    }
                }
            },
            dataLabels: {
                enabled: false
            },
            markers: {
                size: 5,
                colors: ['#fff'],
                strokeColors: '#4ba064',
                strokeWidth: 2,
                hover: {
                    size: 7
                }
            },
            fill: {
                type: "gradient",
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.3,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            grid: {
                borderColor: '#e9ecef',
                strokeDashArray: 4
            },
            tooltip: {
                theme: "light",
                y: {
                    formatter: val => "Rs " + val.toLocaleString()
                }
            },
            legend: {
                position: 'top',
                labels: {
                    colors: '#495057'
                }
            }
        };

        const salesChart = new ApexCharts(document.querySelector("#salesReportChart"), salesOptions);
        salesChart.render();

        document.getElementById('salesFilter').addEventListener('change', function() {
            const selected = this.value;
            salesChart.updateOptions({
                series: salesStats[selected].series,
                xaxis: {
                    categories: salesStats[selected].categories
                }
            });
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const purchaseStats = @json($purchaseChartStats);

        const purchaseOptions = {
            chart: {
                type: 'line',
                height: 400,
                toolbar: {
                    show: false
                },
                dropShadow: {
                    enabled: true,
                    top: 5,
                    left: 2,
                    blur: 4,
                    opacity: 0.15
                }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            colors: ['#007bff'],
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: "vertical",
                    shadeIntensity: 0.3,
                    gradientToColors: ['#66b2ff'],
                    inverseColors: false,
                    opacityFrom: 0.6,
                    opacityTo: 0.5,
                    stops: [0, 80, 100]
                }
            },
            series: purchaseStats.daily.series,
            xaxis: {
                categories: purchaseStats.daily.categories,
                labels: {
                    style: {
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '12px'
                    }
                }
            },
            markers: {
                size: 4,
                colors: ['#fff'],
                strokeColors: '#007bff',
                strokeWidth: 2,
                hover: {
                    size: 6
                }
            },
            dataLabels: {
                enabled: false
            },
            grid: {
                borderColor: '#f1f1f1'
            },
            legend: {
                position: 'top'
            },
            tooltip: {
                theme: 'light',
                style: {
                    fontSize: '13px'
                }
            }
        };

        const purchaseChart = new ApexCharts(document.querySelector("#purchaseReportChart"), purchaseOptions);
        purchaseChart.render();

        document.getElementById('purchaseFilter').addEventListener('change', function() {
            const selected = this.value;
            purchaseChart.updateOptions({
                series: purchaseStats[selected].series,
                xaxis: {
                    categories: purchaseStats[selected].categories
                }
            });
        });
    });
</script>