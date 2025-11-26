<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = defineProps<{
    class?: string;
    modelValue?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const classes = computed(() =>
    cn(
        'flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50',
        props.class
    )
);
</script>

<template>
    <textarea 
        :class="classes" 
        :value="modelValue"
        @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
    />
</template>
