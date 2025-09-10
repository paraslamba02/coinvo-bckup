<?php

namespace App\Http\Controllers;

use App\Models\AffiliateUser;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AffiliateUsersController extends Controller
{
    public function index(Request $request)
    {
        $query = AffiliateUser::query();

        // Filter by platform
        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        // Filter by KYC status
        if ($request->filled('kyc_status')) {
            $query->where('kyc_status', $request->boolean('kyc_status'));
        }

        // Filter by invite code
        if ($request->filled('invite_code')) {
            $query->where('invite_code', $request->invite_code);
        }

        // Search by UID
        if ($request->filled('search')) {
            $query->where('uid', 'like', '%' . $request->search . '%');
        }

        // Filter by our invite code only
        $ourInviteCode = env('AFFILIATE_INVITE_CODE', 'COINVO2024');
        if ($request->boolean('our_users_only', true)) {
            $query->where('invite_code', $ourInviteCode);
        }

        $users = $query->orderBy('register_time', 'desc')
            ->paginate(15)
            ->withQueryString();

        $platforms = AffiliateUser::select('platform')
            ->distinct()
            ->pluck('platform')
            ->sort()
            ->values();

        $stats = [
            'total_users' => AffiliateUser::count(),
            'our_users' => AffiliateUser::where('invite_code', $ourInviteCode)->count(),
            'kyc_verified' => AffiliateUser::where('kyc_status', true)->count(),
            'active_traders' => AffiliateUser::whereNotNull('last_trade_time')->count(),
        ];

        return Inertia::render('Users', [
            'users' => $users,
            'platforms' => $platforms,
            'stats' => $stats,
            'filters' => $request->only(['platform', 'kyc_status', 'invite_code', 'search', 'our_users_only']),
            'our_invite_code' => $ourInviteCode,
        ]);
    }

    public function affiliates(Request $request)
    {
        $ourInviteCode = env('AFFILIATE_INVITE_CODE', 'COINVO2024');
        
        // Only show users with our invite code
        $query = AffiliateUser::where('invite_code', $ourInviteCode);

        // Filter by platform
        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        // Filter by Step 2 status (using step2_completed_at as deposit indicator)
        if ($request->filled('kyc_status')) {
            if ($request->boolean('kyc_status')) {
                $query->whereNotNull('step2_completed_at');
            } else {
                $query->whereNull('step2_completed_at');
            }
        }

        // Search by UID
        if ($request->filled('search')) {
            $query->where('uid', 'like', '%' . $request->search . '%');
        }

        $perPage = $request->input('per_page', 15);
        $users = $query->orderBy('register_time', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $platforms = AffiliateUser::where('invite_code', $ourInviteCode)
            ->select('platform')
            ->distinct()
            ->pluck('platform')
            ->sort()
            ->values();

        $stats = [
            'total_users' => AffiliateUser::where('invite_code', $ourInviteCode)->count(),
            'step2_completed' => AffiliateUser::where('invite_code', $ourInviteCode)->whereNotNull('step2_completed_at')->count(),
            'step3_completed' => AffiliateUser::where('invite_code', $ourInviteCode)->whereNotNull('step3_completed_at')->count(),
            'rewards_claimed' => AffiliateUser::where('invite_code', $ourInviteCode)->where('reward_status', 'claimed')->count(),
        ];

        return Inertia::render('Affiliates', [
            'users' => $users,
            'platforms' => $platforms,
            'stats' => $stats,
            'filters' => $request->only(['platform', 'kyc_status', 'search', 'per_page']),
            'our_invite_code' => $ourInviteCode,
        ]);
    }

    public function users(Request $request)
    {
        // Show actual user accounts (admin/superuser)
        $query = User::with('role');

        // Search by name or email
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('role', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $roles = ['admin', 'superuser', 'user'];

        return Inertia::render('Users', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search', 'role']),
            'is_user_management' => true,
        ]);
    }
}
