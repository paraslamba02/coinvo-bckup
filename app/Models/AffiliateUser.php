<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AffiliateUser extends Model
{
    protected $fillable = [
        'uid',
        'platform',
        'register_time',
        'funnel_clicked_at',
        'invite_code',
        'first_deposit_time',
        'first_trade_time',
        'last_deposit_time',
        'last_trade_time',
        'funnel_step',
        'step1_completed_at',
        'step2_completed_at',
        'step3_completed_at',
        'deposit_amount',
        'reward_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'register_time' => 'datetime',
            'funnel_clicked_at' => 'datetime',
            'first_deposit_time' => 'datetime',
            'first_trade_time' => 'datetime',
            'last_deposit_time' => 'datetime',
            'last_trade_time' => 'datetime',
            'step1_completed_at' => 'datetime',
            'step2_completed_at' => 'datetime',
            'step3_completed_at' => 'datetime',
            'deposit_amount' => 'decimal:2',
        ];
    }

    public function scopeByInviteCode(Builder $query, ?string $inviteCode): Builder
    {
        if ($inviteCode) {
            return $query->where('invite_code', $inviteCode);
        }
        return $query;
    }

    public function scopeByPlatform(Builder $query, ?string $platform): Builder
    {
        if ($platform) {
            return $query->where('platform', $platform);
        }
        return $query;
    }

    public function scopeByFunnelStep(Builder $query, ?string $step): Builder
    {
        if ($step) {
            return $query->where('funnel_step', $step);
        }
        return $query;
    }

    public function scopeByRewardStatus(Builder $query, ?string $status): Builder
    {
        if ($status) {
            return $query->where('reward_status', $status);
        }
        return $query;
    }

    public function scopeStep1Completed(Builder $query): Builder
    {
        return $query->whereNotNull('step1_completed_at');
    }

    public function scopeStep2Completed(Builder $query): Builder
    {
        return $query->whereNotNull('step2_completed_at');
    }

    public function scopeStep3Completed(Builder $query): Builder
    {
        return $query->whereNotNull('step3_completed_at');
    }
    
    // Helper methods
    public function getFunnelProgressAttribute(): int
    {
        if ($this->step3_completed_at) return 3;
        if ($this->step2_completed_at) return 2;
        if ($this->step1_completed_at) return 1;
        return 0;
    }

    public function getFunnelProgressPercentAttribute(): int
    {
        return ($this->funnel_progress / 3) * 100;
    }
}
