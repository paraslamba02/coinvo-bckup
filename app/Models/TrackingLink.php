<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TrackingLink extends Model
{
    protected $fillable = [
        'funnel_id',
        'name',
        'source',
        'slug',
        'short_code',
        'is_active',
        'click_count',
        'unique_visitors',
        'last_clicked_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'click_count' => 'integer',
            'unique_visitors' => 'integer',
            'last_clicked_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    // Relationships
    public function funnel()
    {
        return $this->belongsTo(Funnel::class);
    }

    public function linkClicks()
    {
        return $this->hasMany(LinkClick::class);
    }

    // Helper methods
    public function getFullUrlAttribute()
    {
        if ($this->funnel && $this->funnel->base_url) {
            return rtrim($this->funnel->base_url, '/') . '/' . $this->slug;
        }
        // Fallback to old system if base_url not set
        return url('/' . $this->funnel->slug . '/' . $this->slug);
    }

    public function getShortUrlAttribute()
    {
        return $this->short_code ? url('/l/' . $this->short_code) : null;
    }

    public static function generateUniqueSlug($source = null)
    {
        do {
            $slug = $source ? strtolower($source) . '-' . Str::random(4) : Str::random(6);
        } while (static::where('slug', $slug)->exists());
        
        return $slug;
    }

    public static function generateUniqueShortCode()
    {
        do {
            $code = Str::random(6);
        } while (static::where('short_code', $code)->exists());
        
        return $code;
    }

    public function incrementClick($sessionId, $ipAddress, $userAgent = null)
    {
        $this->increment('click_count');
        $this->update(['last_clicked_at' => now()]);

        // Check if it's a unique visitor
        $existingClick = $this->linkClicks()
            ->where('session_id', $sessionId)
            ->orWhere('ip_address', $ipAddress)
            ->exists();

        if (!$existingClick) {
            $this->increment('unique_visitors');
        }
    }
}
