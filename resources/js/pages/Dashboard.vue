<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Users, CheckCircle, Activity, TrendingUp, ExternalLink } from 'lucide-vue-next';
import { computed } from 'vue';

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
        step1_signups: number;
        step2_deposits: number;
        step3_rewards: number;
        step1_to_step2_rate: number;
        step2_to_step3_rate: number;
        overall_conversion_rate: number;
        total_earnings: number;
        monthly_earnings: number;
    };
    platform_stats: Record<string, number>;
    recent_users: AffiliateUser[];
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
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">

            <!-- Funnel Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Step 1: Signups</CardTitle>
                        <Users class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">{{ funnel_stats.step1_signups.toLocaleString() }}</div>
                        <p class="text-xs text-muted-foreground">Users signed up via our link</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Step 2: Deposits</CardTitle>
                        <CheckCircle class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ funnel_stats.step2_deposits.toLocaleString() }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ funnel_stats.step1_to_step2_rate }}% conversion from signups
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Step 3: Rewards</CardTitle>
                        <TrendingUp class="h-4 w-4 text-purple-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-purple-600">{{ funnel_stats.step3_rewards.toLocaleString() }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ funnel_stats.step2_to_step3_rate }}% conversion from deposits
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Overall Rate</CardTitle>
                        <Activity class="h-4 w-4 text-orange-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-orange-600">{{ funnel_stats.overall_conversion_rate }}%</div>
                        <p class="text-xs text-muted-foreground">End-to-end conversion</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Earnings Stats -->
            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Earnings</CardTitle>
                        <TrendingUp class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">${{ funnel_stats.total_earnings.toLocaleString() }}</div>
                        <p class="text-xs text-muted-foreground">Total affiliate earnings</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">This Month</CardTitle>
                        <TrendingUp class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">${{ funnel_stats.monthly_earnings.toLocaleString() }}</div>
                        <p class="text-xs text-muted-foreground">Earnings this month</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Platform Distribution -->
                <Card>
                    <CardHeader>
                        <CardTitle>Platform Distribution</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div 
                                v-for="(count, platform) in platform_stats" 
                                :key="platform"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center space-x-2">
                                    <div class="h-3 w-3 rounded bg-blue-500"></div>
                                    <span class="text-sm font-medium">{{ platform }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-muted-foreground">{{ count }} users</span>
                                    <span class="text-xs text-muted-foreground">
                                        ({{ funnel_stats.step1_signups > 0 ? ((count / funnel_stats.step1_signups) * 100).toFixed(1) : 0 }}%)
                                    </span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Users -->
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
                        <!-- Manage Affiliates - visible to admin and superuser -->
                        <Link 
                            v-if="user?.role?.name === 'admin' || user?.role?.name === 'superuser'"
                            href="/affiliates" 
                            class="flex items-center justify-center p-4 rounded-lg border border-dashed border-gray-300 hover:border-gray-400 hover:bg-gray-50 transition-colors"
                        >
                            <div class="text-center">
                                <Users class="mx-auto h-6 w-6 text-gray-400 mb-2" />
                                <p class="text-sm font-medium">Manage Affiliates</p>
                            </div>
                        </Link>

                        <!-- Manage Users - visible to superuser only -->
                        <Link 
                            v-if="user?.role?.name === 'superuser'"
                            href="/users" 
                            class="flex items-center justify-center p-4 rounded-lg border border-dashed border-gray-300 hover:border-gray-400 hover:bg-gray-50 transition-colors"
                        >
                            <div class="text-center">
                                <Users class="mx-auto h-6 w-6 text-gray-400 mb-2" />
                                <p class="text-sm font-medium">Manage Users</p>
                            </div>
                        </Link>
                        
                        <div class="flex items-center justify-center p-4 rounded-lg border border-dashed border-gray-300 opacity-50">
                            <div class="text-center">
                                <Activity class="mx-auto h-6 w-6 text-gray-400 mb-2" />
                                <p class="text-sm font-medium">Analytics (Soon)</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-center p-4 rounded-lg border border-dashed border-gray-300 opacity-50">
                            <div class="text-center">
                                <TrendingUp class="mx-auto h-6 w-6 text-gray-400 mb-2" />
                                <p class="text-sm font-medium">Campaigns (Soon)</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-center p-4 rounded-lg border border-dashed border-gray-300 opacity-50">
                            <div class="text-center">
                                <CheckCircle class="mx-auto h-6 w-6 text-gray-400 mb-2" />
                                <p class="text-sm font-medium">Funnels (Soon)</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
