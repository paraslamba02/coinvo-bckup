<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Edit, Trash2, ExternalLink, Globe, Link2, Copy } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

interface TrackingLink {
    id: number;
    slug: string;
    full_url: string;
    is_active: boolean;
}

interface Funnel {
    id: number;
    name: string;
    heading: string;
    sub_heading: string | null;
    image_url: string | null;
    affiliate_url: string;
    base_url: string | null;
    affiliate_earnings_amount: number;
    platform: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    tracking_links: TrackingLink[];
}

interface Props {
    funnels: Funnel[];
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Funnels', href: '/funnels' },
];

const showCreateModal = ref(false);
const editingFunnel = ref<Funnel | null>(null);

const form = reactive({
    name: '',
    heading: '',
    sub_heading: '',
    image_url: '',
    affiliate_url: '',
    base_url: '',
    affiliate_earnings_amount: '',
    platform: '',
    is_active: true,
});

const resetForm = () => {
    form.name = '';
    form.heading = '';
    form.sub_heading = '';
    form.image_url = '';
    form.affiliate_url = '';
    form.base_url = '';
    form.affiliate_earnings_amount = '';
    form.platform = '';
    form.is_active = true;
    editingFunnel.value = null;
};

const openCreateModal = () => {
    resetForm();
    showCreateModal.value = true;
};

const editFunnel = (funnel: Funnel) => {
    editingFunnel.value = funnel;
    form.name = funnel.name;
    form.heading = funnel.heading;
    form.sub_heading = funnel.sub_heading || '';
    form.image_url = funnel.image_url || '';
    form.affiliate_url = funnel.affiliate_url;
    form.base_url = funnel.base_url || '';
    form.affiliate_earnings_amount = funnel.affiliate_earnings_amount.toString();
    form.platform = funnel.platform;
    form.is_active = funnel.is_active;
    showCreateModal.value = true;
};

const submitForm = () => {
    const data = {
        ...form,
        affiliate_earnings_amount: parseFloat(form.affiliate_earnings_amount),
    };

    if (editingFunnel.value) {
        router.put(`/funnels/${editingFunnel.value.id}`, data, {
            onSuccess: () => {
                showCreateModal.value = false;
                resetForm();
            },
        });
    } else {
        router.post('/funnels', data, {
            onSuccess: () => {
                showCreateModal.value = false;
                resetForm();
            },
        });
    }
};

const deleteFunnel = (funnel: Funnel) => {
    if (confirm(`Are you sure you want to delete "${funnel.name}"?`)) {
        router.delete(`/funnels/${funnel.id}`);
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const copyToClipboard = async (text: string) => {
    try {
        await navigator.clipboard.writeText(text);
        // You could add a toast notification here if you have one
    } catch (err) {
        console.error('Failed to copy text: ', err);
    }
};
</script>

<template>
    <Head title="Funnels" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Funnel Management</h1>
                    <p class="text-muted-foreground">Manage your affiliate marketing funnels and track earnings</p>
                </div>
                <div class="flex space-x-3">
                    <Link href="/tracking-links" class="cursor-pointer">
                        <Button variant="outline" class="cursor-pointer">
                            <Link2 class="mr-2 h-4 w-4" />
                            Manage Links
                        </Button>
                    </Link>
                    <Button @click="openCreateModal" class="cursor-pointer">
                        <Plus class="mr-2 h-4 w-4" />
                        Create Funnel
                    </Button>
                </div>
            </div>

            <!-- Funnels Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="funnel in funnels" :key="funnel.id" class="relative">
                    <CardHeader class="pb-3">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <CardTitle class="text-lg">{{ funnel.name }}</CardTitle>
                                <p class="text-sm text-muted-foreground mt-1">{{ funnel.platform }}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <input 
                                    type="checkbox" 
                                    v-model="funnel.is_active"
                                    class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                                />
                                <span :class="funnel.is_active ? 'text-green-600' : 'text-gray-400'" class="text-xs">
                                    {{ funnel.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div>
                            <h3 class="font-medium">{{ funnel.heading }}</h3>
                            <p v-if="funnel.sub_heading" class="text-sm text-muted-foreground">{{ funnel.sub_heading }}</p>
                        </div>

                        <div class="flex items-center space-x-2">
                            <span class="font-medium text-green-600">${{ funnel.affiliate_earnings_amount }}</span>
                            <span class="text-xs text-muted-foreground">per deposit</span>
                        </div>

                        <!-- Tracking Links Section -->
                        <div class="border-t pt-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Recent Links</span>
                                <span class="text-xs text-muted-foreground">
                                    {{ funnel.tracking_links?.length || 0 }} active
                                </span>
                            </div>
                            
                            <!-- Show tracking links or empty state -->
                            <div v-if="funnel.tracking_links?.length > 0" class="space-y-2">
                                <div 
                                    v-for="link in funnel.tracking_links.slice(0, 3)" 
                                    :key="link.id"
                                    class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded border border-dashed border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition-colors group"
                                >
                                    <div class="flex-1 min-w-0">
                                        <div class="font-mono text-xs text-gray-700 dark:text-gray-300 font-medium">{{ link.slug }}</div>
                                        <div class="font-mono text-xs text-muted-foreground truncate">{{ link.full_url }}</div>
                                    </div>
                                    <button 
                                        @click="copyToClipboard(link.full_url)"
                                        class="opacity-0 group-hover:opacity-100 p-1 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-all cursor-pointer"
                                        title="Copy link"
                                    >
                                        <Copy class="h-3 w-3" />
                                    </button>
                                </div>
                            </div>
                            <div v-else class="text-center py-4">
                                <p class="text-xs text-muted-foreground">No tracking links yet</p>
                                <Link :href="`/tracking-links?funnel=${funnel.id}`" class="cursor-pointer">
                                    <Button size="sm" variant="outline" class="mt-2 cursor-pointer">
                                        <Plus class="h-3 w-3 mr-1" />
                                        Create First Link
                                    </Button>
                                </Link>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t">
                            <div class="flex space-x-2">
                                <Button size="sm" variant="outline" @click="editFunnel(funnel)" class="cursor-pointer hover:bg-blue-50">
                                    <Edit class="h-4 w-4" />
                                </Button>
                                <Button size="sm" variant="outline" @click="deleteFunnel(funnel)" class="cursor-pointer hover:bg-red-50 hover:text-red-600">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                                <a :href="funnel.affiliate_url" target="_blank" rel="noopener noreferrer" class="cursor-pointer">
                                    <Button size="sm" variant="outline" class="hover:bg-green-50">
                                        <ExternalLink class="h-4 w-4" />
                                    </Button>
                                </a>
                            </div>
                            <span class="text-xs text-muted-foreground">
                                {{ formatDate(funnel.created_at) }}
                            </span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Create/Edit Modal -->
            <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <Card class="w-full max-w-lg mx-4">
                    <CardHeader>
                        <CardTitle>{{ editingFunnel ? 'Edit Funnel' : 'Create New Funnel' }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Funnel Name*</Label>
                                <Input 
                                    id="name"
                                    v-model="form.name"
                                    placeholder="WEX Affiliate Funnel"
                                    required
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="platform">Platform*</Label>
                                <Input 
                                    id="platform"
                                    v-model="form.platform"
                                    placeholder="WEX"
                                    required
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="heading">Heading*</Label>
                            <Input 
                                id="heading"
                                v-model="form.heading"
                                placeholder="Get $20 Bonus on WEX!"
                                required
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="sub_heading">Sub Heading</Label>
                            <textarea 
                                id="sub_heading"
                                v-model="form.sub_heading"
                                placeholder="Sign up and deposit $100 to get your bonus"
                                rows="2"
                                class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="affiliate_url">Affiliate URL*</Label>
                            <Input 
                                id="affiliate_url"
                                v-model="form.affiliate_url"
                                placeholder="https://wex.app/ref/COINVO2024"
                                required
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="base_url">Base URL for Tracking Links</Label>
                            <Input 
                                id="base_url"
                                v-model="form.base_url"
                                placeholder="https://track.coinvo.com"
                            />
                            <p class="text-xs text-muted-foreground">URLs will be: base_url/slug (e.g., https://track.coinvo.com/promo2024)</p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="affiliate_earnings_amount">Affiliate Earnings ($)*</Label>
                                <Input 
                                    id="affiliate_earnings_amount"
                                    v-model="form.affiliate_earnings_amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="20.00"
                                    required
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="image_url">Image URL</Label>
                                <Input 
                                    id="image_url"
                                    v-model="form.image_url"
                                    placeholder="https://example.com/image.png"
                                />
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t">
                            <div class="flex space-x-2">
                                <Button @click="submitForm" class="cursor-pointer">
                                    {{ editingFunnel ? 'Update' : 'Create' }} Funnel
                                </Button>
                                <Button variant="outline" @click="showCreateModal = false" class="cursor-pointer">
                                    Cancel
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>