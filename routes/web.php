<?php

use App\Http\Controllers\Admin\RateLimitController;
use App\Http\Controllers\Admin\SecurityDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'status'])
    ->name('dashboard');

Route::middleware(['auth', 'status', 'throttle.profile'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin-only routes
Route::middleware(['auth', 'verified', 'status', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    // Placeholder for future admin routes
    Route::get('/users', function () {
        return inertia('Admin/Users');
    })->name('users');

    Route::get('/dashboard', function () {
        return inertia('Admin/Dashboard');
    })->name('dashboard');

    // Security Dashboard routes
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/', [SecurityDashboardController::class, 'index'])->name('dashboard');
        Route::get('/events', [SecurityDashboardController::class, 'events'])->name('events');
        Route::get('/suspicious', [SecurityDashboardController::class, 'suspicious'])->name('suspicious');
    });

    // Rate Limiting Dashboard routes
    Route::prefix('rate-limits')->name('rate_limits.')->group(function () {
        Route::get('/', [RateLimitController::class, 'index'])->name('dashboard');
        Route::get('/stats', [RateLimitController::class, 'stats'])->name('stats');
        Route::get('/active', [RateLimitController::class, 'activeThrottles'])->name('active');
        Route::get('/analytics', [RateLimitController::class, 'analytics'])->name('analytics');
        Route::get('/export', [RateLimitController::class, 'export'])->name('export');
        Route::post('/reset', [RateLimitController::class, 'reset'])->name('reset');
    });
});

// Researcher-specific routes
Route::middleware(['auth', 'verified', 'status', 'role:researcher,super_admin'])->prefix('research')->name('research.')->group(function () {
    // Placeholder for future research routes
    Route::get('/data', function () {
        return inertia('Research/Data');
    })->name('data');

    Route::get('/analytics', function () {
        return inertia('Research/Analytics');
    })->name('analytics');
});

require __DIR__.'/auth.php';
