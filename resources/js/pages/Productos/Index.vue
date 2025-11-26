<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2, Plus, ChefHat } from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    productos: Array<{
        id: number;
        name: string;
        description: string;
        price: number;
        cost: number;
        available: boolean;
        category: {
            id: number;
            name: string;
        };
    }>;
}>();

const productos = computed(() => props.productos);

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Productos', href: route('productos.index') },
];

const deleteProduct = (producto: any) => {
    if (confirm('¿Estás seguro de eliminar este producto?')) {
        router.delete(route('productos.destroy', producto.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Gestionar Productos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Gestionar Productos
                </h1>
                <Button as-child>
                    <Link :href="route('productos.create')">
                        <Plus class="mr-2 h-4 w-4" />
                        Nuevo Producto
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
                                Categoría
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Costo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Precio Venta
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        <tr v-for="producto in productos" :key="producto.id">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ producto.name }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ producto.description?.substring(0, 50) }}...
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                {{ producto.category.name }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                Bs. {{ producto.cost }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                Bs. {{ producto.price }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span
                                    class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                    :class="producto.available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                >
                                    {{ producto.available ? 'Disponible' : 'No disponible' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <Button variant="ghost" size="sm" as-child class="mr-2">
                                    <Link :href="route('recetas.edit', producto.id)">
                                        <ChefHat class="h-4 w-4" />
                                    </Link>
                                </Button>
                                <Button variant="ghost" size="sm" as-child class="mr-2">
                                    <Link :href="route('productos.edit', producto.id)">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                </Button>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-md p-2 text-sm font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-700"
                                    @click="deleteProduct(producto)"
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
