<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Staff\StaffDashboardController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
    
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

// Shared routes for Admin and Staff
Route::middleware(['auth', 'staff'])->prefix('admin')->group(function () {
    Route::resource('customers', App\Http\Controllers\Admin\CustomerController::class);
    Route::get('/stock', [App\Http\Controllers\Admin\StockController::class, 'index'])->name('stock.index');
    
    // Payments / Due Management
    Route::resource('payments', App\Http\Controllers\Admin\PaymentController::class);
    Route::get('/payments/{id}/receipt', [App\Http\Controllers\Admin\PaymentController::class, 'receipt'])->name('payments.receipt');
});

use App\Http\Controllers\PosController;

Route::middleware(['auth', 'staff'])->prefix('pos')->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('pos.index');
    Route::post('/store', [PosController::class, 'store'])->name('pos.store');
    Route::get('/product/{id}', [PosController::class, 'getProductDetails'])->name('pos.productDetails');
    Route::get('/print/{id}', [PosController::class, 'print'])->name('pos.print');
});
