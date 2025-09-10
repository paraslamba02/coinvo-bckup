<?php

namespace Database\Seeders;

use App\Models\AffiliateUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AffiliateUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = ['WEX', 'Binance', 'Coinbase', 'Kraken', 'Bybit'];
        $inviteCodes = [env('AFFILIATE_INVITE_CODE', 'COINVO2024'), 'OTHER2024', 'RANDOM123'];

        for ($i = 1; $i <= 75; $i++) {
            $funnelClickedTime = now()->subDays(rand(1, 90));
            $registerTime = $funnelClickedTime->copy()->addMinutes(rand(5, 120)); // Register 5 minutes to 2 hours after clicking
            $hasFirstDeposit = rand(1, 100) <= 60; // 60% have made first deposit
            $hasFirstTrade = rand(1, 100) <= 50; // 50% have made first trade
            
            // Funnel progression logic
            $step1Completed = $registerTime; // Everyone completed step 1 (signup)
            $step2Completed = $hasFirstDeposit ? $registerTime->copy()->addDays(rand(1, 15)) : null;
            $step3Completed = $step2Completed && rand(1, 100) <= 30 ? // 30% claim reward after deposit
                $step2Completed->copy()->addDays(rand(1, 7)) : null;
            
            // Determine current funnel step
            $funnelStep = 'step1_signup';
            if ($step3Completed) {
                $funnelStep = 'step3_reward_claimed';
            } elseif ($step2Completed) {
                $funnelStep = 'step2_deposit';
            }

            $depositAmount = $hasFirstDeposit ? rand(100, 10000) : null;
            $rewardStatus = $step3Completed ? 'claimed' : ($step2Completed ? 'pending' : 'not_eligible');

            AffiliateUser::create([
                'uid' => 'USER_' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'platform' => $platforms[array_rand($platforms)],
                'register_time' => $registerTime,
                'funnel_clicked_at' => $funnelClickedTime,
                'invite_code' => $inviteCodes[array_rand($inviteCodes)],
                'first_deposit_time' => $hasFirstDeposit ? $registerTime->copy()->addDays(rand(1, 30)) : null,
                'first_trade_time' => $hasFirstTrade ? $registerTime->copy()->addDays(rand(1, 45)) : null,
                'last_deposit_time' => $hasFirstDeposit && rand(1, 100) <= 40 ? 
                    $registerTime->copy()->addDays(rand(30, 80)) : null,
                'last_trade_time' => $hasFirstTrade && rand(1, 100) <= 60 ? 
                    now()->subDays(rand(1, 15)) : null,
                'funnel_step' => $funnelStep,
                'step1_completed_at' => $step1Completed,
                'step2_completed_at' => $step2Completed,
                'step3_completed_at' => $step3Completed,
                'deposit_amount' => $depositAmount,
                'reward_status' => $rewardStatus,
                'notes' => $step3Completed ? 'Reward claimed successfully' : null,
            ]);
        }
    }
}
