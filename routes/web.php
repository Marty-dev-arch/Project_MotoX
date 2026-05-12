<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\JobOrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SystemLogController;
use App\Http\Controllers\WorkshopFrontendController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WorkshopFrontendController::class, 'landing'])->name('landing');
Route::get('/metrics/landing', [WorkshopFrontendController::class, 'landingMetrics'])->name('landing.metrics');
Route::get('/policies', [WorkshopFrontendController::class, 'policies'])->name('policies');
Route::get('/privacy', [WorkshopFrontendController::class, 'privacy'])->name('privacy');
Route::get('/cookies', [WorkshopFrontendController::class, 'cookies'])->name('cookies');
Route::get('/support', [WorkshopFrontendController::class, 'support'])->name('support');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->name('password.email');
    Route::get('/forgot-password/verify', [AuthController::class, 'showVerifyOtp'])->name('password.otp.form');
    Route::post('/forgot-password/verify', [AuthController::class, 'verifyOtp'])->name('password.otp.verify');
    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('google.callback');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'no-cache'])->group(function (): void {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/metrics/inventory', [DashboardController::class, 'inventoryMetrics'])
            ->name('dashboard.metrics.inventory');

        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
        Route::post('/inventory/parts', [InventoryController::class, 'storePart'])->name('inventory.parts.store');
        Route::put('/inventory/parts/{part}', [InventoryController::class, 'updatePart'])->name('inventory.parts.update');
        Route::delete('/inventory/parts/{part}', [InventoryController::class, 'destroyPart'])->name('inventory.parts.destroy');
        Route::post('/inventory/parts/{part}/movements', [InventoryController::class, 'storeMovement'])->name('inventory.parts.movements.store');

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
        Route::get('/customers/metrics', [CustomerController::class, 'metrics'])->name('customers.metrics');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        Route::get('/job-orders', [JobOrderController::class, 'index'])->name('job-orders');
        Route::get('/job-orders/metrics', [JobOrderController::class, 'metrics'])->name('job-orders.metrics');
        Route::post('/job-orders', [JobOrderController::class, 'store'])->name('job-orders.store');
        Route::put('/job-orders/{jobOrder}', [JobOrderController::class, 'update'])->name('job-orders.update');
        Route::delete('/job-orders/{jobOrder}', [JobOrderController::class, 'destroy'])->name('job-orders.destroy');

        Route::get('/billing', [BillingController::class, 'index'])->name('billing');
        Route::get('/billing/metrics', [BillingController::class, 'metrics'])->name('billing.metrics');
        Route::get('/billing/export', [BillingController::class, 'export'])->name('billing.export');
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
        Route::get('/reports/metrics', [ReportsController::class, 'metrics'])->name('reports.metrics');
        Route::get('/reports/export', [ReportsController::class, 'export'])->name('reports.export');
        Route::get('/logs', [SystemLogController::class, 'index'])->name('logs');
        Route::delete('/logs', [SystemLogController::class, 'destroyAll'])->name('logs.destroy-all');
        Route::delete('/logs/{log}', [SystemLogController::class, 'destroy'])->name('logs.destroy');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});
