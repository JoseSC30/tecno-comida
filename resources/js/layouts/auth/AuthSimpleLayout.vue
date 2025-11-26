<script setup lang="ts">
import VisitCounterBar from '@/components/VisitCounterBar.vue';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
    >
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex items-center gap-2 font-medium hover:opacity-80 transition-opacity"
                    >
                        <ArrowLeft class="h-5 w-5" />
                        <span class="text-sm">Volver al inicio</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium">{{ title }}</h1>
                        <p class="text-center text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
                <VisitCounterBar class="w-full" />
            </div>
        </div>
    </div>
</template>
