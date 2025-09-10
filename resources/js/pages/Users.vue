<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, Link, router } from '@inertiajs/vue3';
import { Users, CheckCircle, XCircle, Activity, Filter, Download } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';

interface AffiliateUser {
    id: number;
    uid: string;
    platform: string;
    register_time: string;
    kyc_status: boolean;
    invite_code: string;
    first_deposit_time: string | null;
    first_trade_time: string | null;
    last_deposit_time: string | null;
    last_trade_time: string | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    users: {
        data: AffiliateUser[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    platforms: string[];
    stats: {
        total_users: number;
        our_users: number;
        kyc_verified: number;
        active_traders: number;
    };
    filters: {
        platform?: string;
        kyc_status?: boolean;
        invite_code?: string;
        search?: string;
        our_users_only?: boolean;
    };
    our_invite_code: string;
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Users', href: '/users' },
];

const filters = reactive({
    platform: props.filters.platform || '',
    kyc_status: props.filters.kyc_status === undefined ? '' : props.filters.kyc_status.toString(),
    search: props.filters.search || '',
    our_users_only: props.filters.our_users_only !== false,
});

const showFilters = ref(false);

const applyFilters = () => {
    const params = new URLSearchParams();
    
    if (filters.platform) params.append('platform', filters.platform);
    if (filters.kyc_status !== '') params.append('kyc_status', filters.kyc_status);
    if (filters.search) params.append('search', filters.search);
    if (!filters.our_users_only) params.append('our_users_only', 'false');

    router.get(`/users?${params.toString()}`);
};

const clearFilters = () => {
    filters.platform = '';
    filters.kyc_status = '';
    filters.search = '';
    filters.our_users_only = true;
    applyFilters();
};

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Never';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatDateTime = (dateString: string | null) => {
    if (!dateString) return 'Never';
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Users</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_users.toLocaleString() }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Our Users</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.our_users.toLocaleString() }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ ((stats.our_users / stats.total_users) * 100).toFixed(1) }}% of total
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">KYC Verified</CardTitle>
                        <CheckCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.kyc_verified.toLocaleString() }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ ((stats.kyc_verified / stats.total_users) * 100).toFixed(1) }}% verified
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Traders</CardTitle>
                        <Activity class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.active_traders.toLocaleString() }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ ((stats.active_traders / stats.total_users) * 100).toFixed(1) }}% active
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader class="pb-3">
                    <div class="flex items-center justify-between">
                        <CardTitle class="text-lg">User Filters</CardTitle>
                        <div class="flex gap-2">
                            <Button 
                                variant="outline" 
                                size="sm"
                                @click="showFilters = !showFilters"
                            >
                                <Filter class="mr-2 h-4 w-4" />
                                {{ showFilters ? 'Hide' : 'Show' }} Filters
                            </Button>
                            <Button variant="outline" size="sm">
                                <Download class="mr-2 h-4 w-4" />
                                Export CSV
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent v-show="showFilters">
                    <div class="grid gap-4 md:grid-cols-5">
                        <div class="space-y-2">
                            <Label for="search">Search UID</Label>
                            <Input 
                                id="search"
                                v-model="filters.search"
                                placeholder="USER_000001"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="platform">Platform</Label>
                            <select 
                                id="platform"
                                v-model="filters.platform"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">All Platforms</option>
                                <option v-for="platform in platforms" :key="platform" :value="platform">
                                    {{ platform }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="kyc_status">KYC Status</Label>
                            <select 
                                id="kyc_status"
                                v-model="filters.kyc_status"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">All Users</option>
                                <option value="true">KYC Verified</option>
                                <option value="false">Not Verified</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label class="flex items-center space-x-2">
                                <input 
                                    type="checkbox" 
                                    v-model="filters.our_users_only"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                >
                                <span>Our users only</span>
                            </Label>
                            <p class="text-xs text-muted-foreground">
                                Code: {{ our_invite_code }}
                            </p>
                        </div>

                        <div class="flex items-end space-x-2">
                            <Button @click="applyFilters">Apply</Button>
                            <Button variant="outline" @click="clearFilters">Clear</Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Users Table -->
            <Card class="flex-1">
                <CardHeader>
                    <CardTitle>Users ({{ users.total.toLocaleString() }} total)</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-2 text-left">UID</th>
                                    <th class="px-4 py-2 text-left">Platform</th>
                                    <th class="px-4 py-2 text-left">Registered</th>
                                    <th class="px-4 py-2 text-left">KYC Status</th>
                                    <th class="px-4 py-2 text-left">Invite Code</th>
                                    <th class="px-4 py-2 text-left">First Deposit</th>
                                    <th class="px-4 py-2 text-left">Last Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users.data" :key="user.id" class="border-b hover:bg-muted/50">
                                    <td class="px-4 py-2 font-mono text-sm">{{ user.uid }}</td>
                                    <td class="px-4 py-2">
                                        <span class="rounded bg-secondary px-2 py-1 text-xs">{{ user.platform }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-sm">{{ formatDate(user.register_time) }}</td>
                                    <td class="px-4 py-2">
                                        <div class="flex items-center">
                                            <CheckCircle v-if="user.kyc_status" class="mr-2 h-4 w-4 text-green-600" />
                                            <XCircle v-else class="mr-2 h-4 w-4 text-red-600" />
                                            <span class="text-sm">{{ user.kyc_status ? 'Verified' : 'Pending' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span 
                                            :class="user.invite_code === our_invite_code ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                            class="rounded px-2 py-1 text-xs font-mono"
                                        >
                                            {{ user.invite_code }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-sm">{{ formatDate(user.first_deposit_time) }}</td>
                                    <td class="px-4 py-2 text-sm">{{ formatDateTime(user.last_trade_time) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4 flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">
                            Showing {{ ((users.current_page - 1) * users.per_page) + 1 }} to 
                            {{ Math.min(users.current_page * users.per_page, users.total) }} of 
                            {{ users.total }} results
                        </p>
                        <div class="flex space-x-2">
                            <Link 
                                v-for="link in users.links" 
                                :key="link.label"
                                :href="link.url || ''"
                                :class="[
                                    'px-3 py-2 text-sm border rounded',
                                    link.active ? 'bg-primary text-primary-foreground' : 'bg-background hover:bg-muted',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>