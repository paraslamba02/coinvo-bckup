<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import { Calendar, ChevronDown } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

interface Props {
    startDate?: string;
    endDate?: string;
}

const props = withDefaults(defineProps<Props>(), {
    startDate: '',
    endDate: ''
});

const emit = defineEmits<{
    'update:startDate': [value: string];
    'update:endDate': [value: string];
    'apply': [startDate: string, endDate: string];
}>();

const localStartDate = ref(props.startDate);
const localEndDate = ref(props.endDate);
const showDropdown = ref(false);

// Preset date ranges
const presets = [
    {
        label: 'Today',
        getValue: () => {
            const today = new Date().toISOString().split('T')[0];
            return { start: today, end: today };
        }
    },
    {
        label: 'Last 7 days',
        getValue: () => {
            const end = new Date().toISOString().split('T')[0];
            const start = new Date(Date.now() - 6 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
            return { start, end };
        }
    },
    {
        label: 'Last 30 days',
        getValue: () => {
            const end = new Date().toISOString().split('T')[0];
            const start = new Date(Date.now() - 29 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
            return { start, end };
        }
    },
    {
        label: 'Last 90 days',
        getValue: () => {
            const end = new Date().toISOString().split('T')[0];
            const start = new Date(Date.now() - 89 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
            return { start, end };
        }
    },
    {
        label: 'This month',
        getValue: () => {
            const now = new Date();
            const start = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
            const end = new Date().toISOString().split('T')[0];
            return { start, end };
        }
    }
];

const selectedPreset = computed(() => {
    const current = { start: localStartDate.value, end: localEndDate.value };
    return presets.find(preset => {
        const presetValue = preset.getValue();
        return presetValue.start === current.start && presetValue.end === current.end;
    })?.label || 'Custom';
});

const applyPreset = (preset: typeof presets[0]) => {
    const { start, end } = preset.getValue();
    localStartDate.value = start;
    localEndDate.value = end;
    applyDateRange();
    showDropdown.value = false;
};

const applyDateRange = () => {
    if (localStartDate.value && localEndDate.value) {
        emit('update:startDate', localStartDate.value);
        emit('update:endDate', localEndDate.value);
        emit('apply', localStartDate.value, localEndDate.value);

        // Update the URL with new date range
        router.get(route('dashboard'), {
            start_date: localStartDate.value,
            end_date: localEndDate.value
        }, {
            preserveState: true,
            preserveScroll: true
        });
    }
};

const formatDisplayDate = (dateStr: string) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

const displayRange = computed(() => {
    if (!localStartDate.value || !localEndDate.value) return 'Select date range';
    if (localStartDate.value === localEndDate.value) {
        return formatDisplayDate(localStartDate.value);
    }
    return `${formatDisplayDate(localStartDate.value)} - ${formatDisplayDate(localEndDate.value)}`;
});

// Initialize with default if no dates provided
if (!props.startDate || !props.endDate) {
    const defaultRange = presets[2].getValue(); // Last 30 days
    localStartDate.value = defaultRange.start;
    localEndDate.value = defaultRange.end;
}
</script>

<template>
    <div class="relative">
        <!-- Date Range Button -->
        <Button
            variant="outline"
            @click="showDropdown = !showDropdown"
            class="w-full sm:w-auto justify-between min-w-[280px]"
        >
            <div class="flex items-center">
                <Calendar class="h-4 w-4 mr-2" />
                <span>{{ displayRange }}</span>
            </div>
            <ChevronDown class="h-4 w-4 ml-2" />
        </Button>

        <!-- Dropdown -->
        <Card v-if="showDropdown" class="absolute top-full left-0 z-50 mt-1 w-full sm:w-96 shadow-lg">
            <CardContent class="p-4">
                <!-- Presets -->
                <div class="space-y-2 mb-4">
                    <Label class="text-sm font-medium">Quick Select</Label>
                    <div class="grid grid-cols-2 gap-2">
                        <Button
                            v-for="preset in presets"
                            :key="preset.label"
                            variant="ghost"
                            size="sm"
                            @click="applyPreset(preset)"
                            :class="selectedPreset === preset.label ? 'bg-primary text-primary-foreground' : ''"
                            class="justify-start"
                        >
                            {{ preset.label }}
                        </Button>
                    </div>
                </div>

                <!-- Custom Date Range -->
                <div class="space-y-3">
                    <Label class="text-sm font-medium">Custom Range</Label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <Label for="start-date" class="text-xs">Start Date</Label>
                            <Input
                                id="start-date"
                                type="date"
                                v-model="localStartDate"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <Label for="end-date" class="text-xs">End Date</Label>
                            <Input
                                id="end-date"
                                type="date"
                                v-model="localEndDate"
                                class="mt-1"
                            />
                        </div>
                    </div>
                </div>

                <!-- Apply/Cancel Buttons -->
                <div class="flex justify-end space-x-2 mt-4">
                    <Button variant="ghost" size="sm" @click="showDropdown = false">
                        Cancel
                    </Button>
                    <Button size="sm" @click="applyDateRange" :disabled="!localStartDate || !localEndDate">
                        Apply
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- Overlay to close dropdown -->
        <div
            v-if="showDropdown"
            class="fixed inset-0 z-40"
            @click="showDropdown = false"
        ></div>
    </div>
</template>