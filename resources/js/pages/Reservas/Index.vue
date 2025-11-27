<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { 
    CalendarDays, 
    Clock, 
    Users, 
    Banknote, 
    QrCode, 
    CheckCircle, 
    AlertCircle, 
    Loader2,
    RefreshCw,
    Utensils,
    User
} from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { ref, computed, watch, onUnmounted } from 'vue';
import axios from 'axios';

interface Cliente {
    id: number;
    name: string;
}

interface Mesa {
    id: number;
    numero: number;
    capacidad: number;
    icon: string;
    disponible?: boolean;
}

const props = defineProps<{
    clientes: Cliente[];
    mesas: Mesa[];
}>();

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);

// Estado del formulario
const selectedMesa = ref<Mesa | null>(null);
const selectedDate = ref('');
const selectedTime = ref('');
const selectedPersonas = ref(2);
const selectedClienteId = ref<number | null>(null);
const notas = ref('');
const activeTab = ref<'efectivo' | 'qr'>('efectivo');
const tipoPago = ref<'completo' | 'cuotas'>('completo');

// Estado de disponibilidad
const availableMesas = ref<Mesa[]>(props.mesas.map(m => ({ ...m, disponible: true })));
const loadingAvailability = ref(false);

// Estado para pago QR
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

// Horarios disponibles
const availableTimes = [
    '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
    '18:00', '18:30', '19:00', '19:30', '20:00', '20:30', '21:00', '21:30'
];

// Fecha mínima (hoy)
const minDate = computed(() => {
    const today = new Date();
    return today.toISOString().split('T')[0];
});

// Costo por persona
const costoPorPersona = 20;
const totalCosto = computed(() => selectedPersonas.value * costoPorPersona);

// Monto a pagar ahora (completo o primera cuota)
const montoPagarAhora = computed(() => {
    if (tipoPago.value === 'cuotas') {
        return totalCosto.value / 2;
    }
    return totalCosto.value;
});

// Número de cuotas
const numCuotas = computed(() => tipoPago.value === 'cuotas' ? 2 : 1);

// Verificar disponibilidad cuando cambia fecha u hora
watch([selectedDate, selectedTime], async () => {
    if (selectedDate.value && selectedTime.value) {
        await checkAvailability();
    }
});

// Verificar disponibilidad
const checkAvailability = async () => {
    if (!selectedDate.value || !selectedTime.value) return;

    loadingAvailability.value = true;
    try {
        const response = await axios.post(route('reservas.availability'), {
            fecha: selectedDate.value,
            hora: selectedTime.value,
        });

        if (response.data.success) {
            availableMesas.value = response.data.mesas;
            // Deseleccionar mesa si ya no está disponible
            if (selectedMesa.value) {
                const mesa = availableMesas.value.find(m => m.id === selectedMesa.value?.id);
                if (mesa && !mesa.disponible) {
                    selectedMesa.value = null;
                }
            }
        }
    } catch (error) {
        console.error('Error checking availability:', error);
    } finally {
        loadingAvailability.value = false;
    }
};

// Seleccionar mesa
const selectMesa = (mesa: Mesa) => {
    if (!mesa.disponible) return;
    if (selectedMesa.value?.id === mesa.id) {
        selectedMesa.value = null;
    } else {
        selectedMesa.value = mesa;
        // Ajustar número de personas si excede capacidad
        if (selectedPersonas.value > mesa.capacidad) {
            selectedPersonas.value = mesa.capacidad;
        }
    }
};

// Obtener nombre del cliente
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
    }, 5000);
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
    if (!selectedMesa.value || !selectedDate.value || !selectedTime.value) {
        qrError.value = 'Complete todos los campos antes de generar el QR';
        return;
    }

    qrLoading.value = true;
    qrError.value = null;
    qrData.value = null;
    paymentStatus.value = null;
    callbackReceived.value = false;
    callbackData.value = null;
    stopPolling();

    try {
        const cuotaLabel = tipoPago.value === 'cuotas' ? ' (Cuota 1 de 2)' : '';
        const response = await axios.post(route('pagofacil.generateQr'), {
            client_id: selectedClienteId.value || currentUser.value?.id,
            client_name: getClientName(selectedClienteId.value),
            amount: montoPagarAhora.value,
            description: `Reserva Mesa #${selectedMesa.value.numero} - ${selectedDate.value} ${selectedTime.value}${cuotaLabel}`,
            items: [{
                description: `Reserva Mesa #${selectedMesa.value.numero}${cuotaLabel}`,
                quantity: tipoPago.value === 'cuotas' ? 1 : selectedPersonas.value,
                price: montoPagarAhora.value,
            }],
        });

        if (response.data.success) {
            qrData.value = response.data.data;
            paymentStatus.value = 'pending';
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

// Confirmar reserva con pago efectivo
const submitEfectivo = () => {
    if (!selectedMesa.value || !selectedDate.value || !selectedTime.value) return;

    router.post(route('reservas.store'), {
        cliente_id: selectedClienteId.value,
        mes_id: selectedMesa.value.id,
        fecha: selectedDate.value,
        hora: selectedTime.value,
        personas: selectedPersonas.value,
        notas: notas.value,
        metodo_pago: 'efectivo',
        tipo_pago: tipoPago.value,
        num_cuotas: numCuotas.value,
    });
};

// Confirmar reserva después del pago QR
const confirmQrReservation = () => {
    if (!selectedMesa.value || !selectedDate.value || !selectedTime.value || !qrData.value) {
        console.error('Missing data:', { 
            mesa: selectedMesa.value, 
            date: selectedDate.value, 
            time: selectedTime.value, 
            qrData: qrData.value 
        });
        qrError.value = 'Datos incompletos. Por favor, genere un nuevo QR.';
        return;
    }

    const transactionId = qrData.value.transaction_id;
    
    if (!transactionId) {
        qrError.value = 'No se encontró el ID de transacción. Por favor, genere un nuevo QR.';
        console.error('Transaction ID missing:', qrData.value);
        return;
    }

    console.log('Confirming reservation with:', {
        cliente_id: selectedClienteId.value,
        mes_id: selectedMesa.value.id,
        fecha: selectedDate.value,
        hora: selectedTime.value,
        personas: selectedPersonas.value,
        transaction_id: transactionId,
        tipo_pago: tipoPago.value,
        num_cuotas: numCuotas.value,
    });

    router.post(route('reservas.store'), {
        cliente_id: selectedClienteId.value,
        mes_id: selectedMesa.value.id,
        fecha: selectedDate.value,
        hora: selectedTime.value,
        personas: selectedPersonas.value,
        notas: notas.value,
        metodo_pago: 'qr',
        transaction_id: transactionId,
        tipo_pago: tipoPago.value,
        num_cuotas: numCuotas.value,
    }, {
        onError: (errors) => {
            console.error('Reservation errors:', errors);
            qrError.value = Object.values(errors).flat().join(', ');
        },
        onSuccess: () => {
            console.log('Reservation created successfully');
        }
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

// Limpiar polling al desmontar
onUnmounted(() => {
    stopPolling();
});

// Obtener color de capacidad
const getCapacityColor = (capacidad: number) => {
    if (capacidad <= 2) return 'from-pink-500 to-rose-500';
    if (capacidad <= 4) return 'from-blue-500 to-indigo-500';
    if (capacidad <= 6) return 'from-green-500 to-emerald-500';
    if (capacidad <= 8) return 'from-yellow-500 to-orange-500';
    return 'from-purple-500 to-violet-500';
};

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Reservar Mesa', href: route('reservas.index') },
];
</script>

<template>
    <Head title="Reservar Mesa" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mx-auto max-w-6xl">
                <div class="mb-6 flex items-center gap-3">
                    <div class="rounded-full bg-orange-100 p-3 dark:bg-orange-900">
                        <Utensils class="h-8 w-8 text-orange-600 dark:text-orange-400" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            Reservar Mesa
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400">
                            Selecciona fecha, hora y mesa para tu reserva
                        </p>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Columna izquierda: Selección de fecha/hora y mesas -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Fecha y Hora -->
                        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                            <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                <CalendarDays class="h-5 w-5 text-orange-600" />
                                Fecha y Hora
                            </h2>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label for="fecha">Fecha de reserva</Label>
                                    <input
                                        id="fecha"
                                        type="date"
                                        v-model="selectedDate"
                                        :min="minDate"
                                        class="mt-1 flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <Label for="hora">Hora de reserva</Label>
                                    <select
                                        id="hora"
                                        v-model="selectedTime"
                                        class="mt-1 flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    >
                                        <option value="">Seleccionar hora</option>
                                        <option v-for="time in availableTimes" :key="time" :value="time">
                                            {{ time }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Selección de Mesa -->
                        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                            <div class="mb-4 flex items-center justify-between">
                                <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    <Utensils class="h-5 w-5 text-orange-600" />
                                    Seleccionar Mesa
                                </h2>
                                <div v-if="loadingAvailability" class="flex items-center gap-2 text-sm text-gray-500">
                                    <Loader2 class="h-4 w-4 animate-spin" />
                                    Verificando disponibilidad...
                                </div>
                            </div>

                            <div v-if="!selectedDate || !selectedTime" class="rounded-lg bg-gray-50 p-8 text-center dark:bg-gray-900">
                                <CalendarDays class="mx-auto h-12 w-12 text-gray-400" />
                                <p class="mt-2 text-gray-600 dark:text-gray-400">
                                    Selecciona fecha y hora para ver las mesas disponibles
                                </p>
                            </div>

                            <!-- Grid de mesas -->
                            <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-4 md:grid-cols-5">
                                <button
                                    v-for="mesa in availableMesas"
                                    :key="mesa.id"
                                    @click="selectMesa(mesa)"
                                    :disabled="!mesa.disponible"
                                    :class="[
                                        'relative flex flex-col items-center justify-center rounded-xl p-4 transition-all duration-200',
                                        mesa.disponible
                                            ? selectedMesa?.id === mesa.id
                                                ? 'bg-gradient-to-br ' + getCapacityColor(mesa.capacidad) + ' text-white shadow-lg scale-105'
                                                : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600'
                                            : 'bg-red-100 text-red-400 cursor-not-allowed dark:bg-red-900/30'
                                    ]"
                                >
                                    <span class="text-2xl">{{ mesa.icon }}</span>
                                    <span class="mt-1 text-lg font-bold">{{ mesa.numero }}</span>
                                    <span class="text-xs opacity-75">{{ mesa.capacidad }} personas</span>
                                    <span 
                                        v-if="!mesa.disponible" 
                                        class="absolute inset-0 flex items-center justify-center rounded-xl bg-black/50"
                                    >
                                        <span class="text-xs font-medium text-white">Reservada</span>
                                    </span>
                                </button>
                            </div>

                            <!-- Leyenda -->
                            <div class="mt-6 flex flex-wrap gap-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="h-4 w-4 rounded bg-gradient-to-br from-pink-500 to-rose-500"></span>
                                    <span class="text-gray-600 dark:text-gray-400">2 personas</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="h-4 w-4 rounded bg-gradient-to-br from-blue-500 to-indigo-500"></span>
                                    <span class="text-gray-600 dark:text-gray-400">4 personas</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="h-4 w-4 rounded bg-gradient-to-br from-green-500 to-emerald-500"></span>
                                    <span class="text-gray-600 dark:text-gray-400">6 personas</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="h-4 w-4 rounded bg-gradient-to-br from-yellow-500 to-orange-500"></span>
                                    <span class="text-gray-600 dark:text-gray-400">8 personas</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="h-4 w-4 rounded bg-gradient-to-br from-purple-500 to-violet-500"></span>
                                    <span class="text-gray-600 dark:text-gray-400">10+ personas</span>
                                </div>
                            </div>
                        </div>

                        <!-- Detalles de la reserva -->
                        <div v-if="selectedMesa" class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                            <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                <Users class="h-5 w-5 text-orange-600" />
                                Detalles de la Reserva
                            </h2>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label for="personas">Número de personas</Label>
                                    <select
                                        id="personas"
                                        v-model="selectedPersonas"
                                        class="mt-1 flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    >
                                        <option v-for="n in selectedMesa.capacidad" :key="n" :value="n">
                                            {{ n }} {{ n === 1 ? 'persona' : 'personas' }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <Label for="cliente">Cliente (opcional)</Label>
                                    <select
                                        id="cliente"
                                        v-model="selectedClienteId"
                                        class="mt-1 flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    >
                                        <option :value="null">Usuario actual</option>
                                        <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
                                            {{ cliente.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <Label for="notas">Notas adicionales</Label>
                                    <textarea
                                        id="notas"
                                        v-model="notas"
                                        rows="2"
                                        placeholder="Alergias, preferencias, ocasión especial..."
                                        class="mt-1 flex w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha: Resumen y pago -->
                    <div class="space-y-6">
                        <!-- Resumen -->
                        <div class="sticky top-4 rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                            <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    Resumen de Reserva
                                </h2>
                            </div>
                            <div class="p-4 space-y-4">
                                <div v-if="selectedMesa" class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
                                    <div class="flex items-center gap-3">
                                        <span class="text-3xl">{{ selectedMesa.icon }}</span>
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-gray-100">
                                                Mesa #{{ selectedMesa.numero }}
                                            </p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Capacidad: {{ selectedMesa.capacidad }} personas
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center text-gray-500 py-4">
                                    Selecciona una mesa
                                </div>

                                <div v-if="selectedDate && selectedTime" class="flex items-center gap-3 text-sm">
                                    <CalendarDays class="h-4 w-4 text-gray-500" />
                                    <span class="text-gray-900 dark:text-gray-100">{{ selectedDate }}</span>
                                    <Clock class="h-4 w-4 text-gray-500" />
                                    <span class="text-gray-900 dark:text-gray-100">{{ selectedTime }}</span>
                                </div>

                                <div v-if="selectedMesa" class="flex items-center gap-3 text-sm">
                                    <Users class="h-4 w-4 text-gray-500" />
                                    <span class="text-gray-900 dark:text-gray-100">
                                        {{ selectedPersonas }} {{ selectedPersonas === 1 ? 'persona' : 'personas' }}
                                    </span>
                                </div>

                                <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Costo por persona</span>
                                        <span class="text-gray-900 dark:text-gray-100">Bs {{ costoPorPersona.toFixed(2) }}</span>
                                    </div>
                                    <div class="mt-2 flex justify-between text-lg font-bold">
                                        <span class="text-gray-900 dark:text-gray-100">Total</span>
                                        <span class="text-orange-600">Bs {{ totalCosto.toFixed(2) }}</span>
                                    </div>
                                </div>

                                <!-- Tipo de pago (completo o cuotas) -->
                                <div v-if="selectedMesa" class="border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <Label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 block">
                                        Tipo de Pago
                                    </Label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <button
                                            type="button"
                                            @click="tipoPago = 'completo'; resetQr()"
                                            :class="[
                                                'flex flex-col items-center justify-center rounded-lg border-2 p-3 transition-all',
                                                tipoPago === 'completo'
                                                    ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20'
                                                    : 'border-gray-200 hover:border-gray-300 dark:border-gray-700'
                                            ]"
                                        >
                                            <span class="text-sm font-medium" :class="tipoPago === 'completo' ? 'text-orange-600' : 'text-gray-700 dark:text-gray-300'">
                                                Pago Completo
                                            </span>
                                            <span class="text-xs text-gray-500 mt-1">
                                                Bs {{ totalCosto.toFixed(2) }}
                                            </span>
                                        </button>
                                        <button
                                            type="button"
                                            @click="tipoPago = 'cuotas'; resetQr()"
                                            :class="[
                                                'flex flex-col items-center justify-center rounded-lg border-2 p-3 transition-all',
                                                tipoPago === 'cuotas'
                                                    ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20'
                                                    : 'border-gray-200 hover:border-gray-300 dark:border-gray-700'
                                            ]"
                                        >
                                            <span class="text-sm font-medium" :class="tipoPago === 'cuotas' ? 'text-orange-600' : 'text-gray-700 dark:text-gray-300'">
                                                2 Cuotas
                                            </span>
                                            <span class="text-xs text-gray-500 mt-1">
                                                2 x Bs {{ (totalCosto / 2).toFixed(2) }}
                                            </span>
                                        </button>
                                    </div>
                                    <p v-if="tipoPago === 'cuotas'" class="mt-2 text-xs text-amber-600 dark:text-amber-400">
                                        ⚡ Pagarás la primera cuota ahora (Bs {{ (totalCosto / 2).toFixed(2) }}). La segunda cuota deberás pagarla antes de tu reserva.
                                    </p>
                                </div>
                            </div>

                            <!-- Tabs de método de pago -->
                            <div v-if="selectedMesa" class="border-t border-gray-200 dark:border-gray-700">
                                <div class="flex border-b border-gray-200 dark:border-gray-700">
                                    <button
                                        type="button"
                                        @click="activeTab = 'efectivo'"
                                        :class="[
                                            'flex-1 flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium transition-colors',
                                            activeTab === 'efectivo'
                                                ? 'border-b-2 border-orange-600 text-orange-600'
                                                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'
                                        ]"
                                    >
                                        <Banknote class="h-4 w-4" />
                                        Efectivo
                                    </button>
                                    <button
                                        type="button"
                                        @click="activeTab = 'qr'"
                                        :class="[
                                            'flex-1 flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium transition-colors',
                                            activeTab === 'qr'
                                                ? 'border-b-2 border-orange-600 text-orange-600'
                                                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'
                                        ]"
                                    >
                                        <QrCode class="h-4 w-4" />
                                        QR
                                    </button>
                                </div>

                                <!-- Contenido Efectivo -->
                                <div v-show="activeTab === 'efectivo'" class="p-4">
                                    <Button 
                                        @click="submitEfectivo"
                                        class="w-full"
                                        :disabled="!selectedMesa || !selectedDate || !selectedTime"
                                    >
                                        <Banknote class="mr-2 h-4 w-4" />
                                        Confirmar Reserva
                                    </Button>
                                    <p class="mt-2 text-xs text-center text-gray-500">
                                        El pago se realizará al llegar al restaurante
                                    </p>
                                </div>

                                <!-- Contenido QR -->
                                <div v-show="activeTab === 'qr'" class="p-4">
                                    <!-- Sin QR generado -->
                                    <div v-if="!qrData && !qrLoading" class="text-center">
                                        <div v-if="qrError" class="mb-4 rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
                                            <p class="text-sm text-red-600 dark:text-red-400">{{ qrError }}</p>
                                        </div>
                                        <Button @click="generateQr" class="w-full">
                                            <QrCode class="mr-2 h-4 w-4" />
                                            Generar QR de Pago
                                        </Button>
                                    </div>

                                    <!-- Cargando -->
                                    <div v-else-if="qrLoading" class="flex flex-col items-center gap-3 py-4">
                                        <Loader2 class="h-8 w-8 animate-spin text-orange-600" />
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Generando código QR...</p>
                                    </div>

                                    <!-- QR generado -->
                                    <div v-else-if="qrData" class="text-center">
                                        <!-- Pago pendiente -->
                                        <div v-if="paymentStatus === 'pending'">
                                            <div class="mb-3 rounded-lg bg-yellow-50 p-2 dark:bg-yellow-900/20">
                                                <div class="flex items-center justify-center gap-2 text-yellow-600 dark:text-yellow-400">
                                                    <Clock class="h-4 w-4" />
                                                    <span class="text-sm">Esperando pago...</span>
                                                </div>
                                            </div>
                                            <img
                                                :src="'data:image/png;base64,' + qrData.qr_base64"
                                                alt="QR de pago"
                                                class="mx-auto max-w-[180px] rounded-lg border"
                                            />
                                            <div class="mt-3 rounded-lg bg-gray-100 p-2 dark:bg-gray-700">
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Número de Transacción:</p>
                                                <p class="font-mono text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ qrData.transaction_id }}
                                                </p>
                                            </div>
                                            <div class="mt-3 flex gap-2">
                                                <Button 
                                                    size="sm" 
                                                    variant="outline"
                                                    @click="checkPaymentStatus"
                                                    :disabled="checkingStatus"
                                                    class="flex-1"
                                                >
                                                    <RefreshCw :class="['h-3 w-3 mr-1', { 'animate-spin': checkingStatus }]" />
                                                    Verificar
                                                </Button>
                                                <Button 
                                                    size="sm" 
                                                    variant="outline"
                                                    @click="resetQr"
                                                    class="flex-1"
                                                >
                                                    Cancelar
                                                </Button>
                                            </div>
                                            <p v-if="pollingActive" class="mt-2 text-xs text-gray-500">
                                                Verificando automáticamente cada 5 segundos...
                                            </p>
                                        </div>

                                        <!-- Pago confirmado -->
                                        <div v-else-if="paymentStatus === 'paid'" class="space-y-4">
                                            <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                                                <CheckCircle class="mx-auto h-10 w-10 text-green-500" />
                                                <p class="mt-2 font-semibold text-green-700 dark:text-green-400">
                                                    ¡Pago confirmado!
                                                </p>
                                            </div>
                                            <Button @click="confirmQrReservation" class="w-full">
                                                <CheckCircle class="mr-2 h-4 w-4" />
                                                Confirmar Reserva
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
