<script setup lang="ts">
import { useCart } from '@/composables/useCart';
import { Button } from '@/components/ui/button';
import { X, Minus, Plus, Trash2, ShoppingCart } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const { cartItems, isCartOpen, closeCart, incrementQuantity, decrementQuantity, removeFromCart, total, itemCount, clearCart } = useCart();

const checkout = () => {
    closeCart();
    router.visit(route('checkout'));
};
</script>

<template>
    <!-- Overlay -->
    <Transition
        enter-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-300"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isCartOpen"
            class="fixed inset-0 z-40 bg-black/50"
            @click="closeCart"
        />
    </Transition>

    <!-- Cart Sidebar -->
    <Transition
        enter-active-class="transition-transform duration-300"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition-transform duration-300"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div
            v-if="isCartOpen"
            class="fixed right-0 top-0 z-50 flex h-full w-full max-w-md flex-col bg-white shadow-xl dark:bg-gray-800"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <ShoppingCart class="h-5 w-5" />
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Carrito ({{ itemCount }})
                    </h2>
                </div>
                <Button variant="ghost" size="sm" @click="closeCart">
                    <X class="h-5 w-5" />
                </Button>
            </div>

            <!-- Cart Items -->
            <div class="flex flex-1 flex-col overflow-hidden">
                <div v-if="cartItems.length === 0" class="flex flex-1 items-center justify-center p-8">
                    <div class="text-center">
                        <ShoppingCart class="mx-auto h-16 w-16 text-gray-400" />
                        <p class="mt-4 text-gray-500 dark:text-gray-400">
                            Tu carrito está vacío
                        </p>
                    </div>
                </div>

                <div v-else class="flex-1 overflow-y-auto p-4">
                    <div class="space-y-4">
                        <div
                            v-for="item in cartItems"
                            :key="item.food_id"
                            class="flex gap-4 rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                        >
                            <!-- Image -->
                            <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded bg-gray-100">
                                <img
                                    v-if="item.image"
                                    :src="`/storage/${item.image}`"
                                    :alt="item.name"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    v-else
                                    class="h-full w-full bg-gradient-to-br from-[#ec1c24] to-[#ff9ea3]"
                                />
                            </div>

                            <!-- Info -->
                            <div class="flex flex-1 flex-col">
                                <h3 class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ item.name }}
                                </h3>
                                <p class="text-sm font-semibold text-orange-600" style="color: #ec1c24;">
                                    Bs. {{ item.price }}
                                </p>

                                <!-- Quantity Controls -->
                                <div class="mt-2 flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="h-8 w-8 p-0"
                                        @click="decrementQuantity(item.food_id)"
                                    >
                                        <Minus class="h-3 w-3" />
                                    </Button>
                                    <span class="w-8 text-center font-medium">{{ item.quantity }}</span>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="h-8 w-8 p-0"
                                        @click="incrementQuantity(item.food_id)"
                                    >
                                        <Plus class="h-3 w-3" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="ml-auto"
                                        @click="removeFromCart(item.food_id)"
                                    >
                                        <Trash2 class="h-4 w-4 text-red-600" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div v-if="cartItems.length > 0" class="border-t border-gray-200 p-4 dark:border-gray-700">
                    <div class="mb-4 flex items-center justify-between">
                        <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">Total:</span>
                        <span class="text-2xl font-bold text-orange-600">Bs. {{ total.toFixed(2) }}</span>
                    </div>
                    <div class="space-y-2">
                        <Button class="w-full" size="lg" @click="checkout">
                            Realizar Pedido
                        </Button>
                        <Button variant="outline" class="w-full" @click="clearCart">
                            Vaciar Carrito
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
