<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Staff\StaffDashboardController;

// PERFORMANCE OPTIMIZATION
// Route cache compatible redirect.
Route::redirect('/', '/login');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
    // STAFF PRODUCT REQUEST
    // PRODUCT APPROVAL FLOW
    Route::get('/approvals', [App\Http\Controllers\Admin\ApprovalController::class, 'index'])->name('admin.approvals.index');
    Route::post('/product-requests/{productRequest}/approve', [App\Http\Controllers\Admin\ApprovalController::class, 'approveProduct'])->name('admin.productRequests.approve');
    Route::post('/product-requests/{productRequest}/reject', [App\Http\Controllers\Admin\ApprovalController::class, 'rejectProduct'])->name('admin.productRequests.reject');
    // PRICE APPROVAL SYSTEM
    Route::post('/price-requests/{priceRequest}/approve', [App\Http\Controllers\Admin\ApprovalController::class, 'approvePrice'])->name('admin.priceRequests.approve');
    Route::post('/price-requests/{priceRequest}/reject', [App\Http\Controllers\Admin\ApprovalController::class, 'rejectPrice'])->name('admin.priceRequests.reject');
    
    // Reports (Admin Only)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [App\Http\Controllers\Admin\ReportsController::class, 'salesReport'])->name('sales');
        Route::get('/profit', [App\Http\Controllers\Admin\ReportsController::class, 'profitReport'])->name('profit');
        Route::get('/stock', [App\Http\Controllers\Admin\ReportsController::class, 'stockReport'])->name('stock');
        Route::get('/due', [App\Http\Controllers\Admin\ReportsController::class, 'dueReport'])->name('due');
        Route::get('/ledger', [App\Http\Controllers\Admin\ReportsController::class, 'customerLedger'])->name('ledger');
    });

    // Admin only stock actions
    Route::get('/stock/create', [App\Http\Controllers\Admin\StockController::class, 'create'])->name('stock.create');
    Route::post('/stock', [App\Http\Controllers\Admin\StockController::class, 'store'])->name('stock.store');
});

// Staff Dashboard
Route::middleware(['auth', 'staff'])->prefix('staff')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('staff.dashboard');
    // STAFF DASHBOARD FIX
    Route::get('/sales', [StaffDashboardController::class, 'sales'])->name('staff.sales');
    Route::get('/product-requests', [App\Http\Controllers\Staff\ProductRequestController::class, 'index'])->name('staff.productRequests.index');
    Route::post('/product-requests', [App\Http\Controllers\Staff\ProductRequestController::class, 'store'])->name('staff.productRequests.store');
    Route::get('/price-requests', [App\Http\Controllers\Staff\PriceApprovalRequestController::class, 'index'])->name('staff.priceRequests.index');
    Route::post('/price-requests', [App\Http\Controllers\Staff\PriceApprovalRequestController::class, 'store'])->name('staff.priceRequests.store');
});

// Shared routes for Admin and Staff
Route::middleware(['auth', 'staff'])->prefix('admin')->group(function () {
    Route::resource('customers', App\Http\Controllers\Admin\CustomerController::class);
    Route::get('/stock', [App\Http\Controllers\Admin\StockController::class, 'index'])->name('stock.index');
    // ROW PRINT FIX
    Route::get('/invoices/{invoice}/print', [App\Http\Controllers\PosController::class, 'print'])->name('invoice.print');
    Route::get('/customers/{customer}/due-receipt', [App\Http\Controllers\Admin\ReportsController::class, 'customerDueReceipt'])->name('receipt.print');
    
    // Payments / Due Management
    // DUE COLLECTION IMPROVEMENT
    Route::get('/payments/customer-due-data/{customer}', [App\Http\Controllers\Admin\PaymentController::class, 'customerDueData'])->name('payments.customerDueData');
    Route::get('/payments/search-invoices', [App\Http\Controllers\Admin\PaymentController::class, 'searchInvoices'])->name('payments.searchInvoices');
    Route::resource('payments', App\Http\Controllers\Admin\PaymentController::class);
    // PAYMENT RECEIPT SYSTEM
    Route::get('/payments/{payment}/receipt', [App\Http\Controllers\Admin\PaymentController::class, 'receipt'])->name('payment.receipt');
});

use App\Http\Controllers\PosController;

Route::middleware(['auth', 'staff'])->prefix('pos')->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('pos.index');
    Route::post('/store', [PosController::class, 'store'])->name('pos.store');
    Route::get('/product/{id}', [PosController::class, 'getProductDetails'])->name('pos.productDetails');
    // CUSTOMER PRICE MEMORY
    Route::get('/customer-price/{customerId}/{productId}', [PosController::class, 'getCustomerProductPrice'])->name('pos.customerPrice');
    Route::post('/customer-prices/{customerId}', [PosController::class, 'getCustomerProductPrices'])->name('pos.customerPrices');
    Route::get('/print/{invoice}', [PosController::class, 'print'])->name('pos.print');
});
