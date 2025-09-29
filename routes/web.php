<?php

use App\Http\Controllers\AffiliateUsersController;
use App\Http\Controllers\FunnelController;
use App\Http\Controllers\TrackingLinkController;
use App\Http\Controllers\LinkRedirectController;
use App\Http\Controllers\SystemUsersController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Admin and Superuser routes
Route::middleware(['auth', 'verified', 'role:admin,superuser'])->group(function () {
    Route::get('affiliates', [AffiliateUsersController::class, 'affiliates'])->name('affiliates');
    Route::resource('funnels', FunnelController::class)->except(['show', 'create', 'edit']);
    
    // Tracking Links routes
    Route::get('tracking-links', [TrackingLinkController::class, 'index'])->name('tracking-links.index');
    Route::post('tracking-links', [TrackingLinkController::class, 'store'])->name('tracking-links.store');
    Route::put('tracking-links/{trackingLink}', [TrackingLinkController::class, 'update'])->name('tracking-links.update');
    Route::delete('tracking-links/{trackingLink}', [TrackingLinkController::class, 'destroy'])->name('tracking-links.destroy');
    Route::get('tracking-links/{trackingLink}/analytics', [TrackingLinkController::class, 'analytics'])->name('tracking-links.analytics');
    Route::patch('tracking-links/{trackingLink}/toggle', [TrackingLinkController::class, 'toggle'])->name('tracking-links.toggle');
    Route::delete('tracking-links/bulk', [TrackingLinkController::class, 'bulkDelete'])->name('tracking-links.bulk-delete');
});

// Superuser only routes
Route::middleware(['auth', 'verified', 'role:superuser'])->group(function () {
    Route::get('users', [AffiliateUsersController::class, 'users'])->name('users');
    
    // System Users Management
    Route::get('system-users', [SystemUsersController::class, 'index'])->name('system-users.index');
    Route::post('system-users', [SystemUsersController::class, 'store'])->name('system-users.store');
    Route::put('system-users/{user}', [SystemUsersController::class, 'update'])->name('system-users.update');
    Route::delete('system-users/{user}', [SystemUsersController::class, 'destroy'])->name('system-users.destroy');
    Route::patch('system-users/{user}/toggle-status', [SystemUsersController::class, 'toggleStatus'])->name('system-users.toggle-status');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

// Public redirect routes (no authentication required) - MUST be at the very end
Route::get('/l/{shortCode}', [LinkRedirectController::class, 'redirectShort'])->name('link.redirect.short');
Route::get('/{funnelSlug}/{trackingCode}', [LinkRedirectController::class, 'redirect'])->name('link.redirect');
Route::get('/{slug}', [LinkRedirectController::class, 'redirectDirect'])->name('link.redirect.direct');
