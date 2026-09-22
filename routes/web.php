<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Auth::routes();
});

Route::middleware(['auth'])->group(function () {
    // Akses Kasir & Riwayat (Dapat diakses Admin + User/Kasir)
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');
    
    // Riwayat & Manajemen Koreksi Transaksi
    Route::get('/history', [DashboardController::class, 'history'])->name('sales.history');
    Route::put('/sales/{sale}', [DashboardController::class, 'updateSale'])->name('sales.update');
    Route::delete('/sales/{sale}', [DashboardController::class, 'destroySale'])->name('sales.destroy');

    // Khusus Admin
    Route::middleware(['admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('/products', ProductController::class)->except(['create', 'edit', 'show']);
        
        // Route Export Sales Data
        Route::get('/sales/export', [DashboardController::class, 'exportCsv'])->name('sales.export');
    });
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});