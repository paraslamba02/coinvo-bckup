<?php

namespace App\Http\Controllers;

use App\Models\TrackingLink;
use App\Models\Funnel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class TrackingLinkController extends Controller
{
    public function index(Request $request)
    {
        $query = TrackingLink::with(['funnel', 'linkClicks']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%");
            });
        }

        if ($request->filled('funnel')) {
            $query->where('funnel_id', $request->get('funnel'));
        }

        if ($request->filled('source')) {
            $query->where('source', $request->get('source'));
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($status === 'expired') {
                $query->where('expires_at', '<', now());
            }
        }

        $trackingLinks = $query->latest()->paginate(15);
        
        // Get unique sources for filter dropdown
        $sources = TrackingLink::distinct()->pluck('source')->filter()->sort()->values();

        return Inertia::render('TrackingLinks', [
            'trackingLinks' => $trackingLinks,
            'funnels' => Funnel::where('is_active', true)->get(),
            'sources' => $sources,
            'filters' => $request->only(['search', 'funnel', 'source', 'status'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'funnel_id' => 'required|exists:funnels,id',
            'name' => 'required|string|max:255',
            'source' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:tracking_links,slug',
            'expires_at' => 'nullable|date|after:now',
            'is_active' => 'boolean'
        ]);

        // If no slug provided, generate one
        if (empty($validated['slug'])) {
            $validated['slug'] = TrackingLink::generateUniqueSlug($validated['source'] ?? null);
        } else {
            // Sanitize the provided slug
            $validated['slug'] = strtolower(str_replace([' ', '_'], '-', trim($validated['slug'])));
        }
        $validated['short_code'] = TrackingLink::generateUniqueShortCode();
        $validated['is_active'] = $validated['is_active'] ?? true;

        TrackingLink::create($validated);

        return Redirect::back()->with('success', 'Tracking link created successfully.');
    }

    public function update(Request $request, TrackingLink $trackingLink)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'source' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:tracking_links,slug,' . $trackingLink->id,
            'expires_at' => 'nullable|date|after:now',
            'is_active' => 'boolean'
        ]);

        // Sanitize the slug
        if (!empty($validated['slug'])) {
            $validated['slug'] = strtolower(str_replace([' ', '_'], '-', trim($validated['slug'])));
        }

        $trackingLink->update($validated);

        return Redirect::back()->with('success', 'Tracking link updated successfully.');
    }

    public function destroy(TrackingLink $trackingLink)
    {
        $trackingLink->delete();

        return Redirect::back()->with('success', 'Tracking link deleted successfully.');
    }

    public function analytics(TrackingLink $trackingLink)
    {
        $analytics = [
            'total_clicks' => $trackingLink->click_count,
            'unique_visitors' => $trackingLink->unique_visitors,
            'today_clicks' => $trackingLink->linkClicks()->today()->count(),
            'this_week_clicks' => $trackingLink->linkClicks()->thisWeek()->count(),
            'this_month_clicks' => $trackingLink->linkClicks()->thisMonth()->count(),
            'clicks_by_country' => $trackingLink->linkClicks()
                ->selectRaw('country, COUNT(*) as count')
                ->whereNotNull('country')
                ->groupBy('country')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'clicks_by_device' => $trackingLink->linkClicks()
                ->selectRaw('device_type, COUNT(*) as count')
                ->whereNotNull('device_type')
                ->groupBy('device_type')
                ->get(),
            'recent_clicks' => $trackingLink->linkClicks()
                ->latest('clicked_at')
                ->limit(20)
                ->get()
        ];

        return response()->json($analytics);
    }

    public function toggle(TrackingLink $trackingLink)
    {
        $trackingLink->update(['is_active' => !$trackingLink->is_active]);

        return Redirect::back()->with('success', 'Tracking link status updated.');
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tracking_links,id'
        ]);

        TrackingLink::whereIn('id', $validated['ids'])->delete();

        return Redirect::back()->with('success', count($validated['ids']) . ' tracking links deleted successfully.');
    }
}