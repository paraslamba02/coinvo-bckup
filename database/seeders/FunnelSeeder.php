<?php

namespace Database\Seeders;

use App\Models\Funnel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FunnelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $funnels = [
            [
                'name' => 'WEX Exchange Funnel',
                'slug' => 'wex-exchange',
                'heading' => 'Get $20 Bonus on WEX!',
                'sub_heading' => 'Sign up using our link and deposit $100 to get your bonus',
                'affiliate_url' => 'https://wex.app/ref/COINVO2024',
                'base_url' => 'https://track.coinvo.com',
                'affiliate_earnings_amount' => 20.00,
                'platform' => 'WEX',
                'is_active' => true,
            ],
            [
                'name' => 'Binance Affiliate Program',
                'slug' => 'binance-program',
                'heading' => 'Earn with Binance - $25 Bonus!',
                'sub_heading' => 'Join the world\'s largest crypto exchange and get rewarded',
                'affiliate_url' => 'https://accounts.binance.com/register?ref=COINVO2024',
                'base_url' => 'https://track.coinvo.com',
                'affiliate_earnings_amount' => 25.00,
                'platform' => 'Binance',
                'is_active' => true,
            ],
            [
                'name' => 'Coinbase Pro Campaign',
                'slug' => 'coinbase-pro',
                'heading' => 'Trade Pro, Get $15 Back',
                'sub_heading' => 'Professional trading tools with instant rewards',
                'affiliate_url' => 'https://coinbase.com/join/coinvo_2024',
                'base_url' => 'https://links.coinvo.com',
                'affiliate_earnings_amount' => 15.00,
                'platform' => 'Coinbase',
                'is_active' => true,
            ],
            [
                'name' => 'Kraken Exchange Bonus',
                'slug' => 'kraken-bonus',
                'heading' => '$30 Welcome Bonus on Kraken',
                'sub_heading' => 'Secure trading with regulatory compliance',
                'affiliate_url' => 'https://kraken.com/sign-up?ref=coinvo2024',
                'base_url' => 'https://go.coinvo.com',
                'affiliate_earnings_amount' => 30.00,
                'platform' => 'Kraken',
                'is_active' => true,
            ],
            [
                'name' => 'Bybit Trading Campaign',
                'slug' => 'bybit-campaign',
                'heading' => 'Trade Derivatives - $40 Bonus!',
                'sub_heading' => 'Advanced futures trading with high leverage',
                'affiliate_url' => 'https://bybit.com/register?affiliate_id=COINVO2024',
                'base_url' => 'https://promo.coinvo.com',
                'affiliate_earnings_amount' => 40.00,
                'platform' => 'Bybit',
                'is_active' => true,
            ],
        ];

        foreach ($funnels as $funnelData) {
            Funnel::create($funnelData);
        }
    }
}
