<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Head, Link, router } from '@inertiajs/vue3';
import { Trash2, Plus, Pencil } from 'lucide-vue-next';
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
    { title: 'Categorías', href: route('categorias.index') },
];

const deleteCategory = (categoria: any) => {
    if (confirm('¿Estás seguro de eliminar esta categoría?')) {
        router.delete(route('categorias.destroy', categoria.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Gestionar Categorías" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Gestionar Categorías
                </h1>
                <Button as-child>
                    <Link :href="route('categorias.create')">
                        <Plus class="mr-2 h-4 w-4" />
                        Nueva Categoría
                    </Link>
                </Button>
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
                                {{ categoria.foods_count }} producto(s)
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <Button variant="ghost" size="sm" as-child>
                                <Link :href="route('categorias.edit', categoria.id)">
                                    <Pencil class="h-4 w-4 text-blue-600" />
                                </Link>
                            </Button>
                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-md p-2 text-sm font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-700"
                                @click="deleteCategory(categoria)"
                            >
                                <Trash2 class="h-4 w-4 text-red-600" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
