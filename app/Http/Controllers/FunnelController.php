<?php

namespace App\Http\Controllers;

use App\Models\Funnel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FunnelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $funnels = Funnel::with(['trackingLinks' => function($query) {
                $query->where('is_active', true)
                      ->latest()
                      ->limit(3);
            }])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return Inertia::render('Funnels', [
            'funnels' => $funnels,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'heading' => 'required|string|max:255',
            'sub_heading' => 'nullable|string',
            'image_url' => 'nullable|url',
            'affiliate_url' => 'required|url',
            'base_url' => 'nullable|url',
            'affiliate_earnings_amount' => 'required|numeric|min:0',
            'platform' => 'required|string|max:255',
        ]);

        Funnel::create($validated);

        return redirect()->back()->with('success', 'Funnel created successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Funnel $funnel)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'heading' => 'required|string|max:255',
            'sub_heading' => 'nullable|string',
            'image_url' => 'nullable|url',
            'affiliate_url' => 'required|url',
            'base_url' => 'nullable|url',
            'affiliate_earnings_amount' => 'required|numeric|min:0',
            'platform' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $funnel->update($validated);

        return redirect()->back()->with('success', 'Funnel updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Funnel $funnel)
    {
        $funnel->delete();

        return redirect()->back()->with('success', 'Funnel deleted successfully!');
    }
}
