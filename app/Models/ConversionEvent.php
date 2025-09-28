<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversionEvent extends Model
{
    protected $fillable = [
        'tracking_link_id',
        'funnel_id',
        'session_id',
        'ip_address',
        'event_type',
        'event_category',
        'event_data',
        'page_url',
        'referrer_url',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'step_number',
        'time_spent',
        'revenue',
        'device_type',
        'browser',
        'os',
        'country',
        'city',
        'event_timestamp',
    ];

    protected function casts(): array
    {
        return [
            'event_data' => 'array',
            'time_spent' => 'decimal:2',
            'revenue' => 'decimal:2',
            'event_timestamp' => 'datetime',
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
    public function scopeByEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeBySession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeConversions($query)
    {
        return $query->where('event_category', 'conversion');
    }

    public function scopeEngagements($query)
    {
        return $query->where('event_category', 'engagement');
    }

    public function scopeByFunnelStep($query, $step)
    {
        return $query->where('step_number', $step);
    }

    // Helper methods
    public static function recordEvent($trackingLinkId, $funnelId, $sessionId, $eventType, array $data = [])
    {
        $defaultData = [
            'tracking_link_id' => $trackingLinkId,
            'funnel_id' => $funnelId,
            'session_id' => $sessionId,
            'event_type' => $eventType,
            'event_timestamp' => now(),
            'ip_address' => request()->ip(),
        ];

        return static::create(array_merge($defaultData, $data));
    }
}
