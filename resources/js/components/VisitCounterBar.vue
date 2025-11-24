<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { AppPageProps, PageVisitPayload } from '@/types';

const props = withDefaults(
    defineProps<{
        class?: string;
    }>(),
    {
        class: '',
    },
);

const page = usePage<AppPageProps>();

const visitInfo = computed(() => page.props.pageVisit ?? null);
const visitTotal = computed(() => visitInfo.value?.visits ?? 0);
const pathLabel = computed(() => {
    if (visitInfo.value?.path) {
        return visitInfo.value.path;
    }

    if (typeof window !== 'undefined') {
        return window.location.pathname || '/';
    }

    return '/';
});

const lastVisited = computed(() => visitInfo.value?.last_visited_at ?? null);
</script>

<template>
    <section
        :class="[
            'visit-counter mt-8 flex flex-col items-center justify-center gap-1 rounded-xl border border-border/60 bg-accent/20 px-4 py-3 text-center text-xs text-muted-foreground shadow-sm backdrop-blur',
            props.class,
        ]"
    >
        <p class="text-sm font-semibold text-foreground">
            Visitas a esta página:
            <span class="text-primary">{{ visitInfo ? visitTotal.toLocaleString('es-BO') : '—' }}</span>
        </p>
        <p class="text-[11px] uppercase tracking-wide text-muted-foreground">
            Ruta: {{ pathLabel }}
        </p>
        <p v-if="lastVisited" class="text-[11px] text-muted-foreground">
            Última visita registrada: {{ new Date(lastVisited).toLocaleString('es-BO') }}
        </p>
    </section>
</template>
