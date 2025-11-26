<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useCart } from '@/composables/useCart';
import CartSidebar from '@/components/CartSidebar.vue';
import { Button } from '@/components/ui/button';
import { ShoppingCart } from 'lucide-vue-next';
import { route } from 'ziggy-js';

const props = defineProps<{
    productos: Array<{
        id: number;
        name: string;
        description: string;
        price: number;
        image?: string;
        category: {
            id: number;
            name: string;
        };
    }>;
    categorias: Array<{
        id: number;
        name: string;
        foods_count: number;
    }>;
}>();

const { addToCart, openCart, itemCount } = useCart();
const selectedCategory = ref<number | null>(null);

const productosFiltrados = computed(() => {
    if (!selectedCategory.value) return props.productos;
    return props.productos.filter(producto => producto.category.id === selectedCategory.value);
});

const handleAddToCart = (producto: typeof props.productos[0]) => {
    addToCart({
        food_id: producto.id,
        name: producto.name,
        price: producto.price,
        image: producto.image,
    });
    openCart();
};

const filterByCategory = (categoryId: number | null) => {
    selectedCategory.value = selectedCategory.value === categoryId ? null : categoryId;
};

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Menú', href: route('menu') },
];
</script>

<template>
    <Head title="Menú" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header con botón de carrito -->
            <div class="mb-6 flex items-start justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                        Menú de Comidas
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Explora nuestro delicioso menú
                    </p>
                </div>
                <Button 
                    size="lg" 
                    class="relative"
                    @click="openCart"
                >
                    <ShoppingCart class="mr-2 h-5 w-5" />
                    Ver Carrito
                    <span 
                        v-if="itemCount > 0"
                        class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white"
                    >
                        {{ itemCount }}
                    </span>
                </Button>
            </div>

            <!-- Categorías -->
            <div class="mb-6 flex gap-2 overflow-x-auto pb-2">
                <button
                    @click="filterByCategory(null)"
                    :class="[
                        'whitespace-nowrap rounded-full px-4 py-2 text-sm font-medium shadow transition',
                        selectedCategory === null
                            ? 'text-white'
                            : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                    ]"
                    :style="selectedCategory === null ? 'background-color: #ec1c24' : ''"
                >
                    Todas
                </button>
                <button
                    v-for="categoria in props.categorias"
                    :key="categoria.id"
                    @click="filterByCategory(categoria.id)"
                    :class="[
                        'whitespace-nowrap rounded-full px-4 py-2 text-sm font-medium shadow transition',
                        selectedCategory === categoria.id
                            ? 'text-white'
                            : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                    ]"
                    :style="selectedCategory === categoria.id ? 'background-color: #ec1c24' : ''"
                >
                    {{ categoria.name }} ({{ categoria.foods_count }})
                </button>
            </div>

            <!-- Grid de comidas -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div
                    v-for="producto in productosFiltrados"
                    :key="producto.id"
                    class="overflow-hidden rounded-lg bg-white shadow transition hover:shadow-lg dark:bg-gray-800"
                >
                    <div class="aspect-video relative overflow-hidden">
                        <img 
                            v-if="producto.image" 
                            :src="`/storage/${producto.image}`" 
                            :alt="producto.name"
                            class="absolute inset-0 w-full h-full object-cover"
                        />
                        <div 
                            v-else 
                            class="absolute inset-0" 
                            style="background: linear-gradient(135deg, #ec1c24 0%, #f54d55 50%, #ff9ea3 100%);"
                        ></div>
                    </div>
                    <div class="p-4">
                        <div class="mb-2 flex items-start justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ producto.name }}
                            </h3>
                            <span class="text-lg font-bold text-green-600">
                                Bs. {{ producto.price }}
                            </span>
                        </div>
                        <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">
                            {{ producto.description }}
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ producto.category.name }}
                            </span>
                            <button
                                @click="handleAddToCart(producto)"
                                class="rounded px-4 py-2 text-sm font-medium text-white hover:opacity-90 transition"
                                style="background-color: #ec1c24;"
                            >
                                Agregar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mensaje si no hay resultados -->
            <div v-if="productosFiltrados.length === 0" class="py-12 text-center">
                <p class="text-gray-500 dark:text-gray-400">
                    No hay comidas disponibles en esta categoría
                </p>
            </div>
        </div>

        <!-- Cart Sidebar -->
        <CartSidebar />
    </AppLayout>
</template>
