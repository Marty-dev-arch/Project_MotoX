<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\JobOrderController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WorkshopFrontendController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WorkshopFrontendController::class, 'landing'])->name('landing');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('google.callback');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/metrics/inventory', [DashboardController::class, 'inventoryMetrics'])
        ->name('dashboard.metrics.inventory');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::post('/inventory/parts', [InventoryController::class, 'storePart'])->name('inventory.parts.store');
    Route::put('/inventory/parts/{part}', [InventoryController::class, 'updatePart'])->name('inventory.parts.update');
    Route::delete('/inventory/parts/{part}', [InventoryController::class, 'destroyPart'])->name('inventory.parts.destroy');
    Route::post('/inventory/parts/{part}/movements', [InventoryController::class, 'storeMovement'])->name('inventory.parts.movements.store');

    Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('/job-orders', [JobOrderController::class, 'index'])->name('job-orders');
    Route::post('/job-orders', [JobOrderController::class, 'store'])->name('job-orders.store');
    Route::put('/job-orders/{jobOrder}', [JobOrderController::class, 'update'])->name('job-orders.update');
    Route::delete('/job-orders/{jobOrder}', [JobOrderController::class, 'destroy'])->name('job-orders.destroy');

    Route::get('/billing', [BillingController::class, 'index'])->name('billing');
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
