<script setup lang="ts">
import { useTheme } from '@/composables/useTheme';
import { computed } from 'vue';

const { effectiveMode } = useTheme();

const logoSrc = computed(() => {
    return effectiveMode.value === 'dark' ? '/images/Logo2.png' : '/images/Logo1.png';
});
</script>

<template>
    <div class="flex aspect-square size-13 items-center justify-center rounded-md overflow-hidden">
        <img :src="logoSrc" alt="Logo" class="size-full object-cover" />
    </div>
    <div class="ml-2 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold text-lg">Menú</span>
    </div>
</template>
