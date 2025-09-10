<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <!-- Coinvo Logo -->
    <img 
        src="/assets/Coinvo-02.png" 
        alt="Coinvo" 
        :class="className" 
        v-bind="$attrs"
        style="height: 20px; width: auto; background-color: #1f2937; padding: 2px 4px; border-radius: 4px;"
    />
</template>