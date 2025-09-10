<?php

namespace Database\Seeders;

use App\Models\Funnel;
use App\Models\TrackingLink;
use App\Models\LinkClick;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TrackingLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, ensure we have some funnels to work with
        if (Funnel::count() === 0) {
            $this->call(FunnelSeeder::class);
        }

        $funnels = Funnel::all();
        
        if ($funnels->isEmpty()) {
            $this->command->info('No funnels found. Creating sample funnels first...');
            
            // Create sample funnels
            $sampleFunnels = [
                [
                    'name' => 'WEX Premium Signup',
                    'slug' => 'wex-premium',
                    'heading' => 'Get $25 Bonus on WEX!',
                    'sub_heading' => 'Sign up today and receive your instant bonus when you deposit $100',
                    'affiliate_url' => 'https://wex.app/ref/COINVO2024',
                    'affiliate_earnings_amount' => 25.00,
                    'platform' => 'WEX',
                    'is_active' => true,
                ],
                [
                    'name' => 'Coinbase Pro Launch',
                    'slug' => 'coinbase-pro',
                    'heading' => 'Start Trading with Coinbase Pro',
                    'sub_heading' => 'Professional trading tools for serious crypto traders',
                    'affiliate_url' => 'https://coinbase.com/join/coinvo_pro',
                    'affiliate_earnings_amount' => 15.00,
                    'platform' => 'Coinbase',
                    'is_active' => true,
                ],
                [
                    'name' => 'Binance VIP Access',
                    'slug' => 'binance-vip',
                    'heading' => 'Join Binance VIP Program',
                    'sub_heading' => 'Exclusive access to lower fees and premium features',
                    'affiliate_url' => 'https://binance.com/en/activity/referral/coinvo',
                    'affiliate_earnings_amount' => 20.00,
                    'platform' => 'Binance',
                    'is_active' => true,
                ],
            ];

            foreach ($sampleFunnels as $funnelData) {
                Funnel::create($funnelData);
            }
            
            $funnels = Funnel::all();
        }

        // Sample tracking link data with slugs
        $trackingLinkTemplates = [
            [
                'name' => 'Facebook Campaign - Holiday Special',
                'source' => 'facebook',
                'slug' => 'fb-holiday-2024',
                'click_count' => rand(150, 500),
                'unique_visitors' => rand(100, 300),
            ],
            [
                'name' => 'Instagram Stories Promotion',
                'source' => 'instagram',
                'slug' => 'ig-stories-promo',
                'click_count' => rand(80, 250),
                'unique_visitors' => rand(60, 180),
            ],
            [
                'name' => 'Email Newsletter - Weekly',
                'source' => 'email',
                'slug' => 'newsletter-weekly',
                'click_count' => rand(200, 600),
                'unique_visitors' => rand(150, 400),
            ],
            [
                'name' => 'Twitter Thread Campaign',
                'source' => 'twitter',
                'slug' => 'twitter-viral',
                'click_count' => rand(50, 180),
                'unique_visitors' => rand(40, 120),
            ],
            [
                'name' => 'YouTube Description Link',
                'source' => 'youtube',
                'slug' => 'youtube-tutorial',
                'click_count' => rand(300, 800),
                'unique_visitors' => rand(200, 500),
            ],
            [
                'name' => 'TikTok Bio Link',
                'source' => 'tiktok',
                'slug' => 'tiktok-viral',
                'click_count' => rand(100, 400),
                'unique_visitors' => rand(80, 300),
            ],
            [
                'name' => 'Reddit Community Post',
                'source' => 'reddit',
                'slug' => 'reddit-community',
                'click_count' => rand(70, 220),
                'unique_visitors' => rand(50, 150),
            ],
            [
                'name' => 'LinkedIn Article Share',
                'source' => 'linkedin',
                'slug' => 'linkedin-article',
                'click_count' => rand(30, 120),
                'unique_visitors' => rand(25, 90),
            ],
        ];

        // Create tracking links for each funnel
        foreach ($funnels as $funnel) {
            // Create 2-3 tracking links per funnel
            $numLinks = rand(2, 3);
            $selectedTemplates = collect($trackingLinkTemplates)->shuffle()->take($numLinks);
            
            foreach ($selectedTemplates as $index => $template) {
                $slug = $template['slug'] . '-' . strtolower($funnel->platform) . '-' . ($index + 1);
                $shortCode = rand(0, 1) ? TrackingLink::generateUniqueShortCode() : null;
                
                $trackingLink = TrackingLink::create([
                    'funnel_id' => $funnel->id,
                    'name' => $template['name'] . ' - ' . $funnel->platform,
                    'source' => $template['source'],
                    'slug' => $slug,
                    'short_code' => $shortCode,
                    'is_active' => rand(0, 10) > 1, // 90% chance of being active
                    'click_count' => $template['click_count'],
                    'unique_visitors' => $template['unique_visitors'],
                    'last_clicked_at' => Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
                    'expires_at' => rand(0, 1) ? Carbon::now()->addDays(rand(30, 365)) : null,
                ]);

                // Create some link clicks for realism
                $this->createLinkClicks($trackingLink);
            }
        }

        $this->command->info('Created ' . TrackingLink::count() . ' tracking links with demo data.');
    }

    /**
     * Create realistic link clicks for a tracking link
     */
    private function createLinkClicks(TrackingLink $trackingLink): void
    {
        $clicksToCreate = min($trackingLink->click_count, 50); // Limit to 50 for performance
        
        for ($i = 0; $i < $clicksToCreate; $i++) {
            LinkClick::create([
                'tracking_link_id' => $trackingLink->id,
                'funnel_id' => $trackingLink->funnel_id,
                'session_id' => 'sess_' . uniqid() . rand(1000, 9999),
                'ip_address' => $this->generateRandomIP(),
                'user_agent' => $this->getRandomUserAgent(),
                'referrer' => $this->getRandomReferrer($trackingLink->source),
                'clicked_at' => Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
            ]);
        }
    }

    /**
     * Generate a random IP address
     */
    private function generateRandomIP(): string
    {
        return rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255);
    }

    /**
     * Get random user agent strings
     */
    private function getRandomUserAgent(): string
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Android 13; Mobile; rv:109.0) Gecko/109.0 Firefox/120.0',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ];

        return $userAgents[array_rand($userAgents)];
    }

    /**
     * Get random referrer URLs based on source
     */
    private function getRandomReferrer(?string $source): ?string
    {
        $referrers = [
            'facebook' => 'https://www.facebook.com/',
            'instagram' => 'https://www.instagram.com/',
            'twitter' => 'https://twitter.com/',
            'youtube' => 'https://www.youtube.com/',
            'tiktok' => 'https://www.tiktok.com/',
            'reddit' => 'https://www.reddit.com/',
            'linkedin' => 'https://www.linkedin.com/',
            'email' => null, // Email usually doesn't have a referrer
        ];

        return $source ? ($referrers[$source] ?? 'https://www.google.com/') : 'https://www.google.com/';
    }
}