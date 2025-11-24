<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { useCart } from '@/composables/useCart';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ShoppingCart, CreditCard, MapPin } from 'lucide-vue-next';
import { computed } from 'vue';

const { cartItems, total, clearCart } = useCart();

const form = useForm({
    delivery_address: '',
    notes: '',
    items: [] as Array<{ food_id: number; quantity: number; price: number }>
});

const submit = () => {
    form.items = cartItems.value.map(item => ({
        food_id: item.food_id,
        quantity: item.quantity,
        price: item.price
    }));
    
    form.post('/orders', {
        onSuccess: () => {
            clearCart();
        },
    });
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Menú', href: '/menu' },
    { title: 'Checkout', href: '/checkout' },
];
</script>

<template>
    <Head title="Checkout" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mx-auto max-w-4xl">
                <h1 class="mb-6 text-3xl font-bold text-gray-900 dark:text-gray-100">
                    Finalizar Pedido
                </h1>

                <!-- Alerta si el carrito está vacío -->
                <div v-if="cartItems.length === 0" class="rounded-lg border border-orange-200 bg-orange-50 p-6 dark:border-orange-900 dark:bg-orange-950">
                    <div class="flex items-center gap-3">
                        <ShoppingCart class="h-6 w-6 text-orange-600" />
                        <div>
                            <h3 class="font-semibold text-orange-900 dark:text-orange-100">
                                Tu carrito está vacío
                            </h3>
                            <p class="mt-1 text-sm text-orange-700 dark:text-orange-300">
                                Agrega productos desde el menú para realizar un pedido.
                            </p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <Link href="/menu">
                            <Button>
                                Ver Menú
                            </Button>
                        </Link>
                    </div>
                </div>

                <!-- Formulario de checkout -->
                <form v-else @submit.prevent="submit" class="grid gap-6 lg:grid-cols-3">
                    <!-- Columna izquierda: Formulario -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Dirección de entrega -->
                        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                            <div class="mb-4 flex items-center gap-2">
                                <MapPin class="h-5 w-5 text-orange-600" />
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    Dirección de Entrega
                                </h2>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <Label for="delivery_address">Dirección Completa *</Label>
                                    <Textarea
                                        id="delivery_address"
                                        v-model="form.delivery_address"
                                        placeholder="Ej: Calle Principal #123, Zona Norte"
                                        class="mt-1"
                                    />
                                    <p v-if="form.errors.delivery_address" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.delivery_address }}
                                    </p>
                                </div>
                                <div>
                                    <Label for="notes">Notas Adicionales (Opcional)</Label>
                                    <Textarea
                                        id="notes"
                                        v-model="form.notes"
                                        placeholder="Instrucciones especiales, referencias, etc."
                                        rows="2"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha: Resumen del pedido -->
                    <div class="lg:col-span-1">
                        <div class="sticky top-6 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                            <div class="mb-4 flex items-center gap-2">
                                <ShoppingCart class="h-5 w-5 text-orange-600" />
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    Resumen del Pedido
                                </h2>
                            </div>

                            <!-- Items -->
                            <div class="mb-4 space-y-3">
                                <div
                                    v-for="item in cartItems"
                                    :key="item.food_id"
                                    class="flex justify-between text-sm"
                                >
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ item.name }}
                                        </p>
                                        <p class="text-gray-500 dark:text-gray-400">
                                            {{ item.quantity }} x Bs. {{ item.price }}
                                        </p>
                                    </div>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">
                                        Bs. {{ (item.quantity * item.price).toFixed(2) }}
                                    </p>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                                <div class="mb-2 flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">
                                        Bs. {{ total.toFixed(2) }}
                                    </span>
                                </div>
                                <div class="mb-4 flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Entrega:</span>
                                    <span class="font-medium text-green-600">Gratis</span>
                                </div>
                                <div class="flex justify-between border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">Total:</span>
                                    <span class="text-2xl font-bold text-orange-600">
                                        Bs. {{ total.toFixed(2) }}
                                    </span>
                                </div>
                            </div>

                            <Button
                                type="submit"
                                class="mt-6 w-full"
                                size="lg"
                                :disabled="form.processing"
                            >
                                <CreditCard class="mr-2 h-5 w-5" />
                                {{ form.processing ? 'Procesando...' : 'Confirmar Pedido' }}
                            </Button>

                            <p class="mt-4 text-center text-xs text-gray-500 dark:text-gray-400">
                                Al confirmar aceptas nuestros términos y condiciones
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
