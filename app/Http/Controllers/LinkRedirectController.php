<?php

namespace App\Http\Controllers;

use App\Models\TrackingLink;
use App\Models\LinkClick;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Jenssegers\Agent\Agent;

class LinkRedirectController extends Controller
{
    public function redirect(Request $request, $funnelSlug, $slug)
    {
        $trackingLink = TrackingLink::whereHas('funnel', function ($query) use ($funnelSlug) {
            $query->where('slug', $funnelSlug)->where('is_active', true);
        })
        ->where('slug', $slug)
        ->where('is_active', true)
        ->first();

        if (!$trackingLink) {
            abort(404);
        }

        if ($trackingLink->expires_at && $trackingLink->expires_at->isPast()) {
            abort(410, 'Link has expired');
        }

        $this->recordClick($request, $trackingLink);

        return redirect($trackingLink->funnel->affiliate_url);
    }

    public function redirectShort(Request $request, $shortCode)
    {
        $trackingLink = TrackingLink::where('short_code', $shortCode)
            ->where('is_active', true)
            ->whereHas('funnel', function ($query) {
                $query->where('is_active', true);
            })
            ->first();

        if (!$trackingLink) {
            abort(404);
        }

        if ($trackingLink->expires_at && $trackingLink->expires_at->isPast()) {
            abort(410, 'Link has expired');
        }

        $this->recordClick($request, $trackingLink);

        return redirect($trackingLink->funnel->affiliate_url);
    }

    private function recordClick(Request $request, TrackingLink $trackingLink)
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $sessionId = Session::getId();
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        $deviceType = $agent->isMobile() ? 'mobile' : ($agent->isTablet() ? 'tablet' : 'desktop');
        $browser = $agent->browser();
        $os = $agent->platform();
        $referrer = $request->header('referer');

        $country = null;
        $city = null;

        LinkClick::create([
            'tracking_link_id' => $trackingLink->id,
            'funnel_id' => $trackingLink->funnel_id,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_type' => $deviceType,
            'browser' => $browser,
            'os' => $os,
            'referrer' => $referrer,
            'country' => $country,
            'city' => $city,
            'session_id' => $sessionId,
            'clicked_at' => now(),
        ]);

        $trackingLink->incrementClick($sessionId, $ipAddress, $userAgent);
    }
}