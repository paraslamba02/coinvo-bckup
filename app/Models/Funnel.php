<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funnel extends Model
{
    protected $fillable = [
        'name',
        'heading',
        'sub_heading',
        'image_url',
        'affiliate_url',
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
}
