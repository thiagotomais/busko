<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

/**
 * ============================================
 * PORTAL - Web Routes (Blade Templates)
 * ============================================
 */

// Home - Redirect based on auth status
Route::get('/', function () {
    return auth()->check() ? redirect()->route('portal.dashboard') : redirect()->route('portal.login');
})->name('home');

/**
 * Portal Auth Routes (Public)
 */
Route::prefix('portal')->name('portal.')->middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'login'])->name('login');
    Route::post('login', [AuthController::class, 'store'])->name('login.store');
});

/**
 * Portal Dashboard Routes (Protected)
 */
Route::prefix('portal')->name('portal.')->middleware('auth')->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Company Management
    Route::prefix('company')->name('company.')->group(function () {
        Route::get('/', [DashboardController::class, 'company'])->name('index');
        Route::get('/manage/{tenant}/edit', [DashboardController::class, 'companyEdit'])->name('edit');
        Route::patch('/manage/{tenant}', [DashboardController::class, 'companyAdminUpdate'])->name('admin-update');
        Route::post('/manage/{tenant}/toggle-status', [DashboardController::class, 'companyAdminToggleStatus'])->name('admin-toggle-status');

        // Legacy alias
        Route::get('/show', [DashboardController::class, 'company'])->name('show');
        Route::patch('/', [DashboardController::class, 'companyUpdate'])->name('update');
        Route::post('/toggle-status', [DashboardController::class, 'companyToggleStatus'])->name('toggle-status');
    });

    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [DashboardController::class, 'users'])->name('index');
        Route::get('/create', [DashboardController::class, 'userCreate'])->name('create');
        Route::post('/', [DashboardController::class, 'userStore'])->name('store');
        Route::get('/{user}/edit', [DashboardController::class, 'userEdit'])->name('edit');
        Route::patch('/{user}', [DashboardController::class, 'userUpdate'])->name('update');
        Route::post('/{user}/toggle-status', [DashboardController::class, 'userToggleStatus'])->name('toggle-status');
    });
    
    // Drivers Management
    Route::prefix('drivers')->name('drivers.')->group(function () {
        Route::get('/', [DashboardController::class, 'drivers'])->name('index');
        Route::get('/create', [DashboardController::class, 'driverCreate'])->name('create');
        Route::post('/', [DashboardController::class, 'driverStore'])->name('store');
        Route::get('/{driver}', [DashboardController::class, 'driverShow'])->name('show');
    });
    
    // Guardians Management
    Route::prefix('guardians')->name('guardians.')->group(function () {
        Route::get('/', [DashboardController::class, 'guardians'])->name('index');
        Route::get('/{guardian}', [DashboardController::class, 'guardianShow'])->name('show');
    });

    // Bank autocomplete API
    Route::get('/api/banks/search', [DashboardController::class, 'searchBanks'])->name('api.banks.search');
    
    // Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});
