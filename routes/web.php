<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryZoneController;
use App\Http\Controllers\Admin\DirectSaleController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductWeightOptionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SalesHistoryController;
use App\Http\Controllers\Admin\StoreSettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StoreProfileController;
use Illuminate\Support\Facades\Route;

// Public routes (Customers / Visitors)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/katalog/{slug}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/tentang', [StoreProfileController::class, 'index'])->name('store.profile');

// Shopping Cart routes
Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');
Route::patch('/keranjang/{weightOption}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/keranjang/{weightOption}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::delete('/keranjang', [CartController::class, 'clear'])->name('cart.clear');

// Guest Checkout & Order Tracking routes
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/pesanan/{token}', [OrderTrackingController::class, 'show'])->name('order.track');
Route::post('/pesanan/{token}/snap-token', [PaymentController::class, 'getSnapToken'])->name('order.snap_token');
Route::post('/pesanan/{token}/sync-status', [PaymentController::class, 'syncStatus'])->name('order.sync_status');

// Midtrans Webhook (CSRF exempted in bootstrap/app.php)
Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handle'])->name('midtrans.notification');

// Authentication routes
Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');
Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.submit');
Route::match(['get', 'post'], '/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

// Internal routes (Admin, Owner, Cashier)
Route::middleware(['auth', 'active'])->prefix('admin')->name('admin.')->group(function () {

    // 1. Dashboard (Owner and Admin render view; Cashier redirected to direct-sales)
    Route::middleware('role:owner,admin,cashier')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    });

    // 2. Operational Routes (Cashier and Admin)
    Route::middleware('role:admin,cashier')->group(function () {
        // Direct In-Store Sales (POS)
        Route::get('direct-sales', [DirectSaleController::class, 'index'])->name('direct-sales.index');
        Route::get('direct-sales/create', [DirectSaleController::class, 'create'])->name('direct-sales.create');
        Route::post('direct-sales', [DirectSaleController::class, 'store'])->name('direct-sales.store');
        Route::get('direct-sales/{order}', [DirectSaleController::class, 'show'])->name('direct-sales.show');

        // Orders Management
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('orders/{order}/print', [OrderController::class, 'printLabel'])->name('orders.print');
        Route::patch('orders/{order}/fulfillment', [OrderController::class, 'updateFulfillment'])->name('orders.fulfillment');
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

        // Riwayat Penjualan Kasir Pribadi (Dilayani oleh Kasir)
        Route::get('my-sales-history', [SalesHistoryController::class, 'myHistory'])->name('my-sales-history.index');
        Route::get('my-sales-history/export', [SalesHistoryController::class, 'exportMyCsv'])->name('my-sales-history.export');
    });

    // 3. Reports & Sales History (Owner and Admin)
    Route::middleware('role:owner,admin')->group(function () {
        // Riwayat Seluruh Penjualan
        Route::get('sales-history', [SalesHistoryController::class, 'index'])->name('sales-history.index');
        Route::get('sales-history/export', [SalesHistoryController::class, 'exportAllCsv'])->name('sales-history.export');

        // Laporan Penjualan & Stok Menipis
        Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/sales/export', [ReportController::class, 'exportSalesCsv'])->name('reports.sales.export');
        Route::get('reports/low-stock', [ReportController::class, 'lowStock'])->name('reports.low-stock');
        Route::get('reports/low-stock/export', [ReportController::class, 'exportLowStockCsv'])->name('reports.low-stock.export');
    });

    // 4. Admin Management (Catalog, Stock Adjustments, Delivery Zones, Store Settings, Users)
    Route::middleware('role:admin')->group(function () {
        // Categories Management
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Products Management
        Route::resource('products', ProductController::class)->except(['show']);

        // Weight Options for Product
        Route::post('products/{product}/weight-options', [ProductWeightOptionController::class, 'store'])
            ->name('products.weight-options.store');
        Route::put('products/{product}/weight-options/{weightOption}', [ProductWeightOptionController::class, 'update'])
            ->name('products.weight-options.update');
        Route::delete('products/{product}/weight-options/{weightOption}', [ProductWeightOptionController::class, 'destroy'])
            ->name('products.weight-options.destroy');

        // Inventory & Stock Management
        Route::get('inventory', [InventoryController::class, 'index'])->name('stock.index');
        Route::get('inventory/movements', [InventoryController::class, 'movements'])->name('stock.movements');
        Route::get('inventory/adjust', [InventoryController::class, 'createAdjustment'])->name('stock.adjust.create');
        Route::post('inventory/adjust', [InventoryController::class, 'storeAdjustment'])->name('stock.adjust.store');

        // Delivery Zones Management
        Route::resource('delivery-zones', DeliveryZoneController::class)->except(['show']);
        Route::patch('delivery-zones/{deliveryZone}/toggle-active', [DeliveryZoneController::class, 'toggleActive'])
            ->name('delivery-zones.toggle-active');

        // Store Settings (Admin access per user request)
        Route::get('/settings', [StoreSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [StoreSettingController::class, 'update'])->name('settings.update');

        // User Management (Admin access per user request)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    });
});
