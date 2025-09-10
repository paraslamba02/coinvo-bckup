<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FunnelAnalytics extends Model
{
    protected $fillable = [
        'session_id',
        'page_type',
        'action_type',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'referrer',
        'user_agent',
        'ip_address',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function scopeByPageType(Builder $query, string $pageType): Builder
    {
        return $query->where('page_type', $pageType);
    }

    public function scopeByActionType(Builder $query, string $actionType): Builder
    {
        return $query->where('action_type', $actionType);
    }

    public function scopeByCampaign(Builder $query, ?string $campaign): Builder
    {
        if ($campaign) {
            return $query->where('utm_campaign', $campaign);
        }
        return $query;
    }

    public function scopeByDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
