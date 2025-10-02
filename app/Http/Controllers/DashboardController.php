<?php

namespace App\Http\Controllers;

use App\Models\AffiliateUser;
use App\Models\LinkClick;
use App\Models\ConversionEvent;
use App\Models\TrackingLink;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $ourInviteCode = env('AFFILIATE_INVITE_CODE', 'COINVO2024');

        // Get date range from request or default to last 30 days
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->subDays(30);
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now();

        // Landing page visitors (from tracking links)
        $landingPageVisitors = LinkClick::whereBetween('clicked_at', [$startDate, $endDate])->count();
        $uniqueLandingVisitors = LinkClick::whereBetween('clicked_at', [$startDate, $endDate])
            ->distinct('ip_address')
            ->count('ip_address');

        // Traditional funnel analytics (using existing affiliate users)
        $step1Users = AffiliateUser::where('invite_code', $ourInviteCode)
            ->whereBetween('register_time', [$startDate, $endDate])
            ->count();
        $step2Users = AffiliateUser::where('invite_code', $ourInviteCode)
            ->whereNotNull('step2_completed_at')
            ->whereBetween('step2_completed_at', [$startDate, $endDate])
            ->count();
        $step3Users = AffiliateUser::where('invite_code', $ourInviteCode)
            ->whereNotNull('step3_completed_at')
            ->whereBetween('step3_completed_at', [$startDate, $endDate])
            ->count();

        // Conversion rates with landing page
        $landingToStep1Rate = $uniqueLandingVisitors > 0 ? round(($step1Users / $uniqueLandingVisitors) * 100, 1) : 0;
        $step1ToStep2Rate = $step1Users > 0 ? round(($step2Users / $step1Users) * 100, 1) : 0;
        $step2ToStep3Rate = $step2Users > 0 ? round(($step3Users / $step2Users) * 100, 1) : 0;
        $overallConversionRate = $uniqueLandingVisitors > 0 ? round(($step3Users / $uniqueLandingVisitors) * 100, 1) : 0;

        // Total conversions (step 3 completions)
        $totalConversions = $step3Users;

        // Source breakdown - visitors (from link clicks) and conversions (from user signups)
        $sourceStats = [];

        // Get visitor data from tracking links
        $visitorData = LinkClick::join('tracking_links', 'link_clicks.tracking_link_id', '=', 'tracking_links.id')
            ->whereNotNull('tracking_links.source')
            ->whereBetween('link_clicks.clicked_at', [$startDate, $endDate])
            ->selectRaw('tracking_links.source, COUNT(DISTINCT link_clicks.session_id) as visitors, COUNT(*) as clicks')
            ->groupBy('tracking_links.source')
            ->get();

        // Get conversion data from user signups
        $conversionData = AffiliateUser::where('invite_code', $ourInviteCode)
            ->whereNotNull('traffic_source')
            ->whereBetween('register_time', [$startDate, $endDate])
            ->selectRaw('traffic_source, COUNT(*) as conversions')
            ->groupBy('traffic_source')
            ->get()
            ->keyBy('traffic_source');

        // Combine visitor and conversion data
        foreach ($visitorData as $item) {
            $sourceName = ucfirst(strtolower($item->source));
            $sourceStats[$sourceName] = [
                'visitors' => $item->visitors,
                'clicks' => $item->clicks,
                'conversions' => $conversionData->get($item->source)?->conversions ?? 0
            ];
        }

        // Add sources that have conversions but no visitor data (edge case)
        foreach ($conversionData as $source => $data) {
            $sourceName = ucfirst(strtolower($source));
            if (!isset($sourceStats[$sourceName])) {
                $sourceStats[$sourceName] = [
                    'visitors' => 0,
                    'clicks' => 0,
                    'conversions' => $data->conversions
                ];
            }
        }

        // Sort by visitors descending
        uasort($sourceStats, function ($a, $b) {
            return $b['visitors'] <=> $a['visitors'];
        });

        // Device breakdown from link clicks
        $deviceStats = LinkClick::whereBetween('clicked_at', [$startDate, $endDate])
            ->whereNotNull('device_type')
            ->selectRaw('device_type, COUNT(DISTINCT session_id) as visitors')
            ->groupBy('device_type')
            ->orderBy('visitors', 'desc')
            ->pluck('visitors', 'device_type')
            ->toArray();

        // Platform distribution from actual user signups by platform
        $platformStats = AffiliateUser::where('invite_code', $ourInviteCode)
            ->whereBetween('register_time', [$startDate, $endDate])
            ->whereNotNull('platform')
            ->selectRaw('platform, COUNT(*) as signups')
            ->groupBy('platform')
            ->orderBy('signups', 'desc')
            ->pluck('signups', 'platform')
            ->toArray();

        // Recent users
        $recentUsers = AffiliateUser::where('invite_code', $ourInviteCode)
            ->orderBy('register_time', 'desc')
            ->limit(5)
            ->get();

        // Calculate drop-off rates
        $dropoffRates = [
            'landing_to_step1' => $uniqueLandingVisitors > 0 ? round((($uniqueLandingVisitors - $step1Users) / $uniqueLandingVisitors) * 100, 1) : 0,
            'step1_to_step2' => $step1Users > 0 ? round((($step1Users - $step2Users) / $step1Users) * 100, 1) : 0,
            'step2_to_step3' => $step2Users > 0 ? round((($step2Users - $step3Users) / $step2Users) * 100, 1) : 0,
        ];

        return Inertia::render('Dashboard', [
            'funnel_stats' => [
                'landing_visitors' => $uniqueLandingVisitors,
                'total_landing_clicks' => $landingPageVisitors,
                'step1_signups' => $step1Users,
                'step2_deposits' => $step2Users,
                'step3_rewards' => $step3Users,
                'total_conversions' => $totalConversions,
                'landing_to_step1_rate' => $landingToStep1Rate,
                'step1_to_step2_rate' => $step1ToStep2Rate,
                'step2_to_step3_rate' => $step2ToStep3Rate,
                'overall_conversion_rate' => $overallConversionRate,
                'dropoff_rates' => $dropoffRates,
            ],
            'source_stats' => $sourceStats,
            'device_stats' => $deviceStats,
            'platform_stats' => $platformStats,
            'recent_users' => $recentUsers,
            'date_range' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
        ]);
    }
}