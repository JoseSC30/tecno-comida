<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    producto: {
        id: number;
        name: string;
    };
    insumos: Array<{
        id: number;
        name: string;
    }>;
    receta: Array<{
        insumo_id: number;
        insumo_name: string;
        cantidad: number;
        unidad: string;
    }>;
}>();

type RecetaItem = {
    insumo_id: number | string;
    cantidad: number | string;
    unidad: string;
};

const initialReceta: RecetaItem[] = props.receta.length > 0 
    ? props.receta.map(item => ({
        insumo_id: item.insumo_id,
        cantidad: item.cantidad,
        unidad: item.unidad,
    }))
    : [{
        insumo_id: '',
        cantidad: '',
        unidad: 'kg',
    }];

const form = useForm({
    receta: initialReceta,
    _method: 'PUT',
});

const agregarInsumo = () => {
    form.receta.push({
        insumo_id: '',
        cantidad: '',
        unidad: 'kg',
    });
};

const eliminarInsumo = (index: number) => {
    form.receta.splice(index, 1);
};

// Computed para obtener los IDs de insumos ya seleccionados
const insumosSeleccionados = computed(() => {
    return form.receta
        .map(item => item.insumo_id)
        .filter(id => id !== '');
});

// Función para verificar si un insumo está disponible para una fila específica
const insumoDisponible = (insumoId: number, currentIndex: number) => {
    const seleccionadoEnOtraFila = form.receta.some((item, index) => 
        index !== currentIndex && item.insumo_id === insumoId
    );
    return !seleccionadoEnOtraFila;
};

const submit = () => {
    // Validar que no haya insumos duplicados
    const insumoIds = form.receta.map(item => item.insumo_id);
    const duplicados = insumoIds.filter((id, index) => insumoIds.indexOf(id) !== index && id !== '');
    
    if (duplicados.length > 0) {
        alert('No puedes agregar el mismo insumo dos veces en la receta');
        return;
    }
    
    form.post(route('recetas.update', props.producto.id));
};

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Productos', href: route('productos.index') },
    { title: 'Receta', href: route('recetas.edit', props.producto.id) },
];
</script>

<template>
    <Head :title="`Receta: ${producto.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <Button variant="ghost" as-child class="mb-4">
                <Link :href="route('productos.index')">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Volver
                </Link>
            </Button>

            <div class="mx-auto max-w-4xl">
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h1 class="mb-6 text-2xl font-bold text-gray-900 dark:text-gray-100">
                        Receta: {{ producto.name }}
                    </h1>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-4">
                            <div v-for="(item, index) in form.receta" :key="index" class="flex gap-4 items-end">
                                <div class="flex-1">
                                    <Label :for="`insumo-${index}`">Insumo *</Label>
                                    <select
                                        :id="`insumo-${index}`"
                                        v-model="item.insumo_id"
                                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        required
                                    >
                                        <option value="">Seleccionar insumo</option>
                                        <option 
                                            v-for="insumo in insumos" 
                                            :key="insumo.id" 
                                            :value="insumo.id"
                                            :disabled="!insumoDisponible(insumo.id, index)"
                                        >
                                            {{ insumo.name }} {{ !insumoDisponible(insumo.id, index) ? '(ya agregado)' : '' }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors[`receta.${index}.insumo_id`]" class="mt-2" />
                                </div>

                                <div class="w-32">
                                    <Label :for="`cantidad-${index}`">Cantidad *</Label>
                                    <Input
                                        :id="`cantidad-${index}`"
                                        v-model="item.cantidad"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="mt-1"
                                        required
                                    />
                                    <InputError :message="form.errors[`receta.${index}.cantidad`]" class="mt-2" />
                                </div>

                                <div class="w-32">
                                    <Label :for="`unidad-${index}`">Unidad *</Label>
                                    <select
                                        :id="`unidad-${index}`"
                                        v-model="item.unidad"
                                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        required
                                    >
                                        <option value="kg">kg</option>
                                        <option value="g">g</option>
                                        <option value="L">L</option>
                                        <option value="ml">ml</option>
                                        <option value="unidad">unidad</option>
                                    </select>
                                    <InputError :message="form.errors[`receta.${index}.unidad`]" class="mt-2" />
                                </div>

                                <Button
                                    v-if="form.receta.length > 1"
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    @click="eliminarInsumo(index)"
                                >
                                    <Trash2 class="h-4 w-4 text-red-600" />
                                </Button>
                            </div>
                        </div>

                        <Button type="button" variant="outline" @click="agregarInsumo">
                            <Plus class="mr-2 h-4 w-4" />
                            Agregar Insumo
                        </Button>

                        <div class="flex gap-4">
                            <Button type="submit" :disabled="form.processing">
                                Guardar Receta
                            </Button>
                            <Button variant="outline" as-child>
                                <Link :href="route('productos.index')">
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
