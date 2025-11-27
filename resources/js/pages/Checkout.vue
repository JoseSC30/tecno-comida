<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { useCart } from '@/composables/useCart';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { ShoppingCart, CreditCard, User, Banknote, QrCode, CheckCircle, Clock, AlertCircle, Loader2, RefreshCw } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { ref, computed, onUnmounted } from 'vue';
import axios from 'axios';

interface Cliente {
    id: number;
    name: string;
}

const props = defineProps<{
    clientes: Cliente[];
}>();

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);
const isCliente = computed(() => currentUser.value?.is_cliente);

const { cartItems, total, clearCart } = useCart();

const activeTab = ref<'efectivo' | 'qr'>('efectivo');

// Estado para pago QR
const qrClienteId = ref<number | null>(null);
const qrData = ref<{
    qr_base64: string | null;
    transaction_id: string | null;
    payment_number: string | null;
    expiration_date: string | null;
} | null>(null);
const qrLoading = ref(false);
const qrError = ref<string | null>(null);
const paymentStatus = ref<string | null>(null);
const callbackReceived = ref(false);
const callbackData = ref<{
    status: string;
    fecha: string;
    hora: string;
    metodo_pago: string;
} | null>(null);
const checkingStatus = ref(false);

// Polling automático
let pollingInterval: ReturnType<typeof setInterval> | null = null;
const pollingActive = ref(false);

const form = useForm({
    cliente_id: null as number | null,
    items: [] as Array<{ food_id: number; quantity: number; price: number }>,
    metodo_pago: 'efectivo' as 'efectivo' | 'qr'
});

const submit = () => {
    form.items = cartItems.value.map(item => ({
        food_id: item.food_id,
        quantity: item.quantity,
        price: item.price
    }));
    form.metodo_pago = 'efectivo';
    
    form.post(route('orders.store'), {
        onSuccess: () => {
            clearCart();
        },
    });
};

// Obtener nombre del cliente seleccionado o usuario autenticado
const getClientName = (clienteId: number | null): string => {
    if (clienteId) {
        const cliente = props.clientes.find(c => c.id === clienteId);
        return cliente?.name || 'Cliente';
    }
    return currentUser.value?.name || 'Cliente';
};

// Iniciar polling automático
const startPolling = () => {
    if (pollingInterval) return;
    
    pollingActive.value = true;
    pollingInterval = setInterval(async () => {
        if (!qrData.value?.payment_number || callbackReceived.value) {
            stopPolling();
            return;
        }

        try {
            const response = await axios.post(route('pagofacil.callbackStatus'), {
                payment_number: qrData.value.payment_number,
            });

            if (response.data.success) {
                callbackReceived.value = true;
                callbackData.value = response.data.data;
                paymentStatus.value = 'paid';
                stopPolling();
            }
        } catch (error) {
            // Silenciar errores durante el polling
        }
    }, 5000); // Consultar cada 5 segundos
};

// Detener polling
const stopPolling = () => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
    pollingActive.value = false;
};

// Generar QR
const generateQr = async () => {
    qrLoading.value = true;
    qrError.value = null;
    qrData.value = null;
    paymentStatus.value = null;
    callbackReceived.value = false;
    callbackData.value = null;
    stopPolling();

    try {
        const response = await axios.post(route('pagofacil.generateQr'), {
            client_id: qrClienteId.value || currentUser.value?.id,
            client_name: getClientName(qrClienteId.value),
            amount: total.value,
            items: cartItems.value.map(item => ({
                food_id: item.food_id,
                quantity: item.quantity,
                price: item.price
            })),
        });

        if (response.data.success) {
            qrData.value = response.data.data;
            paymentStatus.value = 'pending';
            // Iniciar polling automático al generar QR
            startPolling();
        } else {
            qrError.value = response.data.message || 'Error al generar QR';
        }
    } catch (error: any) {
        qrError.value = error.response?.data?.message || 'Error al conectar con el servicio de pago';
    } finally {
        qrLoading.value = false;
    }
};

// Verificar estado del pago manualmente
const checkPaymentStatus = async () => {
    if (!qrData.value?.payment_number) return;

    checkingStatus.value = true;
    try {
        const response = await axios.post(route('pagofacil.callbackStatus'), {
            payment_number: qrData.value.payment_number,
        });

        if (response.data.success) {
            callbackReceived.value = true;
            callbackData.value = response.data.data;
            paymentStatus.value = 'paid';
            stopPolling();
        } else {
            qrError.value = 'Aún no se ha recibido confirmación del pago';
            setTimeout(() => {
                qrError.value = null;
            }, 3000);
        }
    } catch (error) {
        qrError.value = 'Error al verificar el estado del pago';
        setTimeout(() => {
            qrError.value = null;
        }, 3000);
    } finally {
        checkingStatus.value = false;
    }
};

// Confirmar pedido después del pago QR
const confirmQrOrder = () => {
    form.cliente_id = qrClienteId.value;
    form.items = cartItems.value.map(item => ({
        food_id: item.food_id,
        quantity: item.quantity,
        price: item.price
    }));
    form.metodo_pago = 'qr';
    
    form.post(route('orders.store'), {
        onSuccess: () => {
            clearCart();
            qrData.value = null;
            paymentStatus.value = null;
            callbackReceived.value = false;
            callbackData.value = null;
            stopPolling();
        },
    });
};

// Resetear QR
const resetQr = () => {
    stopPolling();
    qrData.value = null;
    qrError.value = null;
    paymentStatus.value = null;
    callbackReceived.value = false;
    callbackData.value = null;
};

// Limpiar polling al desmontar componente
onUnmounted(() => {
    stopPolling();
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Menú', href: route('menu') },
    { title: 'Checkout', href: route('checkout') },
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
                        <Link :href="route('menu')">
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
                        <!-- Tabs de método de pago -->
                        <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex border-b border-gray-200 dark:border-gray-700">
                                <button
                                    type="button"
                                    @click="activeTab = 'efectivo'"
                                    :class="[
                                        'flex items-center gap-2 px-6 py-3 text-sm font-medium transition-colors',
                                        activeTab === 'efectivo'
                                            ? 'border-b-2 border-orange-600 text-orange-600'
                                            : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                                    ]"
                                >
                                    <Banknote class="h-5 w-5" />
                                    Pago Efectivo
                                </button>
                                <button
                                    type="button"
                                    @click="activeTab = 'qr'"
                                    :class="[
                                        'flex items-center gap-2 px-6 py-3 text-sm font-medium transition-colors',
                                        activeTab === 'qr'
                                            ? 'border-b-2 border-orange-600 text-orange-600'
                                            : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                                    ]"
                                >
                                    <QrCode class="h-5 w-5" />
                                    Pago QR
                                </button>
                            </div>

                            <!-- Contenido de Pago Efectivo -->
                            <div v-show="activeTab === 'efectivo'" class="p-6">
                                <div class="mb-4 flex items-center gap-2">
                                    <User class="h-5 w-5 text-orange-600" />
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ isCliente ? 'Datos del Pedido' : 'Seleccionar Cliente' }}
                                    </h2>
                                </div>
                                <div class="space-y-4">
                                    <!-- Selector de cliente - Solo visible para Admin y Cajero -->
                                    <div v-if="!isCliente">
                                        <Label for="cliente">Cliente</Label>
                                        <select
                                            id="cliente"
                                            v-model="form.cliente_id"
                                            class="mt-1 flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-600 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:ring-offset-gray-950 dark:focus-visible:ring-orange-500"
                                        >
                                            <option :value="null">Sin cliente registrado</option>
                                            <option 
                                                v-for="cliente in clientes" 
                                                :key="cliente.id"
                                                :value="cliente.id"
                                            >
                                                {{ cliente.name }}
                                            </option>
                                        </select>
                                        <p v-if="form.errors.cliente_id" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.cliente_id }}
                                        </p>
                                    </div>
                                    <!-- Info para cliente - muestra que el pedido se hará a su nombre -->
                                    <div v-else class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                                        <div class="flex items-center gap-2">
                                            <User class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                            <span class="text-sm text-blue-700 dark:text-blue-300">
                                                Pedido a nombre de: <strong>{{ currentUser?.name }}</strong>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contenido de Pago QR -->
                            <div v-show="activeTab === 'qr'" class="p-6">
                                <!-- Selección de cliente para QR - Solo visible para Admin y Cajero -->
                                <div class="mb-6">
                                    <div class="mb-4 flex items-center gap-2">
                                        <User class="h-5 w-5 text-orange-600" />
                                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                            {{ isCliente ? 'Datos del Pedido' : 'Seleccionar Cliente' }}
                                        </h2>
                                    </div>
                                    <div v-if="!isCliente">
                                        <Label for="cliente-qr">Cliente</Label>
                                        <select
                                            id="cliente-qr"
                                            v-model="qrClienteId"
                                            :disabled="!!qrData"
                                            class="mt-1 flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-600 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:ring-offset-gray-950 dark:focus-visible:ring-orange-500"
                                        >
                                            <option :value="null">Sin cliente registrado</option>
                                            <option 
                                                v-for="cliente in clientes" 
                                                :key="cliente.id"
                                                :value="cliente.id"
                                            >
                                                {{ cliente.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <!-- Info para cliente -->
                                    <div v-else class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                                        <div class="flex items-center gap-2">
                                            <User class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                            <span class="text-sm text-blue-700 dark:text-blue-300">
                                                Pedido a nombre de: <strong>{{ currentUser?.name }}</strong>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Estado sin QR generado -->
                                <div v-if="!qrData && !qrLoading" class="text-center">
                                    <div v-if="qrError" class="mb-4 rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                                        <div class="flex items-center gap-2 text-red-600 dark:text-red-400">
                                            <AlertCircle class="h-5 w-5" />
                                            <p>{{ qrError }}</p>
                                        </div>
                                    </div>
                                    <Button 
                                        type="button" 
                                        @click="generateQr"
                                        class="w-full"
                                    >
                                        <QrCode class="mr-2 h-5 w-5" />
                                        Generar Código QR
                                    </Button>
                                </div>

                                <!-- Cargando QR -->
                                <div v-if="qrLoading" class="flex flex-col items-center justify-center py-8">
                                    <Loader2 class="h-12 w-12 animate-spin text-orange-600" />
                                    <p class="mt-4 text-gray-500 dark:text-gray-400">
                                        Generando código QR...
                                    </p>
                                </div>

                                <!-- QR Generado -->
                                <div v-if="qrData && !qrLoading" class="space-y-4">
                                    <!-- Imagen QR -->
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="rounded-lg border-2 border-gray-200 bg-white p-4 dark:border-gray-700">
                                            <img 
                                                v-if="qrData.qr_base64"
                                                :src="'data:image/png;base64,' + qrData.qr_base64"
                                                alt="Código QR de pago"
                                                class="h-48 w-48"
                                            />
                                        </div>
                                        <!-- Transaction ID debajo del QR -->
                                        <p v-if="qrData.transaction_id" class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            ID Transacción: <span class="font-mono">{{ qrData.transaction_id }}</span>
                                        </p>
                                    </div>

                                    <!-- Estado del pago -->
                                    <div class="rounded-lg border p-4" :class="{
                                        'border-yellow-300 bg-yellow-50 dark:border-yellow-700 dark:bg-yellow-900/20': paymentStatus === 'pending',
                                        'border-green-300 bg-green-50 dark:border-green-700 dark:bg-green-900/20': paymentStatus === 'paid',
                                    }">
                                        <div class="flex items-center gap-3">
                                            <Clock v-if="paymentStatus === 'pending'" class="h-6 w-6 text-yellow-600" />
                                            <CheckCircle v-if="paymentStatus === 'paid'" class="h-6 w-6 text-green-600" />
                                            <div>
                                                <p class="font-semibold" :class="{
                                                    'text-yellow-800 dark:text-yellow-200': paymentStatus === 'pending',
                                                    'text-green-800 dark:text-green-200': paymentStatus === 'paid',
                                                }">
                                                    {{ paymentStatus === 'pending' ? 'Esperando pago...' : '¡Pago recibido!' }}
                                                </p>
                                                <p class="text-sm" :class="{
                                                    'text-yellow-700 dark:text-yellow-300': paymentStatus === 'pending',
                                                    'text-green-700 dark:text-green-300': paymentStatus === 'paid',
                                                }">
                                                    {{ paymentStatus === 'pending' 
                                                        ? 'Escanea el código QR con tu aplicación bancaria' 
                                                        : 'El pago ha sido confirmado' }}
                                                </p>
                                            </div>
                                        </div>
                                        <!-- Indicador de polling activo -->
                                        <div v-if="pollingActive && paymentStatus === 'pending'" class="mt-2 flex items-center gap-2 text-xs text-yellow-600 dark:text-yellow-400">
                                            <Loader2 class="h-3 w-3 animate-spin" />
                                            Verificando automáticamente...
                                        </div>
                                    </div>

                                    <!-- Botón para verificar estado del pago manualmente -->
                                    <Button 
                                        v-if="paymentStatus === 'pending'"
                                        type="button" 
                                        variant="outline"
                                        @click="checkPaymentStatus"
                                        :disabled="checkingStatus"
                                        class="w-full"
                                    >
                                        <Loader2 v-if="checkingStatus" class="mr-2 h-5 w-5 animate-spin" />
                                        <RefreshCw v-else class="mr-2 h-5 w-5" />
                                        {{ checkingStatus ? 'Verificando...' : 'Verificar manualmente' }}
                                    </Button>

                                    <!-- Datos del callback recibido -->
                                    <div v-if="callbackReceived && callbackData" 
                                        class="rounded-lg border border-green-300 bg-green-50 p-4 dark:border-green-700 dark:bg-green-900/20">
                                        <h4 class="font-semibold text-green-800 dark:text-green-200 mb-2">
                                            Detalles del Pago
                                        </h4>
                                        <div class="space-y-1 text-sm text-green-700 dark:text-green-300">
                                            <p><span class="font-medium">Estado:</span> {{ callbackData.status }}</p>
                                            <p><span class="font-medium">Fecha:</span> {{ callbackData.fecha }}</p>
                                            <p><span class="font-medium">Hora:</span> {{ callbackData.hora }}</p>
                                            <p><span class="font-medium">Método:</span> {{ callbackData.metodo_pago }}</p>
                                        </div>
                                    </div>

                                    <!-- Info de transacción -->
                                    <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                                        <p>Número de pedido: {{ qrData.payment_number }}</p>
                                        <p v-if="qrData.expiration_date">Expira: {{ qrData.expiration_date }}</p>
                                    </div>

                                    <!-- Botón cancelar/reintentar -->
                                    <Button 
                                        v-if="paymentStatus === 'pending'"
                                        type="button" 
                                        variant="outline"
                                        @click="resetQr"
                                        class="w-full"
                                    >
                                        Cancelar y generar nuevo QR
                                    </Button>
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
                                <div class="flex justify-between">
                                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">Total:</span>
                                    <span class="text-2xl font-bold text-orange-600">
                                        Bs. {{ total.toFixed(2) }}
                                    </span>
                                </div>
                            </div>

                            <Button
                                v-if="activeTab === 'efectivo'"
                                type="submit"
                                class="mt-6 w-full"
                                size="lg"
                                :disabled="form.processing"
                            >
                                <CreditCard class="mr-2 h-5 w-5" />
                                {{ form.processing ? 'Procesando...' : 'Confirmar Pedido' }}
                            </Button>
                            
                            <Button
                                v-else-if="activeTab === 'qr' && callbackReceived"
                                type="button"
                                class="mt-6 w-full"
                                size="lg"
                                :disabled="form.processing"
                                @click="confirmQrOrder"
                            >
                                <CheckCircle class="mr-2 h-5 w-5" />
                                {{ form.processing ? 'Procesando...' : 'Confirmar Pedido' }}
                            </Button>

                            <Button
                                v-else-if="activeTab === 'qr' && !callbackReceived"
                                type="button"
                                class="mt-6 w-full"
                                size="lg"
                                disabled
                            >
                                <Clock class="mr-2 h-5 w-5" />
                                Esperando confirmación de pago
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
