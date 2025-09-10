<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, router } from '@inertiajs/vue3';
import { Plus, Edit, Trash2, Search, Shield, UserCheck, UserX, Eye, EyeOff } from 'lucide-vue-next';
import { reactive, ref, computed } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    role: {
        id: number;
        name: string;
        display_name: string;
    };
}

interface Role {
    id: number;
    name: string;
    display_name: string;
}

interface Props {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    roles: Role[];
    filters: {
        search?: string;
        role?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'System Users', href: '/system-users' },
];

const showCreateModal = ref(false);
const editingUser = ref<User | null>(null);
const filters = reactive({
    search: props.filters.search || '',
    role: props.filters.role || '',
});

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_id: '',
});

const resetForm = () => {
    form.name = '';
    form.email = '';
    form.password = '';
    form.password_confirmation = '';
    form.role_id = '';
    editingUser.value = null;
};

const openCreateModal = () => {
    resetForm();
    showCreateModal.value = true;
};

const openEditModal = (user: User) => {
    editingUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.password_confirmation = '';
    form.role_id = user.role.id.toString();
    showCreateModal.value = true;
};

const submitForm = () => {
    if (editingUser.value) {
        router.put(`/system-users/${editingUser.value.id}`, form, {
            onSuccess: () => {
                showCreateModal.value = false;
                resetForm();
            }
        });
    } else {
        router.post('/system-users', form, {
            onSuccess: () => {
                showCreateModal.value = false;
                resetForm();
            }
        });
    }
};

const deleteUser = (user: User) => {
    if (confirm(`Are you sure you want to delete ${user.name}?`)) {
        router.delete(`/system-users/${user.id}`);
    }
};

const toggleUserStatus = (user: User) => {
    router.patch(`/system-users/${user.id}/toggle-status`);
};

const applyFilters = () => {
    router.get('/system-users', filters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.search = '';
    filters.role = '';
    applyFilters();
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

const getRoleDisplayName = (roleName: string) => {
    const roleMap: Record<string, string> = {
        'superuser': 'Super Admin',
        'admin': 'Administrator'
    };
    return roleMap[roleName] || roleName;
};

const getRoleBadgeClass = (roleName: string) => {
    return roleName === 'superuser' 
        ? 'bg-red-100 text-red-800 border-red-200' 
        : 'bg-blue-100 text-blue-800 border-blue-200';
};

const totalUsers = computed(() => props.users.total);
const activeUsers = computed(() => props.users.data.filter(u => u.is_active).length);
const adminCount = computed(() => props.users.data.filter(u => u.role.name === 'admin').length);
const superuserCount = computed(() => props.users.data.filter(u => u.role.name === 'superuser').length);
</script>

<template>
    <Head title="System Users" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">System Users</h1>
                    <p class="text-muted-foreground">Manage administrators and system access</p>
                </div>
                <Button @click="openCreateModal" class="cursor-pointer">
                    <Plus class="h-4 w-4 mr-2" />
                    Add Admin
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total System Users</CardTitle>
                        <Shield class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">{{ totalUsers }}</div>
                        <p class="text-xs text-muted-foreground">Admin accounts</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Users</CardTitle>
                        <UserCheck class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ activeUsers }}</div>
                        <p class="text-xs text-muted-foreground">Currently active</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Administrators</CardTitle>
                        <UserCheck class="h-4 w-4 text-purple-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-purple-600">{{ adminCount }}</div>
                        <p class="text-xs text-muted-foreground">Admin role</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Super Admins</CardTitle>
                        <Shield class="h-4 w-4 text-red-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">{{ superuserCount }}</div>
                        <p class="text-xs text-muted-foreground">Superuser role</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Filters</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="space-y-2">
                            <Label for="search">Search</Label>
                            <div class="relative">
                                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input 
                                    id="search"
                                    v-model="filters.search"
                                    placeholder="Search users..."
                                    class="pl-8"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="role">Role</Label>
                            <select 
                                id="role"
                                v-model="filters.role"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">All Roles</option>
                                <option value="admin">Administrator</option>
                                <option value="superuser">Super Admin</option>
                            </select>
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
                    <CardTitle>System Users ({{ users.total.toLocaleString() }} total)</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-3 text-left">User</th>
                                    <th class="px-4 py-3 text-left">Role</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                    <th class="px-4 py-3 text-left">Created</th>
                                    <th class="px-4 py-3 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users.data" :key="user.id" class="border-b hover:bg-muted/50">
                                    <!-- User Info -->
                                    <td class="px-4 py-3">
                                        <div>
                                            <div class="font-semibold text-sm">{{ user.name }}</div>
                                            <div class="text-xs text-muted-foreground">{{ user.email }}</div>
                                        </div>
                                    </td>

                                    <!-- Role -->
                                    <td class="px-4 py-3">
                                        <span 
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
                                            :class="getRoleBadgeClass(user.role.name)"
                                        >
                                            <Shield class="h-3 w-3 mr-1" />
                                            {{ getRoleDisplayName(user.role.name) }}
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3">
                                        <button 
                                            @click="toggleUserStatus(user)"
                                            class="cursor-pointer inline-flex items-center gap-1 text-sm px-2 py-1 rounded transition-colors"
                                            :class="user.is_active 
                                                ? 'text-green-700 bg-green-100 hover:bg-green-200' 
                                                : 'text-red-700 bg-red-100 hover:bg-red-200'"
                                        >
                                            <component :is="user.is_active ? UserCheck : UserX" class="h-3 w-3" />
                                            {{ user.is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>

                                    <!-- Created -->
                                    <td class="px-4 py-3">
                                        <div class="text-sm text-muted-foreground">
                                            {{ formatDate(user.created_at) }}
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center space-x-1">
                                            <Button 
                                                variant="ghost" 
                                                size="sm" 
                                                @click="openEditModal(user)"
                                                class="cursor-pointer h-8 w-8 p-0"
                                                title="Edit User"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                            <Button 
                                                variant="ghost" 
                                                size="sm" 
                                                @click="deleteUser(user)"
                                                class="cursor-pointer h-8 w-8 p-0 text-red-600 hover:text-red-800 hover:bg-red-50"
                                                title="Delete User"
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
                    <div v-if="users.last_page > 1" class="mt-4 flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">
                            Showing {{ ((users.current_page - 1) * users.per_page) + 1 }} to 
                            {{ Math.min(users.current_page * users.per_page, users.total) }} of 
                            {{ users.total }} results
                        </p>
                        <div class="flex gap-2">
                            <Button 
                                v-if="users.current_page > 1"
                                variant="outline" 
                                @click="router.get('/system-users', { ...filters, page: users.current_page - 1 })"
                                class="cursor-pointer"
                            >
                                Previous
                            </Button>
                            <Button 
                                v-if="users.current_page < users.last_page"
                                variant="outline" 
                                @click="router.get('/system-users', { ...filters, page: users.current_page + 1 })"
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
                    <CardTitle>{{ editingUser ? 'Edit System User' : 'Create System User' }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Full Name</Label>
                            <Input 
                                id="name" 
                                v-model="form.name" 
                                placeholder="John Doe"
                                required 
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="email">Email Address</Label>
                            <Input 
                                id="email" 
                                v-model="form.email" 
                                type="email"
                                placeholder="john@example.com"
                                required 
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="role_id">Role</Label>
                            <select 
                                id="role_id" 
                                v-model="form.role_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            >
                                <option value="">Select a role</option>
                                <option v-for="role in roles" :key="role.id" :value="role.id">
                                    {{ getRoleDisplayName(role.name) }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="password">{{ editingUser ? 'New Password (leave blank to keep current)' : 'Password' }}</Label>
                            <Input 
                                id="password" 
                                v-model="form.password" 
                                type="password"
                                placeholder="••••••••"
                                :required="!editingUser"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="password_confirmation">Confirm Password</Label>
                            <Input 
                                id="password_confirmation" 
                                v-model="form.password_confirmation" 
                                type="password"
                                placeholder="••••••••"
                                :required="!editingUser && form.password"
                            />
                        </div>

                        <div class="flex gap-3 pt-4">
                            <Button type="submit" class="cursor-pointer flex-1">
                                {{ editingUser ? 'Update' : 'Create' }} User
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