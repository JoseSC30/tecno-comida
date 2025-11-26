<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2, Plus } from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    insumos: Array<{
        id: number;
        name: string;
        unidad: string;
        stock: number;
    }>;
}>();

const insumos = computed(() => props.insumos);

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Insumos', href: route('insumos.index') },
];

const deleteInsumo = (insumo: any) => {
    if (confirm('¿Estás seguro de eliminar este insumo?')) {
        router.delete(route('insumos.destroy', insumo.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Gestionar Insumos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Gestionar Insumos
                </h1>
                <Button as-child>
                    <Link :href="route('insumos.create')">
                        <Plus class="mr-2 h-4 w-4" />
                        Nuevo Insumo
                    </Link>
                </Button>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Nombre
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Unidad
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Stock
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        <tr v-for="insumo in insumos" :key="insumo.id">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ insumo.name }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                {{ insumo.unidad }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                {{ insumo.stock }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <Button variant="ghost" size="sm" as-child class="mr-2">
                                    <Link :href="route('insumos.edit', insumo.id)">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                </Button>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-md p-2 text-sm font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-700"
                                    @click="deleteInsumo(insumo)"
                                >
                                    <Trash2 class="h-4 w-4 text-red-600" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
