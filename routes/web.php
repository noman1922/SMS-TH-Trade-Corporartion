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
});

use App\Http\Controllers\PosController;

Route::middleware(['auth', 'staff'])->prefix('pos')->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('pos.index');
    Route::post('/store', [PosController::class, 'store'])->name('pos.store');
    Route::get('/product/{id}', [PosController::class, 'getProductDetails'])->name('pos.productDetails');
    Route::get('/print/{id}', [PosController::class, 'print'])->name('pos.print');
});
