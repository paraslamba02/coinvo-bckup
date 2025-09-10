<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkClick extends Model
{
    protected $fillable = [
        'tracking_link_id',
        'funnel_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'referrer',
        'country',
        'city',
        'session_id',
        'clicked_at',
    ];

    protected function casts(): array
    {
        return [
            'clicked_at' => 'datetime',
        ];
    }

    // Relationships
    public function trackingLink()
    {
        return $this->belongsTo(TrackingLink::class);
    }

    public function funnel()
    {
        return $this->belongsTo(Funnel::class);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('clicked_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('clicked_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('clicked_at', now()->month)
                    ->whereYear('clicked_at', now()->year);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeByDevice($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }
}
