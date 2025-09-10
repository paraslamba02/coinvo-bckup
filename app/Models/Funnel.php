<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funnel extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'heading',
        'sub_heading',
        'image_url',
        'affiliate_url',
        'base_url',
        'affiliate_earnings_amount',
        'platform',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'affiliate_earnings_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
    
    // Relationships
    public function trackingLinks()
    {
        return $this->hasMany(TrackingLink::class);
    }
    
    public function linkClicks()
    {
        return $this->hasMany(LinkClick::class);
    }
}
