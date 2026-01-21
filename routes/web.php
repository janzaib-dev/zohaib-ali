<?php

use App\Http\Controllers\AccountsHeadController;
use App\Http\Controllers\AssemblyController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InwardgatepassController;
use App\Http\Controllers\NarrationController;
use App\Http\Controllers\PackageTypeController;
use App\Http\Controllers\PakageTypeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductBookingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalesOfficerController;
use App\Http\Controllers\StocksController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseStockController;
use App\Http\Controllers\ZoneController;
use Illuminate\Support\Facades\Route;

/*
    |--------------------------------------------------------------------------
    | Web Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register web routes for your application. These
    | routes are loaded by the RouteServiceProvider and all of them will
    | be assigned to the "web" middleware group. Make something great!
    |
    */

Route::get('/home', [HomeController::class, 'index'])->middleware('auth')->name('home');

// Route::get('/adminpage', [HomeController::class, 'adminpage'])->middleware(['auth','admin'])->name('adminpage');

Route::get('/test-log', function () {
    try {
        \Illuminate\Support\Facades\Log::info('Test log entry');
        return 'Log written successfully to ' . storage_path('logs/laravel.log');
    } catch (\Exception $e) {
        return 'Log write failed: ' . $e->getMessage();
    }
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    route::get('/category', [CategoryController::class, 'index'])->middleware('permission:categories.view')->name('Category.home');
    Route::get('/category/delete/{id}', [CategoryController::class, 'delete'])->middleware('permission:categories.delete')->name('delete.category');
    route::post('/category/stote', [CategoryController::class, 'store'])->middleware('permission:categories.create|categories.edit')->name('store.category');

    route::get('/Brand', [BrandController::class, 'index'])->middleware('permission:brands.view')->name('Brand.home');
    Route::get('/Brand/delete/{id}', [BrandController::class, 'delete'])->middleware('permission:brands.delete')->name('delete.Brand');
    route::post('/Brand/stote', [BrandController::class, 'store'])->middleware('permission:brands.create|brands.edit')->name('store.Brand');

    route::get('/Unit', [UnitController::class, 'index'])->middleware('permission:units.view')->name('Unit.home');
    Route::get('/Unit/delete/{id}', [UnitController::class, 'delete'])->middleware('permission:units.delete')->name('delete.Unit');
    route::post('/Unit/stote', [UnitController::class, 'store'])->middleware('permission:units.create|units.edit')->name('store.Unit');

    route::get('/subcategory', [SubcategoryController::class, 'index'])->middleware('permission:subcategories.view')->name('subcategory.home');
    Route::get('/subcategory/delete/{id}', [SubcategoryController::class, 'delete'])->middleware('permission:subcategories.delete')->name('delete.subcategory');
    route::post('/subcategory/stote', [SubcategoryController::class, 'store'])->middleware('permission:subcategories.create|subcategories.edit')->name('store.subcategory');

    Route::post('/assembly/pluck-part', [AssemblyController::class, 'pluckPart'])->middleware('permission:assembly.view')->name('assembly.pluck.part');
    Route::post('/assembly/repair-incomplete', [AssemblyController::class, 'repairIncomplete'])->middleware('permission:assembly.edit')->name('assembly.repair.incomplete');
    Route::post('/assembly/build-auto', [AssemblyController::class, 'buildAuto'])->middleware('permission:assembly.create')->name('assembly.build.auto');
    Route::get('/products/{id}/assembly-report', [ProductController::class, 'assemblyReport'])->name('products.assembly-report');
    Route::get('/assembly/summary', [ProductController::class, 'assemblySummary'])->name('assembly.summary');

    Route::post('/assembly/ensure-part-for-sale', [AssemblyController::class, 'ensurePartForSale'])->name('assembly.ensure_part_for_sale');
    Route::get('productget', [ProductController::class, 'productget'])->name('productget');

    Route::get('/product', [ProductController::class, 'Product'])->middleware('permission:products.view')->name('product');
    Route::get('/productview/{id}', [ProductController::class, 'productview'])->name('productview');
    // //////////
    Route::get('/products/price', [ProductController::class, 'getPrice'])
        ->name('products.price');

    // ////////
    Route::get('/create_prodcut', [ProductController::class, 'view_store'])->middleware('permission:products.create')->name('store');
    Route::post('/store-product', [ProductController::class, 'store_product'])->middleware('permission:products.create|products.edit')->name('store-product');
    Route::put('/product/update/{id}', [ProductController::class, 'update'])->middleware('permission:products.edit')->name('product.update');
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->middleware('permission:products.edit')->name('products.edit');
    Route::get('/generate-barcode-image', [ProductController::class, 'generateBarcode'])->name('generate-barcode-image');

    // Route::get('/barcode/{id}', [ProductController::class, 'barcode'])->name('product.barcode');
    // Searches
    Route::get('/generate-barcode-image', [ProductController::class, 'generateBarcode'])->name('generate-barcode-image');
    Route::get('/get-subcategories/{category_id}', [ProductController::class, 'getSubcategories'])->name('fetch-subcategories');

    Route::get('/search-part-name', [ProductController::class, 'searchPartName'])->name('search-part-name');

    Route::prefix('discount')->group(function () {
        Route::get('/', [DiscountController::class, 'index'])->middleware('permission:discount.products.view')->name('discount.index');
        Route::get('/create', [DiscountController::class, 'create'])->middleware('permission:discount.products.create')->name('discount.create');
        Route::post('/store', [DiscountController::class, 'store'])->middleware('permission:discount.products.create')->name('discount.store');
        Route::post('/toggle-status/{id}', [DiscountController::class, 'toggleStatus'])->middleware('permission:discount.products.edit')->name('discount.toggleStatus');
        Route::get('/barcode/{id}', [DiscountController::class, 'barcode'])->middleware('permission:discount.products.view')->name('discount.barcode');
    });

    Route::get('/parts-adjust', [AssemblyController::class, 'adjustForm'])
        ->name('stock.adjust.form');

    Route::post('/stock-adjust/bulk', [AssemblyController::class, 'adjustBulk'])
        ->name('assembly.adjust.bulk');

    // package type controller

    // Route::get('/package-types', [PakageTypeController::class, 'index'])
    //     ->name('package-type.index');

    // Route::post('/package-type/store', [PackageTypeController::class, 'store'])
    //     ->name('package-type.store');

    // Route::post('/package-type/update', [PackageTypeController::class, 'update'])
    //     ->name('package-type.update');

    // Route::get('/package-type/delete/{id}', [PackageTypeController::class, 'destroy'])
    //     ->name('package-type.delete');

    // Assembly Routes
    Route::get('/assembly-report', [AssemblyController::class, 'index'])->middleware('permission:assembly.report.view')->name('assembly.report');
    Route::get('/assembly-report/{product}', [AssemblyController::class, 'show'])->middleware('permission:assembly.report.view')->name('assembly.report.show');
    Route::post('/assembly/build', [AssemblyController::class, 'build'])->middleware('permission:assembly.create')->name('assembly.build');

    // routes/web.php

    // Customer Routes
    // Dropdown list (by type)
    Route::get('sale/customers', [CustomerController::class, 'saleindex'])->middleware('permission:customers.view')->name('salecustomers.index');

    // Single customer detail
    Route::get('sale/customers/{id}', [CustomerController::class, 'show'])->middleware('permission:customers.view')->name('salecustomers.show');
    // Cutomer create
    Route::get('/customers', [CustomerController::class, 'index'])->middleware('permission:customers.view')->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->middleware('permission:customers.create')->name('customers.create');
    Route::post('/customers/store', [CustomerController::class, 'store'])->middleware('permission:customers.create')->name('customers.store');
    Route::get('/customers/edit/{id}', [CustomerController::class, 'edit'])->middleware('permission:customers.edit')->name('customers.edit');
    Route::post('/customers/update/{id}', [CustomerController::class, 'update'])->middleware('permission:customers.edit')->name('customers.update');
    Route::get('/customers/delete/{id}', [CustomerController::class, 'destroy'])->middleware('permission:customers.delete')->name('customers.destroy');

    // New
    Route::get('/customers/inactive', [CustomerController::class, 'inactiveCustomers'])->name('customers.inactive');
    Route::get('/customers/inactive/{id}', [CustomerController::class, 'markInactive'])->name('customers.markInactive');
    Route::get('customers/toggle-status/{id}', [CustomerController::class, 'toggleStatus'])->name('customers.toggleStatus');
    Route::get('/customers/ledger', [CustomerController::class, 'customer_ledger'])->name('customers.ledger');
    Route::get('/customer/payments', [CustomerController::class, 'customer_payments'])->name('customer.payments');
    Route::post('/customer/payments', [CustomerController::class, 'store_customer_payment'])->name('customer.payments.store');
    // web.php
    Route::get('/customer/ledger/{id}', [CustomerController::class, 'getCustomerLedger']);
    Route::delete('/customer-payments/{id}', [CustomerController::class, 'destroy_payment'])->name('customer.payments.destroy');

    // Vendor Routes
    Route::get('/vendor', [VendorController::class, 'index'])->middleware('permission:vendors.view');
    Route::post('/vendor/store', [VendorController::class, 'store'])->name('vendors.store.ajax')->middleware('permission:vendors.create|vendors.edit');
    Route::get('/vendor/delete/{id}', [VendorController::class, 'delete'])->middleware('permission:vendors.delete');
    Route::get('/vendors-ledger', [VendorController::class, 'vendors_ledger'])->middleware('permission:vendors.view')->name('vendors-ledger');
    Route::get('/vendor/payments', [VendorController::class, 'vendor_payments'])->middleware('permission:vendors.view')->name('vendor.payments');
    Route::post('/vendor/payments', [VendorController::class, 'store_vendor_payment'])->middleware('permission:vendors.create')->name('vendor.payments.store');
    Route::get('/vendor/bilties', [VendorController::class, 'vendor_bilties'])->middleware('permission:vendors.view')->name('vendor.bilties');
    Route::post('/vendor/bilties', [VendorController::class, 'store_vendor_bilty'])->middleware('permission:vendors.create')->name('vendor.bilties.store');

    // Warehouse Routes
    // ///
    Route::get('/warehouses/get/', [WarehouseController::class, 'getWarehouses'])->name('warehouses.get');

    // ///
    Route::get('/warehouse', [WarehouseController::class, 'index'])->middleware('permission:warehouse.view');
    Route::post('/warehouse/store', [WarehouseController::class, 'store'])->middleware('permission:warehouse.create|warehouse.edit');
    Route::get('/warehouse/delete/{id}', [WarehouseController::class, 'delete'])->middleware('permission:warehouse.delete');

    // Branches
    // Branches
    Route::get('/branch', [BranchController::class, 'index'])->name('branch.index')->middleware('permission:branches.view');
    Route::post('/branch', [BranchController::class, 'store'])->name('branch.store')->middleware('permission:branches.create|branches.edit');
    Route::get('/branch/delete/{id}', [BranchController::class, 'delete'])->name('branch.delete')->middleware('permission:branches.delete');

    // Roles
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index')->middleware('permission:roles.view');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store')->middleware('permission:roles.create|roles.edit');
    Route::get('/roles/delete/{id}', [RoleController::class, 'delete'])->name('roles.delete')->middleware('permission:roles.delete');
    Route::post('/admin/roles/update-permission', [RoleController::class, 'updatePermissions'])->name('roles.update.permission')->middleware('permission:roles.edit');

    // Permissions
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index')->middleware('permission:permissions.view');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store')->middleware('permission:permissions.create|permissions.edit');
    Route::get('/permissions/delete/{id}', [PermissionController::class, 'delete'])->name('permission.delete')->middleware('permission:permissions.delete');

    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('permission:users.view');
    Route::post('/users', [UserController::class, 'store'])->name('users.store')->middleware('permission:users.create|users.edit');
    Route::get('/users/delete/{id}', [UserController::class, 'delete'])->name('users.delete')->middleware('permission:users.delete');
    Route::post('/admin/users/update-roles', [UserController::class, 'updateRoles'])->name('users.update.roles')->middleware('permission:users.edit');
    // Route::put('/users/{id}/roles', [UserController::class, 'updateRoles'])->name('users.update.roles');

    // Zone
    Route::get('zone', [ZoneController::class, 'index'])->middleware('permission:zones.view')->name('zone.index');
    Route::post('zones/store', [ZoneController::class, 'store'])->middleware('permission:zones.create|zones.edit')->name('zone.store');
    Route::get('zones/edit/{id}', [ZoneController::class, 'edit'])->middleware('permission:zones.edit')->name('zone.edit');
    Route::get('zones/delete/{id}', [ZoneController::class, 'destroy'])->middleware('permission:zones.delete')->name('zone.delete');

    // Sales Officer
    Route::get('sales-officers', [SalesOfficerController::class, 'index'])->middleware('permission:sales.officers.view')->name('sales.officer.index');
    Route::post('sales-officers/store', [SalesOfficerController::class, 'store'])->middleware('permission:sales.officers.create')->name('sales-officer.store');
    Route::get('sales-officers/edit/{id}', [SalesOfficerController::class, 'edit'])->middleware('permission:sales.officers.edit')->name('sales.officer.edit');
    Route::delete('sales-officers/{id}', [SalesOfficerController::class, 'destroy'])->middleware('permission:sales.officers.delete')->name('sales-officer.delete');

    // products

    route::get('/Purchase', [PurchaseController::class, 'index'])->middleware('permission:purchases.view')->name('Purchase.home');
    route::get('/add/Purchase', [PurchaseController::class, 'add_purchase'])->middleware('permission:purchases.create')->name('add_purchase');
    route::post('/Purchase/stote', [PurchaseController::class, 'store'])->middleware('permission:purchases.create|purchases.edit')->name('store.Purchase');
    Route::get('/purchase/{id}/edit', [PurchaseController::class, 'edit'])->middleware('permission:purchases.edit')->name('purchase.edit');
    Route::put('/purchase/{id}', [PurchaseController::class, 'update'])->middleware('permission:purchases.edit')->name('purchase.update');
    Route::delete('/purchase/{id}', [PurchaseController::class, 'destroy'])->middleware('permission:purchases.delete')->name('purchase.destroy');
    Route::get('/search-products', [ProductController::class, 'searchProducts'])->name('search-products');
    Route::get('/purchase/{id}/invoice', [PurchaseController::class, 'Invoice'])->middleware('permission:purchases.view')->name('purchase.invoice');

    Route::get('purchase/return', [PurchaseController::class, 'purchaseReturnIndex'])->name('purchase.return.index');
    Route::get('purchase/return/{id}', [PurchaseController::class, 'showReturnForm'])->name('purchase.return.show');
    Route::post('purchase/return/store', [PurchaseController::class, 'storeReturn'])->name('purchase.return.store');

    // Inward Gatepass Routes
    Route::get('/InwardGatepass', [InwardgatepassController::class, 'index'])->middleware('permission:inward.gatepass.view')->name('InwardGatepass.home');
    Route::get('/add/InwardGatepass', [InwardgatepassController::class, 'create'])->middleware('permission:inward.gatepass.create')->name('add_inwardgatepass');
    Route::post('/InwardGatepass/store', [InwardgatepassController::class, 'store'])->middleware('permission:inward.gatepass.create')->name('store.InwardGatepass');
    Route::get('/InwardGatepass/{id}', [InwardgatepassController::class, 'show'])->middleware('permission:inward.gatepass.view')->name('InwardGatepass.show');

    // edit/update/delete abhi comment kiye hue hain
    Route::get('/InwardGatepass/{id}/edit', [InwardgatepassController::class, 'edit'])->middleware('permission:inward.gatepass.edit')->name('InwardGatepass.edit');
    Route::put('/InwardGatepass/{id}', [InwardgatepassController::class, 'update'])->middleware('permission:inward.gatepass.edit')->name('InwardGatepass.update');
    Route::get('/inward-gatepass/{id}/pdf', [InwardgatepassController::class, 'pdf'])->name('InwardGatepass.pdf');

    Route::delete('/InwardGatepass/{id}', [InwardgatepassController::class, 'destroy'])->middleware('permission:inward.gatepass.delete')->name('InwardGatepass.destroy');
    // Products search
    Route::get('/search-products', [InwardgatepassController::class, 'searchProducts'])->name('search-products');

    // Show Add Bill Form
    Route::get('inward-gatepass/{id}/add-bill', [PurchaseController::class, 'addBill'])->middleware('permission:inward.gatepass.create')->name('add_bill');
    // Store Bill
    Route::post('inward-gatepass/{id}/store-bill', [PurchaseController::class, 'store'])->middleware('permission:inward.gatepass.create')->name('store.bill');
    // Purchase Return Routes

    // Route::get('/fetch-product', [PurchaseController::class, 'fetchProduct'])->name('item.search');
    // Route::post('/fetch-item-details', [PurchaseController::class, 'fetchItemDetails']);
    // Route::get('/Purchase/create', function () {
    //     return view('admin_panel.purchase.add_purchase');
    // });
    // Route::get('/get-items-by-category/{categoryId}', [PurchaseController::class, 'getItemsByCategory'])->name('get-items-by-category');
    // Route::get('/get-product-details/{productName}', [ProductController::class, 'getProductDetails'])->name('get-product-details');

    // Route::get('booking/system', [SaleController::class,'booking-system'])->name('booking.index');
    Route::get('sale', [SaleController::class, 'index'])->middleware('permission:sales.view')->name('sale.index');
    Route::get('sale/create', [SaleController::class, 'addsale'])->middleware('permission:sales.create')->name('sale.add');
    Route::get('/products/search', [SaleController::class, 'searchProducts'])->name('products.search');
    Route::get('/search-product-name', [SaleController::class, 'searchpname'])->name('search-product-name');
    Route::post('/sales/store', [SaleController::class, 'store'])->middleware('permission:sales.create')->name('sales.store');
    Route::get('/sales/{id}/return', [SaleController::class, 'saleretun'])->middleware('permission:sales.create')->name('sales.return.create');
    Route::post('/sales-return/store', [SaleController::class, 'storeSaleReturn'])->middleware('permission:sales.create')->name('sales.return.store');
    Route::get('/sale-returns', [App\Http\Controllers\SaleController::class, 'salereturnview'])->middleware('permission:sales.view')->name('sale.returns.index');
    Route::get('/sales/{id}/invoice', [SaleController::class, 'saleinvoice'])->middleware('permission:sales.view')->name('sales.invoice');
    Route::get('/sales/{id}/edit', [SaleController::class, 'saleedit'])->middleware('permission:sales.edit')->name('sales.edit');
    Route::put('/sales/{id}', [SaleController::class, 'updatesale'])->middleware('permission:sales.edit')->name('sales.update');
    Route::get('/sales/{id}/dc', [SaleController::class, 'saledc'])->middleware('permission:sales.view')->name('sales.dc');
    Route::get('/sales/{id}/recepit', [SaleController::class, 'salerecepit'])->middleware('permission:sales.view')->name('sales.recepit');

    // booking system

    Route::get('bookings', [ProductBookingController::class, 'index'])->middleware('permission:bookings.view')->name('bookings.index');
    Route::get('bookings/create', [ProductBookingController::class, 'create'])->middleware('permission:bookings.create')->name('bookings.create');
    Route::post('bookings/store', [ProductBookingController::class, 'store'])->middleware('permission:bookings.create')->name('bookings.store');
    Route::get('booking/receipt/{id}', [ProductBookingController::class, 'receipt'])->middleware('permission:bookings.view')->name('booking.receipt');
    Route::get('/sales/from-booking/{id}', [SaleController::class, 'convertFromBooking'])->name('sales.from.booking');

    // web.php
    Route::get('/warehouse-stock-quantity', [StockTransferController::class, 'getStockQuantity'])->middleware('permission:stock.transfer.view')->name('warehouse.stock.quantity');

    // narratiions
    Route::get('/get-customers-by-type', [CustomerController::class, 'getByType'])->middleware('permission:customers.view');
    Route::resource('warehouse_stocks', WarehouseStockController::class)->middleware(['permission:warehouse.stock.view']);
    Route::resource('stock_transfers', StockTransferController::class)->middleware(['permission:stock.transfer.view']);
    // //////////
    Route::get('/get-stock/{product}', [StocksController::class, 'getStock'])
        ->name('get.stock');
    // ////////
    Route::get('/narrations', [NarrationController::class, 'index'])->name('narrations.index')->middleware('permission:narrations.view');
    Route::post('/narrations', [NarrationController::class, 'store'])->name('narrations.store')->middleware('permission:narrations.create');
    Route::delete('/narrations/{narration}', [NarrationController::class, 'destroy'])->name('narrations.destroy')->middleware('permission:narrations.delete');
    Route::get('vouchers/{type}', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::post('vouchers/store', [VoucherController::class, 'store'])->name('vouchers.store');
    Route::get('/view_all', [AccountsHeadController::class, 'index'])->name('view_all');
    Route::get('/get-vendor-balance/{id}', [VendorController::class, 'getVendorBalance']);

    // reporting routes

    Route::get('/report/item-stock', [ReportingController::class, 'item_stock_report'])->middleware('permission:item.stock.report.view')->name('report.item_stock');
    Route::post('/report/item-stock-fetch', [ReportingController::class, 'fetchItemStock'])->middleware('permission:item.stock.report.view')->name('report.item_stock.fetch');

    Route::get('report/purchase', [ReportingController::class, 'purchase_report'])->middleware('permission:purchase.report.view')->name('report.purchase');
    Route::post('report/purchase/fetch', [ReportingController::class, 'fetchPurchaseReport'])->middleware('permission:purchase.report.view')->name('report.purchase.fetch');

    Route::get('report/sale', [ReportingController::class, 'sale_report'])->middleware('permission:sale.report.view')->name('report.sale');
    Route::get('report/sale/fetch', [ReportingController::class, 'fetchsaleReport'])->middleware('permission:sale.report.view')->name('report.sale.fetch');

    Route::get('report/customer/ledger', [ReportingController::class, 'customer_ledger_report'])->middleware('permission:customer.ledger.view')->name('report.customer.ledger');
    Route::get('report/customer-ledger/fetch', [ReportingController::class, 'fetch_customer_ledger'])->middleware('permission:customer.ledger.view')->name('report.customer.ledger.fetch');

    Route::get('reports/onhand', [ReportingController::class, 'onhand'])->middleware('permission:inventory.onhand.view')->name('reports.onhand');

    // Return modules list for permission dropdowns (AJAX)
    Route::get('/modules/list', function () {
        return response()->json(\Illuminate\Support\Facades\DB::table('modules')->pluck('name'));
    })->name('modules.list');
    // reports

});
// Temporary debug route to inspect authenticated user's roles & permissions (remove after use)
Route::get('/debug-perms', function () {
    $u = auth()->user();
    if (! $u) {
        return response()->json(['error' => 'not_authenticated'], 401);
    }

    return response()->json([
        'user' => $u->only('id', 'name', 'email'),
        'roles' => $u->getRoleNames(),
        'permissions' => $u->getAllPermissions()->pluck('name'),
        'can_products_read' => $u->can('products.read'),
        'can_any_products_or_users' => $u->canAny(['products.read', 'users.read']),
    ]);
})->middleware('auth');

require __DIR__.'/auth.php';

require __DIR__.'/hr.php';
