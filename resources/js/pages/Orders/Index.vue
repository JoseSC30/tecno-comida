<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { Eye } from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    pedidos: Array<{
        id: number;
        total: number;
        status: string;
        created_at: string;
        user?: {
            name: string;
        };
        items: Array<{
            food: {
                name: string;
            };
            quantity: number;
        }>;
    }>;
}>();

const pedidos = computed(() => props.pedidos);

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Pedidos', href: route('orders.index') },
];

const getStatusColor = (status: string) => {
    const colors = {
        'pendiente': 'bg-yellow-100 text-yellow-800',
        'preparando': 'bg-blue-100 text-blue-800',
        'listo': 'bg-green-100 text-green-800',
        'entregado': 'bg-gray-100 text-gray-800',
        'cancelado': 'bg-red-100 text-red-800',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Mis Pedidos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Pedidos
                </h1>
            </div>

            <div class="space-y-4">
                <div
                    v-for="pedido in pedidos"
                    :key="pedido.id"
                    class="rounded-lg bg-white p-6 shadow dark:bg-gray-800"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    Pedido #{{ pedido.id }}
                                </h3>
                                <span
                                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="getStatusColor(pedido.status)"
                                >
                                    {{ pedido.status }}
                                </span>
                            </div>
                            <p v-if="pedido.user" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Cliente: {{ pedido.user.name }}
                            </p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ new Date(pedido.created_at).toLocaleString() }}
                            </p>
                            <div class="mt-3">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ pedido.items.length }} item(s)
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                Bs. {{ pedido.total }}
                            </div>
                            <Button variant="ghost" size="sm" as-child class="mt-2">
                                <Link :href="route('orders.show', pedido.id)">
                                    <Eye class="mr-2 h-4 w-4" />
                                    Ver detalle
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>

                <div v-if="pedidos.length === 0" class="rounded-lg bg-white p-12 text-center shadow dark:bg-gray-800">
                    <p class="text-gray-600 dark:text-gray-400">
                        No tienes pedidos aún
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
