<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, router } from '@inertiajs/vue3';
import { Plus, Edit, Trash2, ExternalLink, Copy, BarChart3, Eye, EyeOff, Check, Link2, Target, MousePointer, Users, Search } from 'lucide-vue-next';
import { reactive, ref, computed } from 'vue';

interface TrackingLink {
    id: number;
    funnel_id: number;
    name: string;
    source: string | null;
    slug: string;
    short_code: string | null;
    is_active: boolean;
    click_count: number;
    unique_visitors: number;
    last_clicked_at: string | null;
    expires_at: string | null;
    created_at: string;
    updated_at: string;
    funnel: {
        id: number;
        name: string;
        slug: string;
        platform: string;
        base_url: string | null;
    };
    full_url: string;
    short_url: string | null;
}

interface Funnel {
    id: number;
    name: string;
    slug: string;
    platform: string;
    is_active: boolean;
}

interface Props {
    trackingLinks: {
        data: TrackingLink[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    funnels: Funnel[];
    sources: string[];
    filters: {
        search?: string;
        funnel?: string;
        source?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Tracking Links', href: '/tracking-links' },
];

const showCreateModal = ref(false);
const editingLink = ref<TrackingLink | null>(null);
const selectedLinks = ref<number[]>([]);
const copiedStates = ref<Record<string, boolean>>({});

const form = reactive({
    funnel_id: '',
    name: '',
    source: '',
    slug: '',
    expires_at: '',
    is_active: true,
});

const filters = reactive({
    search: props.filters.search || '',
    funnel: props.filters.funnel || '',
    source: props.filters.source || '',
    status: props.filters.status || '',
});

const resetForm = () => {
    form.funnel_id = '';
    form.name = '';
    form.source = '';
    form.slug = '';
    form.expires_at = '';
    form.is_active = true;
    editingLink.value = null;
};

const openCreateModal = () => {
    resetForm();
    showCreateModal.value = true;
};

const openEditModal = (link: TrackingLink) => {
    editingLink.value = link;
    form.funnel_id = link.funnel_id.toString();
    form.name = link.name;
    form.source = link.source || '';
    form.slug = link.slug;
    form.expires_at = link.expires_at ? link.expires_at.split('T')[0] : '';
    form.is_active = link.is_active;
    showCreateModal.value = true;
};

const submitForm = () => {
    const data = {
        ...form,
        funnel_id: parseInt(form.funnel_id),
    };

    if (editingLink.value) {
        router.put(`/tracking-links/${editingLink.value.id}`, data, {
            onSuccess: () => {
                showCreateModal.value = false;
                resetForm();
            }
        });
    } else {
        router.post('/tracking-links', data, {
            onSuccess: () => {
                showCreateModal.value = false;
                resetForm();
            }
        });
    }
};

const deleteLink = (id: number) => {
    if (confirm('Are you sure you want to delete this tracking link?')) {
        router.delete(`/tracking-links/${id}`);
    }
};

const toggleLink = (link: TrackingLink) => {
    router.patch(`/tracking-links/${link.id}/toggle`);
};

const copyToClipboard = async (text: string, key: string) => {
    try {
        await navigator.clipboard.writeText(text);
        copiedStates.value[key] = true;
        setTimeout(() => {
            copiedStates.value[key] = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy: ', err);
    }
};

const viewAnalytics = (link: TrackingLink) => {
    console.log('View analytics for:', link);
};

const selectAll = computed({
    get: () => selectedLinks.value.length === props.trackingLinks.data.length,
    set: (value: boolean) => {
        selectedLinks.value = value ? props.trackingLinks.data.map(link => link.id) : [];
    }
});

const bulkDelete = () => {
    if (selectedLinks.value.length === 0) return;
    
    if (confirm(`Are you sure you want to delete ${selectedLinks.value.length} tracking links?`)) {
        router.delete('/tracking-links/bulk', {
            data: { ids: selectedLinks.value },
            onSuccess: () => {
                selectedLinks.value = [];
            }
        });
    }
};

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Never';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const totalClicks = computed(() => props.trackingLinks.data.reduce((sum, l) => sum + l.click_count, 0));
const totalUniqueVisitors = computed(() => props.trackingLinks.data.reduce((sum, l) => sum + l.unique_visitors, 0));
const activeLinks = computed(() => props.trackingLinks.data.filter(l => l.is_active).length);

const selectedFunnel = computed(() => {
    if (!form.funnel_id) return null;
    return props.funnels.find(f => f.id.toString() === form.funnel_id);
});

const generatedFullUrl = computed(() => {
    if (!selectedFunnel.value || !form.slug) return '';
    if (selectedFunnel.value.base_url) {
        return selectedFunnel.value.base_url.replace(/\/$/, '') + '/' + form.slug;
    }
    // Fallback to old system
    return `${window.location.origin}/${selectedFunnel.value.slug}/${form.slug}`;
});

const applyFilters = () => {
    router.get('/tracking-links', filters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.search = '';
    filters.funnel = '';
    filters.source = '';
    filters.status = '';
    applyFilters();
};
</script>

<template>
    <Head title="Tracking Links" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Header -->
            <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                <div>
                    <h1 class="text-2xl font-bold">Tracking Links</h1>
                    <p class="text-muted-foreground">Manage and track your affiliate marketing links</p>
                </div>
                <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:gap-3">
                    <Button 
                        v-if="selectedLinks.length > 0"
                        variant="destructive" 
                        @click="bulkDelete"
                        class="cursor-pointer w-full sm:w-auto"
                    >
                        <Trash2 class="h-4 w-4 mr-2" />
                        <span class="hidden sm:inline">Delete Selected ({{ selectedLinks.length }})</span>
                        <span class="sm:hidden">Delete ({{ selectedLinks.length }})</span>
                    </Button>
                    <Button @click="openCreateModal" class="cursor-pointer w-full sm:w-auto">
                        <Plus class="h-4 w-4 mr-2" />
                        <span class="hidden sm:inline">Create Link</span>
                        <span class="sm:hidden">Create</span>
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Links</CardTitle>
                        <Link2 class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">{{ trackingLinks.total }}</div>
                        <p class="text-xs text-muted-foreground">Across all funnels</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Links</CardTitle>
                        <Target class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ activeLinks }}</div>
                        <p class="text-xs text-muted-foreground">Currently tracking traffic</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Clicks</CardTitle>
                        <MousePointer class="h-4 w-4 text-purple-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-purple-600">{{ totalClicks.toLocaleString() }}</div>
                        <p class="text-xs text-muted-foreground">Total link interactions</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Unique Visitors</CardTitle>
                        <Users class="h-4 w-4 text-orange-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-orange-600">{{ totalUniqueVisitors.toLocaleString() }}</div>
                        <p class="text-xs text-muted-foreground">Individual users reached</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Filters</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-5">
                        <div class="space-y-2">
                            <Label for="search">Search</Label>
                            <div class="relative">
                                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input 
                                    id="search"
                                    v-model="filters.search"
                                    placeholder="Search links..."
                                    class="pl-8"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="funnel">Funnel</Label>
                            <select 
                                id="funnel"
                                v-model="filters.funnel"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">All Funnels</option>
                                <option v-for="funnel in funnels" :key="funnel.id" :value="funnel.id">
                                    {{ funnel.name }} ({{ funnel.platform }})
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="source">Source</Label>
                            <select 
                                id="source"
                                v-model="filters.source"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">All Sources</option>
                                <option v-for="source in sources" :key="source" :value="source">
                                    {{ source }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Status</Label>
                            <select 
                                id="status"
                                v-model="filters.status"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>

                        <div class="flex flex-col space-y-2 sm:flex-row sm:items-end sm:space-y-0 sm:space-x-2">
                            <Button @click="applyFilters" class="cursor-pointer w-full sm:w-auto">Apply</Button>
                            <Button variant="outline" @click="clearFilters" class="cursor-pointer w-full sm:w-auto">Clear</Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Data Table -->
            <Card class="flex-1">
                <CardHeader>
                    <CardTitle>Tracking Links ({{ trackingLinks.total.toLocaleString() }} total)</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-3 text-left">
                                        <input 
                                            type="checkbox" 
                                            v-model="selectAll"
                                            class="rounded border-input h-4 w-4"
                                        >
                                    </th>
                                    <th class="px-4 py-3 text-left">Link Details</th>
                                    <th class="px-4 py-3 text-left">Funnel</th>
                                    <th class="px-4 py-3 text-left">Performance</th>
                                    <th class="px-4 py-3 text-left">URLs</th>
                                    <th class="px-4 py-3 text-left">Slug</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                    <th class="px-4 py-3 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="link in trackingLinks.data" :key="link.id" class="border-b hover:bg-muted/50">
                                    <!-- Checkbox -->
                                    <td class="px-4 py-3">
                                        <input 
                                            type="checkbox" 
                                            :value="link.id"
                                            v-model="selectedLinks"
                                            class="rounded border-input h-4 w-4"
                                        >
                                    </td>

                                    <!-- Link Details -->
                                    <td class="px-4 py-3">
                                        <div>
                                            <div class="font-semibold text-sm">{{ link.name }}</div>
                                            <div v-if="link.source" class="text-xs text-muted-foreground">
                                                Source: {{ link.source }}
                                            </div>
                                            <div class="text-xs text-muted-foreground">
                                                Created: {{ formatDate(link.created_at) }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Funnel -->
                                    <td class="px-4 py-3">
                                        <div>
                                            <div class="font-medium text-sm">{{ link.funnel.name }}</div>
                                            <span class="inline-block bg-secondary px-2 py-1 text-xs rounded">
                                                {{ link.funnel.platform }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Performance -->
                                    <td class="px-4 py-3">
                                        <div class="space-y-1">
                                            <div class="flex items-center text-sm">
                                                <MousePointer class="h-3 w-3 mr-1 text-purple-600" />
                                                <span class="font-semibold text-purple-600">{{ link.click_count }}</span>
                                                <span class="text-muted-foreground ml-1">clicks</span>
                                            </div>
                                            <div class="flex items-center text-sm">
                                                <Users class="h-3 w-3 mr-1 text-orange-600" />
                                                <span class="font-semibold text-orange-600">{{ link.unique_visitors }}</span>
                                                <span class="text-muted-foreground ml-1">unique</span>
                                            </div>
                                            <div class="text-xs text-muted-foreground">
                                                Last: {{ formatDate(link.last_clicked_at) }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- URLs -->
                                    <td class="px-4 py-3">
                                        <div class="space-y-2 max-w-xs">
                                            <!-- Full URL -->
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-xs text-muted-foreground">Full URL</div>
                                                    <div class="font-mono text-xs truncate bg-muted px-2 py-1 rounded">
                                                        {{ link.full_url }}
                                                    </div>
                                                </div>
                                                <Button 
                                                    variant="ghost" 
                                                    size="sm" 
                                                    @click="copyToClipboard(link.full_url, `full-${link.id}`)"
                                                    class="cursor-pointer flex-shrink-0 h-6 w-6 p-0"
                                                    :class="copiedStates[`full-${link.id}`] ? 'text-green-600' : ''"
                                                >
                                                    <Check v-if="copiedStates[`full-${link.id}`]" class="h-3 w-3" />
                                                    <Copy v-else class="h-3 w-3" />
                                                </Button>
                                            </div>

                                            <!-- Short URL -->
                                            <div v-if="link.short_url" class="flex items-center gap-2">
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-xs text-blue-600">Short URL</div>
                                                    <div class="font-mono text-xs truncate bg-blue-50 px-2 py-1 rounded">
                                                        {{ link.short_url }}
                                                    </div>
                                                </div>
                                                <Button 
                                                    variant="ghost" 
                                                    size="sm" 
                                                    @click="copyToClipboard(link.short_url, `short-${link.id}`)"
                                                    class="cursor-pointer flex-shrink-0 h-6 w-6 p-0"
                                                    :class="copiedStates[`short-${link.id}`] ? 'text-green-600' : ''"
                                                >
                                                    <Check v-if="copiedStates[`short-${link.id}`]" class="h-3 w-3" />
                                                    <Copy v-else class="h-3 w-3" />
                                                </Button>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Slug -->
                                    <td class="px-4 py-3">
                                        <div class="font-mono text-sm bg-muted px-2 py-1 rounded">
                                            {{ link.slug }}
                                        </div>
                                        <div v-if="link.source" class="text-xs text-muted-foreground mt-1">
                                            Source: {{ link.source }}
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3">
                                        <button 
                                            @click="toggleLink(link)"
                                            class="cursor-pointer inline-flex items-center gap-1 text-sm px-2 py-1 rounded transition-colors"
                                            :class="link.is_active 
                                                ? 'text-green-700 bg-green-100 hover:bg-green-200' 
                                                : 'text-gray-700 bg-gray-100 hover:bg-gray-200'"
                                        >
                                            <component :is="link.is_active ? Eye : EyeOff" class="h-3 w-3" />
                                            {{ link.is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center space-x-1">
                                            <Button 
                                                variant="ghost" 
                                                size="sm" 
                                                @click="viewAnalytics(link)"
                                                class="cursor-pointer h-8 w-8 p-0"
                                                title="View Analytics"
                                            >
                                                <BarChart3 class="h-4 w-4" />
                                            </Button>
                                            <Button 
                                                variant="ghost" 
                                                size="sm" 
                                                @click="openEditModal(link)"
                                                class="cursor-pointer h-8 w-8 p-0"
                                                title="Edit Link"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                            <Button 
                                                variant="ghost" 
                                                size="sm" 
                                                @click="deleteLink(link.id)"
                                                class="cursor-pointer h-8 w-8 p-0 text-red-600 hover:text-red-800 hover:bg-red-50"
                                                title="Delete Link"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="trackingLinks.last_page > 1" class="mt-4 flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">
                            Showing {{ ((trackingLinks.current_page - 1) * trackingLinks.per_page) + 1 }} to 
                            {{ Math.min(trackingLinks.current_page * trackingLinks.per_page, trackingLinks.total) }} of 
                            {{ trackingLinks.total }} results
                        </p>
                        <div class="flex gap-2">
                            <Button 
                                v-if="trackingLinks.current_page > 1"
                                variant="outline" 
                                @click="router.get('/tracking-links', { page: trackingLinks.current_page - 1 })"
                                class="cursor-pointer"
                            >
                                Previous
                            </Button>
                            <Button 
                                v-if="trackingLinks.current_page < trackingLinks.last_page"
                                variant="outline" 
                                @click="router.get('/tracking-links', { page: trackingLinks.current_page + 1 })"
                                class="cursor-pointer"
                            >
                                Next
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <Card class="w-full max-w-md max-h-[90vh] overflow-y-auto">
                <CardHeader>
                    <CardTitle>{{ editingLink ? 'Edit Tracking Link' : 'Create Tracking Link' }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="funnel_id">Funnel</Label>
                            <select 
                                id="funnel_id" 
                                v-model="form.funnel_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            >
                                <option value="">Select a funnel</option>
                                <option v-for="funnel in funnels" :key="funnel.id" :value="funnel.id">
                                    {{ funnel.name }} ({{ funnel.platform }})
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="name">Link Name</Label>
                            <Input 
                                id="name" 
                                v-model="form.name" 
                                placeholder="e.g., Facebook Campaign 1"
                                required 
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="source">Source</Label>
                            <Input 
                                id="source" 
                                v-model="form.source" 
                                placeholder="e.g., facebook, instagram, email"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="slug">Link Slug</Label>
                            <Input 
                                id="slug" 
                                v-model="form.slug" 
                                placeholder="e.g., black-friday-2024"
                                required 
                            />
                        </div>

                        <!-- Generated Full URL Display -->
                        <div v-if="generatedFullUrl" class="space-y-2">
                            <Label>Generated Full URL</Label>
                            <div class="flex items-center gap-2 p-3 bg-muted/50 rounded-md border">
                                <div class="flex-1 font-mono text-sm text-muted-foreground truncate">
                                    {{ generatedFullUrl }}
                                </div>
                                <Button 
                                    type="button"
                                    variant="ghost" 
                                    size="sm" 
                                    @click="copyToClipboard(generatedFullUrl, 'preview')"
                                    class="cursor-pointer flex-shrink-0"
                                    :class="copiedStates['preview'] ? 'text-green-600' : ''"
                                >
                                    <Check v-if="copiedStates['preview']" class="h-4 w-4" />
                                    <Copy v-else class="h-4 w-4" />
                                </Button>
                            </div>
                            <p class="text-xs text-muted-foreground">This URL will redirect to: {{ selectedFunnel?.affiliate_url }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="expires_at">Expires At (Optional)</Label>
                            <Input 
                                id="expires_at" 
                                v-model="form.expires_at" 
                                type="date"
                            />
                        </div>

                        <div class="flex items-center space-x-2">
                            <input 
                                id="is_active" 
                                v-model="form.is_active" 
                                type="checkbox"
                                class="rounded border-input h-4 w-4"
                            />
                            <Label for="is_active">Active</Label>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <Button type="submit" class="cursor-pointer flex-1">
                                {{ editingLink ? 'Update' : 'Create' }}
                            </Button>
                            <Button 
                                type="button" 
                                variant="outline" 
                                @click="showCreateModal = false"
                                class="cursor-pointer"
                            >
                                Cancel
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>