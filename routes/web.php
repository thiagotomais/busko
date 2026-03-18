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
    
    // Drivers Management
    Route::prefix('drivers')->name('drivers.')->group(function () {
        Route::get('/', [DashboardController::class, 'drivers'])->name('index');
        Route::get('/{driver}', [DashboardController::class, 'driverShow'])->name('show');
    });
    
    // Guardians Management
    Route::prefix('guardians')->name('guardians.')->group(function () {
        Route::get('/', [DashboardController::class, 'guardians'])->name('index');
        Route::get('/{guardian}', [DashboardController::class, 'guardianShow'])->name('show');
    });
    
    // Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});
