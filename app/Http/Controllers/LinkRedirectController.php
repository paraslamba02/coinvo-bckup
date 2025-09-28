<?php

namespace App\Http\Controllers;

use App\Models\TrackingLink;
use App\Models\LinkClick;
use App\Models\ConversionEvent;
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

        // Redirect to base_url (the funnel page) instead of affiliate_url (referral link)
        $redirectUrl = $trackingLink->funnel->base_url ?: $trackingLink->funnel->affiliate_url;
        return redirect($redirectUrl);
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

        // Redirect to base_url (the funnel page) instead of affiliate_url (referral link)
        $redirectUrl = $trackingLink->funnel->base_url ?: $trackingLink->funnel->affiliate_url;
        return redirect($redirectUrl);
    }

    public function redirectDirect(Request $request, $slug)
    {
        $trackingLink = TrackingLink::where('slug', $slug)
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

        // Redirect to base_url (the funnel page) instead of affiliate_url (referral link)
        $redirectUrl = $trackingLink->funnel->base_url ?: $trackingLink->funnel->affiliate_url;
        return redirect($redirectUrl);
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

        // Parse UTM parameters
        $utmSource = $request->get('utm_source');
        $utmMedium = $request->get('utm_medium');
        $utmCampaign = $request->get('utm_campaign');
        $utmTerm = $request->get('utm_term');
        $utmContent = $request->get('utm_content');

        $country = null;
        $city = null;

        // Record traditional link click
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

        // Record conversion event for detailed tracking
        ConversionEvent::recordEvent(
            $trackingLink->id,
            $trackingLink->funnel_id,
            $sessionId,
            'click',
            [
                'event_category' => 'engagement',
                'page_url' => $request->fullUrl(),
                'referrer_url' => $referrer,
                'utm_source' => $utmSource,
                'utm_medium' => $utmMedium,
                'utm_campaign' => $utmCampaign,
                'utm_term' => $utmTerm,
                'utm_content' => $utmContent,
                'step_number' => 1, // First step in funnel
                'device_type' => $deviceType,
                'browser' => $browser,
                'os' => $os,
                'country' => $country,
                'city' => $city,
                'event_data' => [
                    'user_agent' => $userAgent,
                    'screen_resolution' => $request->header('sec-ch-viewport-width') . 'x' . $request->header('sec-ch-viewport-height'),
                    'language' => $request->header('accept-language'),
                    'tracking_link_slug' => $trackingLink->slug,
                    'funnel_name' => $trackingLink->funnel->name,
                ]
            ]
        );

        $trackingLink->incrementClick($sessionId, $ipAddress, $userAgent);
    }
}