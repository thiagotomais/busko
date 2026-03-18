<?php

use App\Http\Controllers\Auth\DriverRegisterController;
use App\Http\Controllers\Auth\GuardianRegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * ============================================
 * API Routes - REST Endpoints
 * Prefix: /api/v1/
 * ============================================
 */

Route::prefix('v1')->name('api.v1.')->group(function () {
    /**
     * Authentication Routes (Public)
     */
    Route::prefix('auth')->name('auth.')->group(function () {
        // Register endpoints
        Route::post('drivers/register', [DriverRegisterController::class, 'store'])->name('drivers.register');
        Route::post('guardians/register', [GuardianRegisterController::class, 'store'])->name('guardians.register');
        
        // Login endpoint
        Route::post('login', [LoginController::class, 'store'])->name('login');
    });

    /**
     * Protected Routes (Require Bearer Token Authentication)
     */
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('auth')->name('auth.')->group(function () {
            // Get authenticated user info
            Route::get('me', [MeController::class, 'index'])->name('me');
            
            // Logout endpoint
            Route::post('logout', [LogoutController::class, 'store'])->name('logout');
        });
    });
});
