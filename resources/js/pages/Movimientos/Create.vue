<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { watch } from 'vue';

const props = defineProps<{
    insumos: Array<{
        id: number;
        name: string;
        unidad: string;
        stock: number;
    }>;
}>();

const form = useForm({
    insumo_id: '',
    tipo: 'entrada',
    cantidad: '',
    unidad: '',
});

// Cuando se selecciona un insumo, auto-completar la unidad
watch(() => form.insumo_id, (newInsumoId) => {
    const insumo = props.insumos.find(i => i.id === Number(newInsumoId));
    if (insumo) {
        form.unidad = insumo.unidad;
    }
});

const submit = () => {
    form.post(route('movimientos.store'));
};

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Movimientos', href: route('movimientos.index') },
    { title: 'Registrar', href: route('movimientos.create') },
];

const insumoSeleccionado = () => {
    if (!form.insumo_id) return null;
    return props.insumos.find(i => i.id === Number(form.insumo_id));
};
</script>

<template>
    <Head title="Registrar Movimiento" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <Button variant="ghost" as-child class="mb-4">
                <Link :href="route('movimientos.index')">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Volver
                </Link>
            </Button>

            <div class="mx-auto max-w-2xl">
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h1 class="mb-6 text-2xl font-bold text-gray-900 dark:text-gray-100">
                        Registrar Movimiento de Inventario
                    </h1>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="insumo_id">Insumo *</Label>
                            <select
                                id="insumo_id"
                                v-model="form.insumo_id"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                required
                            >
                                <option value="">Seleccionar insumo</option>
                                <option v-for="insumo in insumos" :key="insumo.id" :value="insumo.id">
                                    {{ insumo.name }} (Stock actual: {{ insumo.stock }} {{ insumo.unidad }})
                                </option>
                            </select>
                            <InputError :message="form.errors.insumo_id" class="mt-2" />
                        </div>

                        <div>
                            <Label for="tipo">Tipo de Movimiento *</Label>
                            <select
                                id="tipo"
                                v-model="form.tipo"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                required
                            >
                                <option value="entrada">Entrada (Compra/Ingreso)</option>
                                <option value="salida">Salida (Consumo/Pérdida)</option>
                            </select>
                            <InputError :message="form.errors.tipo" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label for="cantidad">Cantidad *</Label>
                                <Input
                                    id="cantidad"
                                    v-model="form.cantidad"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    class="mt-1"
                                    required
                                />
                                <InputError :message="form.errors.cantidad" class="mt-2" />
                            </div>

                            <div>
                                <Label for="unidad">Unidad *</Label>
                                <select
                                    id="unidad"
                                    v-model="form.unidad"
                                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    required
                                >
                                    <option value="">Seleccionar unidad</option>
                                    <option value="gr">Gramos (gr)</option>
                                    <option value="kg">Kilogramos (kg)</option>
                                    <option value="lt">Litros (lt)</option>
                                    <option value="ml">Mililitros (ml)</option>
                                    <option value="lb">Libras (lb)</option>
                                    <option value="qq">Quintales (qq)</option>
                                    <option value="oz">Onzas (oz)</option>
                                    <option value="unidad">Unidad</option>
                                    <option value="docena">Docena</option>
                                </select>
                                <InputError :message="form.errors.unidad" class="mt-2" />
                            </div>
                        </div>

                        <!-- Resumen del movimiento -->
                        <div v-if="insumoSeleccionado()" class="rounded-md bg-blue-50 p-4 dark:bg-blue-900/20">
                            <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100">Resumen:</h3>
                            <p class="mt-2 text-sm text-blue-800 dark:text-blue-200">
                                <span class="font-medium">Stock actual:</span> {{ insumoSeleccionado()!.stock }} {{ insumoSeleccionado()!.unidad }}
                            </p>
                            <p v-if="form.cantidad && form.tipo" class="mt-1 text-sm text-blue-800 dark:text-blue-200">
                                <span class="font-medium">Nuevo stock:</span> 
                                <span :class="form.tipo === 'entrada' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                    {{ form.tipo === 'entrada' 
                                        ? (insumoSeleccionado()!.stock + Number(form.cantidad))
                                        : (insumoSeleccionado()!.stock - Number(form.cantidad))
                                    }} {{ insumoSeleccionado()!.unidad }}
                                </span>
                            </p>
                        </div>

                        <div class="flex gap-4">
                            <Button type="submit" :disabled="form.processing">
                                Registrar Movimiento
                            </Button>
                            <Button variant="outline" as-child>
                                <Link :href="route('movimientos.index')">
                                    Cancelar
                                </Link>
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
