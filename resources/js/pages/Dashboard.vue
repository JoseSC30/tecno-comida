<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const estadisticas = computed(() => page.props.estadisticas ?? {});
const pedidosRecientes = computed(() => page.props.pedidos_recientes ?? []);
const productosPopulares = computed(() => page.props.productos_populares ?? []);
const productosDestacados = computed(() => page.props.productos_destacados ?? []);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
            <!-- Bienvenida -->
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Bienvenido, {{ user?.name }}
                </h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">
                    Rol: {{ user?.role?.name }}
                </p>
            </div>

            <!-- Estadísticas -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(value, key) in estadisticas"
                    :key="key"
                    class="rounded-lg bg-white p-6 shadow dark:bg-gray-800"
                >
                    <div class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        {{ key.replace(/_/g, ' ').toUpperCase() }}
                    </div>
                    <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                        {{ typeof value === 'number' && (key.includes('ingresos') || key.includes('gasto')) ? `Bs. ${value.toFixed(2)}` : value }}
                    </div>
                </div>
            </div>

            <!-- Admin: Pedidos recientes y comidas populares -->
            <div v-if="user?.is_admin || user?.is_vendedor" class="grid gap-6 lg:grid-cols-2">
                <!-- Pedidos recientes -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Pedidos Recientes
                    </h2>
                    <div class="space-y-3">
                        <div
                            v-for="order in pedidosRecientes"
                            :key="order.id"
                            class="flex items-center justify-between border-b border-gray-200 pb-3 last:border-0 dark:border-gray-700"
                        >
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                    Pedido #{{ order.id }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ order.user?.name }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                    Bs. {{ order.total }}
                                </div>
                                <div class="text-sm" :class="{
                                    'text-yellow-600': order.status === 'pendiente',
                                    'text-blue-600': order.status === 'preparando',
                                    'text-green-600': order.status === 'listo',
                                    'text-gray-600': order.status === 'entregado',
                                }">
                                    {{ order.status }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comidas populares -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Comidas Populares
                    </h2>
                    <div class="space-y-3">
                        <div
                            v-for="food in productosPopulares"
                            :key="food.id"
                            class="flex items-center justify-between border-b border-gray-200 pb-3 last:border-0 dark:border-gray-700"
                        >
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ food.name }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    Bs. {{ food.price }}
                                </div>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ food.order_items_count || 0 }} pedidos
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cliente: Mis pedidos y comidas destacadas -->
            <div v-if="user?.is_cliente" class="grid gap-6 lg:grid-cols-2">
                <!-- Mis pedidos recientes -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Mis Pedidos Recientes
                    </h2>
                    <div v-if="pedidosRecientes.length > 0" class="space-y-3">
                        <div
                            v-for="order in pedidosRecientes"
                            :key="order.id"
                            class="flex items-center justify-between border-b border-gray-200 pb-3 last:border-0 dark:border-gray-700"
                        >
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                    Pedido #{{ order.id }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ new Date(order.created_at).toLocaleDateString() }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                    Bs. {{ order.total }}
                                </div>
                                <div class="text-sm" :class="{
                                    'text-yellow-600': order.status === 'pendiente',
                                    'text-blue-600': order.status === 'preparando',
                                    'text-green-600': order.status === 'listo',
                                    'text-gray-600': order.status === 'entregado',
                                }">
                                    {{ order.status }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-600 dark:text-gray-400">
                        No has realizado pedidos aún
                    </p>
                </div>

                <!-- Comidas destacadas -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Comidas Destacadas
                    </h2>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div
                            v-for="food in productosDestacados"
                            :key="food.id"
                            class="rounded-lg border border-gray-200 p-3 dark:border-gray-700"
                        >
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                {{ food.name }}
                            </div>
                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Bs. {{ food.price }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
