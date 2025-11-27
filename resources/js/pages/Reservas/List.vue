<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { 
    CalendarDays, 
    Clock, 
    Users, 
    CheckCircle,
    XCircle,
    AlertCircle,
    Utensils,
    Plus,
    QrCode,
    Loader2,
    CreditCard,
    Banknote
} from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { ref } from 'vue';
import axios from 'axios';

interface Reserva {
    id: number;
    fecha: string;
    hora: string;
    estado: string;
    monto: number | string;
    personas: number;
    notas: string | null;
    cliente: string;
    mesa: {
        numero: number;
        capacidad: number;
    };
    // Installment payment fields
    tipo_pago?: 'completo' | 'cuotas';
    num_cuotas?: number;
    monto_cuota?: number | string;
    monto_pagado?: number | string;
    cuotas_pagadas?: number;
}

const props = defineProps<{
    reservas: Reserva[];
}>();

// QR payment state for second installment
const payingReservaId = ref<number | null>(null);
const qrData = ref<{
    qr_base64: string | null;
    transaction_id: string | null;
    payment_number: string | null;
    expiration_date: string | null;
} | null>(null);
const qrLoading = ref(false);
const qrError = ref<string | null>(null);
const paymentConfirmed = ref(false);
const pollingInterval = ref<ReturnType<typeof setInterval> | null>(null);

const getStatusBadge = (estado: string) => {
    switch (estado) {
        case 'pendiente':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
        case 'confirmada':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        case 'pagada':
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 'pagada_parcial':
            return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400';
        case 'completada':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
        case 'cancelada':
            return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
    }
};

const getStatusIcon = (estado: string) => {
    switch (estado) {
        case 'pendiente':
            return AlertCircle;
        case 'confirmada':
        case 'pagada':
        case 'completada':
            return CheckCircle;
        case 'pagada_parcial':
            return CreditCard;
        case 'cancelada':
            return XCircle;
        default:
            return AlertCircle;
    }
};

const getStatusLabel = (estado: string) => {
    switch (estado) {
        case 'pagada_parcial':
            return 'Pago Parcial';
        default:
            return estado.charAt(0).toUpperCase() + estado.slice(1);
    }
};

const formatDate = (fecha: string) => {
    const date = new Date(fecha + 'T00:00:00');
    return date.toLocaleDateString('es-ES', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};

const updateStatus = (id: number, estado: string) => {
    router.patch(route('reservas.updateStatus', id), { estado });
};

const cancelReservation = (id: number) => {
    if (confirm('¿Estás seguro de cancelar esta reserva?')) {
        router.delete(route('reservas.cancel', id));
    }
};

// Helper to check if reservation has pending installment
const hasPendingInstallment = (reserva: Reserva) => {
    return reserva.estado === 'pagada_parcial' && 
           reserva.tipo_pago === 'cuotas' && 
           (reserva.cuotas_pagadas || 0) < (reserva.num_cuotas || 2);
};

// Get pending amount
const getPendingAmount = (reserva: Reserva): number => {
    const monto = typeof reserva.monto === 'string' ? parseFloat(reserva.monto) : reserva.monto;
    const pagado = typeof reserva.monto_pagado === 'string' ? parseFloat(reserva.monto_pagado || '0') : (reserva.monto_pagado || 0);
    return monto - pagado;
};

// Format money amount
const formatMoney = (amount: number | string): string => {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    return num.toFixed(2);
};

// Generate QR for second installment
const generateSecondInstallmentQr = async (reserva: Reserva) => {
    payingReservaId.value = reserva.id;
    qrLoading.value = true;
    qrError.value = null;
    qrData.value = null;
    paymentConfirmed.value = false;

    const pendingAmount = getPendingAmount(reserva);

    try {
        const response = await axios.post(route('pagofacil.generateQr'), {
            client_id: null,
            client_name: reserva.cliente,
            amount: pendingAmount,
            description: `Reserva Mesa #${reserva.mesa.numero} - Cuota 2 de 2`,
            items: [{
                description: `Cuota 2/2 - Mesa #${reserva.mesa.numero}`,
                quantity: 1,
                price: pendingAmount,
            }],
        });

        if (response.data.success) {
            qrData.value = response.data.data;
            startPolling(reserva.id);
        } else {
            qrError.value = response.data.message || 'Error al generar QR';
        }
    } catch (error: any) {
        qrError.value = error.response?.data?.message || 'Error al conectar con el servicio de pago';
    } finally {
        qrLoading.value = false;
    }
};

// Start polling for payment confirmation
const startPolling = (reservaId: number) => {
    if (pollingInterval.value) return;
    
    pollingInterval.value = setInterval(async () => {
        if (!qrData.value?.payment_number || paymentConfirmed.value) {
            stopPolling();
            return;
        }

        try {
            const response = await axios.post(route('pagofacil.callbackStatus'), {
                payment_number: qrData.value.payment_number,
            });

            if (response.data.success) {
                paymentConfirmed.value = true;
                stopPolling();
            }
        } catch (error) {
            // Silent error during polling
        }
    }, 5000);
};

// Stop polling
const stopPolling = () => {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
    }
};

// Confirm second installment payment
const confirmSecondInstallment = async () => {
    if (!payingReservaId.value || !qrData.value?.transaction_id) return;

    try {
        const targetUrl = `/reservas/${payingReservaId.value}/pay-second-installment`;
        console.log('Sending to URL:', targetUrl);
        console.log('With transaction_id:', qrData.value.transaction_id);

        const response = await axios.post(targetUrl, {
            transaction_id: String(qrData.value.transaction_id),
        });

        console.log('Response:', response);
        
        if (response.data.success) {
            closePaymentModal();
            // Reload the page to see updated data
            window.location.reload();
        } else {
            qrError.value = response.data.error || 'Error al confirmar el pago';
        }
    } catch (error: any) {
        console.error('Error:', error);
        console.error('Error response:', error.response?.data);
        qrError.value = error.response?.data?.error || error.response?.data?.message || 'Error al confirmar el pago';
    }
};

// Close payment modal
const closePaymentModal = () => {
    stopPolling();
    payingReservaId.value = null;
    qrData.value = null;
    qrError.value = null;
    paymentConfirmed.value = false;
};

// Pay second installment with cash
const paySecondInstallmentCash = async (reserva: Reserva) => {
    if (!confirm('¿Confirmar pago en efectivo de la segunda cuota?')) return;

    try {
        const response = await axios.post(`/reservas/${reserva.id}/pay-second-installment`, {
            transaction_id: 'EFECTIVO-' + Date.now(),
        });

        if (response.data.success) {
            window.location.reload();
        } else {
            alert(response.data.error || 'Error al registrar el pago');
        }
    } catch (error: any) {
        console.error('Error:', error);
        alert(error.response?.data?.error || 'Error al registrar el pago');
    }
};

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Mis Reservas', href: route('reservas.list') },
];
</script>

<template>
    <Head title="Mis Reservas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mx-auto max-w-6xl">
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-orange-100 p-3 dark:bg-orange-900">
                            <CalendarDays class="h-8 w-8 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                Mis Reservas
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400">
                                Historial de reservas de mesa
                            </p>
                        </div>
                    </div>
                    <a :href="route('reservas.index')">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Nueva Reserva
                        </Button>
                    </a>
                </div>

                <!-- Lista de reservas -->
                <div v-if="reservas.length > 0" class="space-y-4">
                    <div
                        v-for="reserva in reservas"
                        :key="reserva.id"
                        class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="rounded-xl bg-gradient-to-br from-orange-500 to-red-500 p-4 text-white">
                                    <Utensils class="h-6 w-6" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        Mesa #{{ reserva.mesa.numero }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ reserva.cliente }}
                                    </p>
                                    <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                        <div class="flex items-center gap-1">
                                            <CalendarDays class="h-4 w-4" />
                                            {{ formatDate(reserva.fecha) }}
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <Clock class="h-4 w-4" />
                                            {{ reserva.hora }}
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <Users class="h-4 w-4" />
                                            {{ reserva.personas }} {{ reserva.personas === 1 ? 'persona' : 'personas' }}
                                        </div>
                                    </div>
                                    <p v-if="reserva.notas" class="mt-2 text-sm italic text-gray-500 dark:text-gray-400">
                                        "{{ reserva.notas }}"
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-col items-end gap-2">
                                <span :class="[
                                    'inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium',
                                    getStatusBadge(reserva.estado)
                                ]">
                                    <component :is="getStatusIcon(reserva.estado)" class="h-4 w-4" />
                                    {{ getStatusLabel(reserva.estado) }}
                                </span>
                                
                                <!-- Payment Info -->
                                <div class="text-right">
                                    <p class="text-lg font-bold text-orange-600">
                                        Bs {{ formatMoney(reserva.monto) }}
                                    </p>
                                    <!-- Show installment info if applicable -->
                                    <div v-if="reserva.tipo_pago === 'cuotas'" class="mt-1 text-xs text-gray-500">
                                        <div class="flex items-center gap-1 justify-end">
                                            <span v-if="reserva.cuotas_pagadas === 2" class="text-green-600">
                                                ✓ 2/2 cuotas pagadas
                                            </span>
                                            <span v-else-if="reserva.cuotas_pagadas === 1" class="text-amber-600">
                                                1/2 cuotas - Pendiente: Bs {{ formatMoney(getPendingAmount(reserva)) }}
                                            </span>
                                            <span v-else class="text-gray-500">
                                                0/2 cuotas pagadas
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="reserva.estado !== 'cancelada' && reserva.estado !== 'completada'" class="flex flex-wrap gap-2">
                                    <!-- Pay second installment buttons -->
                                    <Button 
                                        v-if="hasPendingInstallment(reserva)"
                                        size="sm"
                                        class="bg-amber-600 hover:bg-amber-700"
                                        @click="generateSecondInstallmentQr(reserva)"
                                    >
                                        <QrCode class="mr-1 h-4 w-4" />
                                        2da Cuota QR
                                    </Button>
                                    <Button 
                                        v-if="hasPendingInstallment(reserva)"
                                        size="sm"
                                        variant="outline"
                                        @click="paySecondInstallmentCash(reserva)"
                                    >
                                        <Banknote class="mr-1 h-4 w-4" />
                                        2da Cuota Efectivo
                                    </Button>
                                    <Button 
                                        v-if="reserva.estado === 'pagada'"
                                        size="sm"
                                        variant="outline"
                                        @click="updateStatus(reserva.id, 'completada')"
                                    >
                                        Completar
                                    </Button>
                                    <Button 
                                        size="sm"
                                        variant="destructive"
                                        @click="cancelReservation(reserva.id)"
                                    >
                                        Cancelar
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado vacío -->
                <div v-else class="rounded-lg border border-gray-200 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-800">
                    <CalendarDays class="mx-auto h-16 w-16 text-gray-400" />
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                        No tienes reservas
                    </h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Haz tu primera reserva de mesa
                    </p>
                    <div class="mt-6">
                        <a :href="route('reservas.index')">
                            <Button>
                                <Plus class="mr-2 h-4 w-4" />
                                Nueva Reserva
                            </Button>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Payment Modal for Second Installment -->
        <div 
            v-if="payingReservaId !== null" 
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="closePaymentModal"
        >
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md mx-4 shadow-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Pagar Segunda Cuota
                    </h3>
                    <button 
                        @click="closePaymentModal"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        <XCircle class="h-6 w-6" />
                    </button>
                </div>

                <!-- Loading state -->
                <div v-if="qrLoading" class="flex flex-col items-center py-8">
                    <Loader2 class="h-12 w-12 animate-spin text-orange-600" />
                    <p class="mt-4 text-gray-600 dark:text-gray-400">Generando código QR...</p>
                </div>

                <!-- Error state -->
                <div v-else-if="qrError" class="text-center py-8">
                    <AlertCircle class="h-12 w-12 mx-auto text-red-500" />
                    <p class="mt-4 text-red-600">{{ qrError }}</p>
                    <Button @click="closePaymentModal" class="mt-4">
                        Cerrar
                    </Button>
                </div>

                <!-- QR Code -->
                <div v-else-if="qrData && !paymentConfirmed" class="text-center">
                    <div class="bg-white p-4 rounded-lg inline-block mb-4">
                        <img :src="'data:image/png;base64,' + qrData.qr_base64" alt="Código QR" class="w-48 h-48 mx-auto" />
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        Escanea el código QR para pagar la segunda cuota
                    </p>
                    <div class="mb-4 p-2 bg-gray-100 dark:bg-gray-700 rounded text-xs">
                        <span class="text-gray-500 dark:text-gray-400">Transaction ID:</span>
                        <span class="ml-1 font-mono text-gray-900 dark:text-gray-100">{{ qrData.transaction_id }}</span>
                    </div>
                    <div class="flex items-center justify-center gap-2 text-amber-600">
                        <Loader2 class="h-4 w-4 animate-spin" />
                        <span class="text-sm">Esperando confirmación de pago...</span>
                    </div>
                </div>

                <!-- Payment confirmed -->
                <div v-else-if="paymentConfirmed" class="text-center py-4">
                    <CheckCircle class="h-16 w-16 mx-auto text-green-500" />
                    <h4 class="mt-4 text-lg font-semibold text-green-600">¡Pago Confirmado!</h4>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Tu segunda cuota ha sido pagada exitosamente.
                    </p>
                    <Button @click="confirmSecondInstallment" class="mt-4 w-full">
                        Confirmar y Cerrar
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
