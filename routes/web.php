<?php

use App\Http\Controllers\AffiliateUsersController;
use App\Http\Controllers\FunnelController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    $ourInviteCode = env('AFFILIATE_INVITE_CODE', 'COINVO2024');
    
    // Funnel Analytics
    $step1Users = App\Models\AffiliateUser::where('invite_code', $ourInviteCode)->count(); // Step 1: Total signups
    $step2Users = App\Models\AffiliateUser::where('invite_code', $ourInviteCode)->whereNotNull('step2_completed_at')->count(); // Step 2: Users who deposited
    $step3Users = App\Models\AffiliateUser::where('invite_code', $ourInviteCode)->whereNotNull('step3_completed_at')->count(); // Step 3: Users who claimed rewards
    
    // Conversion rates
    $step1ToStep2Rate = $step1Users > 0 ? round(($step2Users / $step1Users) * 100, 1) : 0;
    $step2ToStep3Rate = $step2Users > 0 ? round(($step3Users / $step2Users) * 100, 1) : 0;
    $overallConversionRate = $step1Users > 0 ? round(($step3Users / $step1Users) * 100, 1) : 0;
    
    // Earnings calculations
    $totalEarnings = App\Models\AffiliateUser::where('invite_code', $ourInviteCode)
        ->whereNotNull('step2_completed_at')
        ->join('funnels', 'affiliate_users.platform', '=', 'funnels.platform')
        ->sum('funnels.affiliate_earnings_amount') ?? 0;
    
    $monthlyEarnings = App\Models\AffiliateUser::where('invite_code', $ourInviteCode)
        ->whereNotNull('step2_completed_at')
        ->whereMonth('step2_completed_at', now()->month)
        ->whereYear('step2_completed_at', now()->year)
        ->join('funnels', 'affiliate_users.platform', '=', 'funnels.platform')
        ->sum('funnels.affiliate_earnings_amount') ?? 0;
    
    $platformStats = App\Models\AffiliateUser::where('invite_code', $ourInviteCode)
        ->select('platform')
        ->selectRaw('count(*) as count')
        ->groupBy('platform')
        ->pluck('count', 'platform')
        ->toArray();
    
    $recentUsers = App\Models\AffiliateUser::where('invite_code', $ourInviteCode)
        ->orderBy('register_time', 'desc')
        ->limit(5)
        ->get();
    
    return Inertia::render('Dashboard', [
        'funnel_stats' => [
            'step1_signups' => $step1Users,
            'step2_deposits' => $step2Users,
            'step3_rewards' => $step3Users,
            'step1_to_step2_rate' => $step1ToStep2Rate,
            'step2_to_step3_rate' => $step2ToStep3Rate,
            'overall_conversion_rate' => $overallConversionRate,
            'total_earnings' => $totalEarnings,
            'monthly_earnings' => $monthlyEarnings,
        ],
        'platform_stats' => $platformStats,
        'recent_users' => $recentUsers,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin and Superuser routes
Route::middleware(['auth', 'verified', 'role:admin,superuser'])->group(function () {
    Route::get('affiliates', [AffiliateUsersController::class, 'affiliates'])->name('affiliates');
    Route::resource('funnels', FunnelController::class)->except(['show', 'create', 'edit']);
});

// Superuser only routes
Route::middleware(['auth', 'verified', 'role:superuser'])->group(function () {
    Route::get('users', [AffiliateUsersController::class, 'users'])->name('users');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
