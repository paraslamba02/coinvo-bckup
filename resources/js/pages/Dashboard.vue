<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Users, CheckCircle, Activity, TrendingUp, ExternalLink, Link as LinkIcon, Settings, Shield, Monitor, Smartphone, Target, Eye } from 'lucide-vue-next';
import { computed } from 'vue';
import DonutChart from '@/components/charts/DonutChart.vue';
import DropoffFunnelChart from '@/components/charts/DropoffFunnelChart.vue';
import SourceBreakdownChart from '@/components/charts/SourceBreakdownChart.vue';
import DeviceBreakdownChart from '@/components/charts/DeviceBreakdownChart.vue';
import SparklineChart from '@/components/charts/SparklineChart.vue';
import DateRangePicker from '@/components/DateRangePicker.vue';

interface AffiliateUser {
    id: number;
    uid: string;
    platform: string;
    register_time: string;
    funnel_step: string;
    reward_status: string;
    invite_code: string;
}

interface Props {
    funnel_stats: {
        landing_visitors: number;
        total_landing_clicks: number;
        step1_signups: number;
        step2_deposits: number;
        step3_rewards: number;
        total_conversions: number;
        landing_to_step1_rate: number;
        step1_to_step2_rate: number;
        step2_to_step3_rate: number;
        overall_conversion_rate: number;
        dropoff_rates: {
            landing_to_step1: number;
            step1_to_step2: number;
            step2_to_step3: number;
        };
    };
    source_stats: Record<string, { visitors: number; clicks: number }>;
    device_stats: Record<string, number>;
    platform_stats: Record<string, number>;
    recent_users: AffiliateUser[];
    date_range: {
        start_date: string;
        end_date: string;
    };
}

const props = defineProps<Props>();

const page = usePage();
const user = computed(() => page.props.auth.user);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

// Mock 7-day trend data for sparklines (in real app, this would come from backend)
const generateTrendData = (baseValue: number, variance: number = 0.2) => {
    const trends = [];
    let current = baseValue * (1 - variance);
    for (let i = 0; i < 7; i++) {
        const change = (Math.random() - 0.5) * variance * baseValue;
        current = Math.max(0, current + change);
        trends.push(Math.round(current));
    }
    return trends;
};

const sparklineData = computed(() => ({
    landing: generateTrendData(props.funnel_stats.landing_visitors / 30),
    signups: generateTrendData(props.funnel_stats.step1_signups / 30),
    deposits: generateTrendData(props.funnel_stats.step2_deposits / 30),
    conversions: generateTrendData(props.funnel_stats.total_conversions / 30)
}));
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">

            <!-- Date Range Picker -->
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h1 class="text-2xl font-bold">Dashboard</h1>
                    <p class="text-muted-foreground">Track your funnel performance and conversions</p>
                </div>
                <DateRangePicker
                    :start-date="date_range.start_date"
                    :end-date="date_range.end_date"
                />
            </div>

            <!-- Enhanced Funnel Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Landing Visitors</CardTitle>
                        <Eye class="h-4 w-4 text-indigo-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold text-indigo-600">{{ funnel_stats.landing_visitors.toLocaleString() }}</div>
                                <p class="text-xs text-muted-foreground">Unique visitors from tracking links</p>
                            </div>
                            <div class="w-16 h-8">
                                <SparklineChart :data="sparklineData.landing" color="#6366F1" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Sign Ups</CardTitle>
                        <Users class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold text-blue-600">{{ funnel_stats.step1_signups.toLocaleString() }}</div>
                                <p class="text-xs text-muted-foreground">
                                    {{ funnel_stats.landing_to_step1_rate }}% conversion from landing
                                </p>
                            </div>
                            <div class="w-16 h-8">
                                <SparklineChart :data="sparklineData.signups" color="#3B82F6" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Deposits</CardTitle>
                        <CheckCircle class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold text-green-600">{{ funnel_stats.step2_deposits.toLocaleString() }}</div>
                                <p class="text-xs text-muted-foreground">
                                    {{ funnel_stats.step1_to_step2_rate }}% conversion from signups
                                </p>
                            </div>
                            <div class="w-16 h-8">
                                <SparklineChart :data="sparklineData.deposits" color="#22C55E" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Conversions</CardTitle>
                        <Target class="h-4 w-4 text-purple-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold text-purple-600">{{ funnel_stats.total_conversions.toLocaleString() }}</div>
                                <p class="text-xs text-muted-foreground">
                                    {{ funnel_stats.overall_conversion_rate }}% overall conversion
                                </p>
                            </div>
                            <div class="w-16 h-8">
                                <SparklineChart :data="sparklineData.conversions" color="#9333EA" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>


            <!-- Enhanced Conversion Funnel with Drop-offs -->
            <Card>
                <CardHeader>
                    <CardTitle>Conversion Funnel & Drop-off Analysis</CardTitle>
                </CardHeader>
                <CardContent>
                    <DropoffFunnelChart :funnel-stats="funnel_stats" :height="180" />
                </CardContent>
            </Card>

            <!-- Analytics Grid -->
            <div class="grid gap-4 md:grid-cols-3">
                <!-- Source Breakdown -->
                <Card>
                    <CardHeader>
                        <CardTitle>Traffic Sources</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <SourceBreakdownChart :data="source_stats" :height="280" />
                    </CardContent>
                </Card>

                <!-- Device Breakdown -->
                <Card>
                    <CardHeader>
                        <CardTitle>Device Types</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <DeviceBreakdownChart :data="device_stats" :height="280" />
                    </CardContent>
                </Card>

                <!-- Platform Distribution -->
                <Card>
                    <CardHeader>
                        <CardTitle>Platform Signups</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="Object.keys(platform_stats).length > 0">
                            <!-- Stats Summary -->
                            <div class="text-center mb-4">
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ Object.values(platform_stats).reduce((a, b) => a + b, 0).toLocaleString() }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Total Signups</div>
                            </div>

                            <DonutChart
                                :data="platform_stats"
                                :colors="['#3B82F6', '#60A5FA', '#93C5FD', '#DBEAFE', '#1E40AF', '#1D4ED8', '#2563EB', '#1956D3', '#1E3A8A', '#1E293B']"
                                :height="240"
                            />
                        </div>

                        <!-- No Data State -->
                        <div v-else class="flex items-center justify-center h-64">
                            <div class="text-center text-gray-500 dark:text-gray-400">
                                <div class="text-sm">No signup data available</div>
                                <div class="text-xs mt-1">User signups by platform will appear here</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Users -->
            <div class="grid gap-4 md:grid-cols-1">

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle>Recent Users</CardTitle>
                        <Link href="/affiliates" class="flex items-center text-sm text-blue-600 hover:text-blue-700">
                            View all <ExternalLink class="ml-1 h-3 w-3" />
                        </Link>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div
                                v-for="user in recent_users"
                                :key="user.id"
                                class="flex items-center justify-between p-2 rounded border"
                            >
                                <div>
                                    <p class="font-mono text-sm font-medium">{{ user.uid }}</p>
                                    <p class="text-xs text-muted-foreground">{{ user.platform }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="flex items-center">
                                        <span
                                            :class="{
                                                'bg-purple-100 text-purple-800': user.reward_status === 'claimed',
                                                'bg-yellow-100 text-yellow-800': user.reward_status === 'pending',
                                                'bg-gray-100 text-gray-800': user.reward_status === 'not_eligible'
                                            }"
                                            class="rounded px-1 py-0.5 text-xs"
                                        >
                                            {{ user.reward_status === 'claimed' ? 'Rewarded' :
                                               user.reward_status === 'pending' ? 'Pending' : 'Step 1' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-muted-foreground">{{ formatDate(user.register_time) }}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-2 md:grid-cols-4">
                        <!-- Manage Users - visible to admin and superuser -->
                        <Link 
                            v-if="user?.role?.name === 'admin' || user?.role?.name === 'superuser'"
                            href="/affiliates" 
                            class="flex items-center justify-center p-4 rounded-lg border border-dashed border-gray-300 hover:border-blue-400 hover:bg-blue-50 transition-all duration-200 cursor-pointer group"
                        >
                            <div class="text-center">
                                <Users class="mx-auto h-6 w-6 text-gray-600 group-hover:text-blue-600 transition-colors mb-2" />
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-blue-700 dark:group-hover:text-blue-400">Manage Users</p>
                            </div>
                        </Link>

                        <!-- Manage Funnels - visible to admin and superuser -->
                        <Link 
                            v-if="user?.role?.name === 'admin' || user?.role?.name === 'superuser'"
                            href="/funnels" 
                            class="flex items-center justify-center p-4 rounded-lg border border-dashed border-gray-300 hover:border-green-400 hover:bg-green-50 transition-all duration-200 cursor-pointer group"
                        >
                            <div class="text-center">
                                <TrendingUp class="mx-auto h-6 w-6 text-gray-600 group-hover:text-green-600 transition-colors mb-2" />
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-green-700 dark:group-hover:text-green-400">Manage Funnels</p>
                            </div>
                        </Link>

                        <!-- Tracking Links - visible to admin and superuser -->
                        <Link 
                            v-if="user?.role?.name === 'admin' || user?.role?.name === 'superuser'"
                            href="/tracking-links" 
                            class="flex items-center justify-center p-4 rounded-lg border border-dashed border-gray-300 hover:border-purple-400 hover:bg-purple-50 transition-all duration-200 cursor-pointer group"
                        >
                            <div class="text-center">
                                <LinkIcon class="mx-auto h-6 w-6 text-gray-600 group-hover:text-purple-600 transition-colors mb-2" />
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-purple-700 dark:group-hover:text-purple-400">Tracking Links</p>
                            </div>
                        </Link>
                        
                        <!-- Manage System Users - visible to superuser only -->
                        <Link 
                            v-if="user?.role?.name === 'superuser'"
                            href="/system-users" 
                            class="flex items-center justify-center p-4 rounded-lg border border-dashed border-gray-300 hover:border-red-400 hover:bg-red-50 transition-all duration-200 cursor-pointer group"
                        >
                            <div class="text-center">
                                <Shield class="mx-auto h-6 w-6 text-gray-600 group-hover:text-red-600 transition-colors mb-2" />
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-red-700 dark:group-hover:text-red-400">System Users</p>
                            </div>
                        </Link>

                        <!-- Analytics placeholder for non-superuser -->
                        <div 
                            v-else
                            class="flex items-center justify-center p-4 rounded-lg border border-dashed border-gray-300 opacity-50"
                        >
                            <div class="text-center">
                                <Activity class="mx-auto h-6 w-6 text-gray-400 mb-2" />
                                <p class="text-sm font-medium text-gray-600">Analytics (Soon)</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
