<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Head, router } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    categorias: Array<{
        id: number;
        name: string;
        description: string;
        foods_count: number;
    }>;
}>();

const categorias = computed(() => props.categorias);

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Categorías', href: route('categories.index') },
];
</script>

<template>
    <Head title="Gestionar Categorías" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Gestionar Categorías
                </h1>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="categoria in categorias"
                    :key="categoria.id"
                    class="rounded-lg bg-white p-6 shadow dark:bg-gray-800"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ categoria.name }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ categoria.description }}
                            </p>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-500">
                                {{ categoria.foods_count }} comida(s)
                            </p>
                        </div>
                        <Button
                            variant="ghost"
                            size="sm"
                            as="button"
                            @click="() => {
                                if (window.confirm('¿Estás seguro de eliminar esta categoría?')) {
                                    router.delete(route('categories.destroy', categoria.id));
                                }
                            }"
                        >
                            <Trash2 class="h-4 w-4 text-red-600" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
