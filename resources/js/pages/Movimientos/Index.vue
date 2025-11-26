<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { route } from 'ziggy-js';

const props = defineProps<{
    movimientos: Array<{
        id: number;
        tipo: string;
        cantidad: number;
        unidad: string;
        fecha: string;
        insumo_id: number;
        insumo_name: string;
    }>;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Movimientos', href: route('movimientos.index') },
];

const getTipoBadgeClass = (tipo: string) => {
    return tipo === 'entrada' 
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
};
</script>

<template>
    <Head title="Movimientos de Inventario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Movimientos de Inventario
                </h1>
                <Button as-child>
                    <Link :href="route('movimientos.create')">
                        <Plus class="mr-2 h-4 w-4" />
                        Registrar Movimiento
                    </Link>
                </Button>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Fecha
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Insumo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Tipo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Cantidad
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Unidad
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        <tr v-if="movimientos.length === 0">
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No hay movimientos registrados
                            </td>
                        </tr>
                        <tr v-for="movimiento in movimientos" :key="movimiento.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                {{ new Date(movimiento.fecha).toLocaleString('es-ES', { 
                                    year: 'numeric', 
                                    month: '2-digit', 
                                    day: '2-digit',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ movimiento.insumo_name }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <span :class="getTipoBadgeClass(movimiento.tipo)" class="inline-flex rounded-full px-2 text-xs font-semibold leading-5">
                                    {{ movimiento.tipo.charAt(0).toUpperCase() + movimiento.tipo.slice(1) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                {{ movimiento.cantidad }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                {{ movimiento.unidad }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
