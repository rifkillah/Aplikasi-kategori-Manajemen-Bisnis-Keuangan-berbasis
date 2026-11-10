<?php

use App\Http\Controllers\AppearanceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockLogController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TenantSettingController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart', [DashboardController::class, 'chartData'])->name('dashboard.chart');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tampilan pribadi: mode terang/gelap dan latar belakang.
    Route::get('/appearance', [AppearanceController::class, 'edit'])->name('appearance.edit');
    Route::patch('/appearance', [AppearanceController::class, 'update'])->name('appearance.update');
    Route::post('/appearance/theme', [AppearanceController::class, 'theme'])->name('appearance.theme');

    // Kasir & transaksi
    Route::middleware('role:owner|kasir')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/lookup', [PosController::class, 'lookup'])
            ->middleware('throttle:60,1')
            ->name('pos.lookup');
        Route::post('/pos', [PosController::class, 'store'])
            ->middleware('throttle:60,1')
            ->name('pos.store');

        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::get('/transactions/{transaction}/pdf', [TransactionController::class, 'pdf'])->name('transactions.pdf');
        Route::post('/transactions/{transaction}/void', [TransactionController::class, 'void'])->name('transactions.void');

        Route::resource('customers', CustomerController::class);
    });

    // Inventaris
    Route::middleware('role:owner|gudang')->group(function () {
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);

        Route::get('/stock', [StockLogController::class, 'index'])->name('stock.index');
        Route::get('/stock/create', [StockLogController::class, 'create'])->name('stock.create');
        Route::post('/stock', [StockLogController::class, 'store'])->name('stock.store');
    });

    // Owner only
    Route::middleware('role:owner')->group(function () {
        Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::get('/finance/create', [FinanceController::class, 'create'])->name('finance.create');
        Route::post('/finance', [FinanceController::class, 'store'])->name('finance.store');
        Route::delete('/finance/{transaction}', [FinanceController::class, 'destroy'])->name('finance.destroy');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

        Route::get('/team', [TeamController::class, 'index'])->name('team.index');
        Route::post('/team', [TeamController::class, 'store'])->name('team.store');
        Route::patch('/team/{member}', [TeamController::class, 'update'])->name('team.update');
        Route::delete('/team/{member}', [TeamController::class, 'destroy'])->name('team.destroy');

        Route::get('/settings', [TenantSettingController::class, 'edit'])->name('settings.edit');
        Route::patch('/settings', [TenantSettingController::class, 'update'])->name('settings.update');
    });
});

require __DIR__.'/auth.php';
