<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    pedido: {
        id: number;
        total: number;
        status: string;
        notes: string;
        created_at: string;
        user: {
            name: string;
            email: string;
        };
        items: Array<{
            id: number;
            quantity: number;
            unit_price: number;
            subtotal: number;
            food: {
                name: string;
                description: string;
            };
        }>;
    };
}>();

const pedido = computed(() => props.pedido);

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pedidos', href: '/orders' },
    { title: `Pedido #${props.pedido.id}`, href: `/orders/${props.pedido.id}` },
];

const updateStatus = (status: string) => {
    router.put(`/admin/orders/${props.pedido.id}/status`, { status });
};
</script>

<template>
    <Head :title="`Pedido #${pedido.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6">
                <Button variant="ghost" as-child class="mb-4">
                    <Link href="/orders">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Volver
                    </Link>
                </Button>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Detalle del Pedido #{{ pedido.id }}
                </h1>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Información del pedido -->
                <div class="lg:col-span-2">
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Items del Pedido
                        </h2>
                        <div class="space-y-4">
                            <div
                                v-for="item in pedido.items"
                                :key="item.id"
                                class="flex justify-between border-b border-gray-200 pb-4 last:border-0 dark:border-gray-700"
                            >
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ item.food.name }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ item.food.description }}
                                    </p>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Cantidad: {{ item.quantity }} × Bs. {{ item.unit_price }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">
                                        Bs. {{ item.subtotal }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 border-t border-gray-200 pt-4 dark:border-gray-700">
                            <div class="flex justify-between text-xl font-bold">
                                <span class="text-gray-900 dark:text-gray-100">Total:</span>
                                <span class="text-gray-900 dark:text-gray-100">Bs. {{ pedido.total }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="space-y-6">
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Información
                        </h2>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Cliente</p>
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ pedido.user.name }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ pedido.user.email }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Fecha</p>
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ new Date(pedido.created_at).toLocaleString() }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Estado</p>
                                <p class="font-medium capitalize text-gray-900 dark:text-gray-100">
                                    {{ pedido.status }}
                                </p>
                            </div>
                            <div v-if="pedido.notes">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Notas</p>
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ pedido.notes }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
