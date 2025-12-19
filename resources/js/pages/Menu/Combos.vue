<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import CartSidebar from '@/components/CartSidebar.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import { useCart } from '@/composables/useCart';
import { Button } from '@/components/ui/button';
import { route } from 'ziggy-js';
import { asset } from '@/utils/asset';
import { ShoppingCart, Plus, Edit, Trash2 } from 'lucide-vue-next';

interface ComboProduct {
    id: number;
    name: string;
    price: number;
    image?: string;
    pivot?: { det_cantidad?: number };
    category?: {
        id?: number;
        name?: string;
    };
}

interface Combo {
    id: number;
    name: string;
    description?: string | null;
    type?: string | null;
    state?: string | null;
    discount?: number | null;
    start_date?: string | null;
    end_date?: string | null;
    products: ComboProduct[];
}

const props = defineProps<{
    combos: Combo[];
    productos: ComboProduct[];
    canCreate: boolean;
}>();

const { addToCart, openCart } = useCart();

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Menú', href: route('menu') },
    { title: 'Combos', href: route('menu.combos') },
];

const tipoOpciones = ['ALMUERZO', 'CENA', 'INDEFINIDO', 'ESPECIAL'];
const estadoOpciones = ['activo', 'finalizado'];

const form = useForm({
    name: '',
    description: '',
    type: 'INDEFINIDO',
    estado: 'activo',
    descuento: 0,
    fecha_inicio: '',
    fecha_fin: '',
    productos: [] as Array<{ id: number; cantidad: number }>,
});

const editForm = useForm({
    id: null as number | null,
    name: '',
    description: '',
    type: 'INDEFINIDO',
    estado: 'activo',
    descuento: 0,
    fecha_inicio: '',
    fecha_fin: '',
    productos: [] as Array<{ id: number; cantidad: number }>,
});

const editingId = ref<number | null>(null);

const creaSeleccion = reactive<Record<number, boolean>>({});
const creaCantidades = reactive<Record<number, number>>({});
const editSeleccion = reactive<Record<number, boolean>>({});
const editCantidades = reactive<Record<number, number>>({});

const toggleSelect = (map: Record<number, boolean>, cantidadesMap: Record<number, number>, id: number, checked: boolean) => {
    map[id] = checked;
    if (checked && !cantidadesMap[id]) cantidadesMap[id] = 1;
    if (!checked) delete cantidadesMap[id];
};

const getCantidad = (cantidadesMap: Record<number, number>, id: number) => {
    return cantidadesMap[id] ?? 1;
};

const setCantidad = (cantidadesMap: Record<number, number>, id: number, value: number) => {
    const parsed = Number(value);
    cantidadesMap[id] = Math.max(1, Number.isFinite(parsed) ? parsed : 1);
};

const buildProductosFromSelection = (selection: Record<number, boolean>, cantidadesMap: Record<number, number>) => {
    return Object.entries(selection)
        .filter(([, selected]) => selected)
        .map(([id]) => ({ id: Number(id), cantidad: getCantidad(cantidadesMap, Number(id)) }));
};

const resetForm = () => {
    form.reset('name', 'description', 'type', 'estado', 'descuento', 'fecha_inicio', 'fecha_fin', 'productos');
    form.clearErrors();
    Object.keys(creaSeleccion).forEach(key => delete creaSeleccion[Number(key)]);
    Object.keys(creaCantidades).forEach(key => delete creaCantidades[Number(key)]);
};

const startEdit = (combo: Combo) => {
    editingId.value = combo.id;
    editForm.id = combo.id;
    editForm.name = combo.name;
    editForm.description = combo.description || '';
    editForm.type = combo.type || 'INDEFINIDO';
    editForm.estado = combo.state || 'activo';
    editForm.descuento = combo.discount || 0;
    editForm.fecha_inicio = combo.start_date || '';
    editForm.fecha_fin = combo.end_date || '';
    editForm.productos = combo.products.map(p => ({ id: p.id, cantidad: p.pivot?.det_cantidad || 1 }));
    Object.keys(editSeleccion).forEach(key => delete editSeleccion[Number(key)]);
    Object.keys(editCantidades).forEach(key => delete editCantidades[Number(key)]);
    combo.products.forEach(p => {
        editSeleccion[p.id] = true;
        editCantidades[p.id] = p.pivot?.det_cantidad || 1;
    });
    editForm.clearErrors();
};

const cancelEdit = () => {
    editingId.value = null;
    editForm.reset();
    editForm.clearErrors();
    Object.keys(editSeleccion).forEach(key => delete editSeleccion[Number(key)]);
    Object.keys(editCantidades).forEach(key => delete editCantidades[Number(key)]);
};

const updateCombo = () => {
    if (!editingId.value) return;
    editForm.productos = buildProductosFromSelection(editSeleccion, editCantidades);
    editForm.put(route('menu.combos.update', editingId.value), {
        onSuccess: () => cancelEdit(),
    });
};

const deleteCombo = (comboId: number) => {
    editForm.delete(route('menu.combos.destroy', comboId));
};

const crearCombo = () => {
    form.productos = buildProductosFromSelection(creaSeleccion, creaCantidades);
    form.post(route('menu.combos.store'), {
        onSuccess: () => resetForm(),
    });
};

const quantityFromPivot = (prod: ComboProduct) => {
    // pivot.det_cantidad proviene de la tabla detalle_menus
    // si no está presente, asumimos 1
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore
    return prod.pivot?.det_cantidad ?? 1;
};

const precioCombo = (combo: Combo) => {
    const base = (combo.products || []).reduce((sum, prod) => sum + (prod.price || 0) * quantityFromPivot(prod), 0);
    const discount = Math.min(Math.max(combo.discount || 0, 0), 100);
    const discounted = base * (1 - discount / 100);

    return {
        base: Number(base.toFixed(2)),
        discounted: Number(discounted.toFixed(2)),
        discount,
    };
};

const comboDisponible = (combo: Combo) => {
    const today = new Date().toISOString().slice(0, 10);
    const isActive = combo.state === 'activo';
    const afterStart = !combo.start_date || combo.start_date <= today;
    const beforeEnd = !combo.end_date || combo.end_date >= today;
    return isActive && afterStart && beforeEnd;
};

const agregarAlCarrito = (combo: Combo) => {
    const pricing = precioCombo(combo);

    const componentes = combo.products.map(prod => ({
        product_id: prod.id,
        quantity: quantityFromPivot(prod),
    }));

    const componentKey = componentes.map(c => `${c.product_id}x${c.quantity}`).join('_') || 'base';

    addToCart({
        key: `combo-${combo.id}-${componentKey}`,
        food_id: combo.id,
        name: combo.name,
        price: pricing.discounted,
        quantity: 1,
        type: 'combo',
        comboId: combo.id,
        discount: pricing.discount,
        originalPrice: pricing.base,
        products: combo.products?.map(p => ({ id: p.id, name: p.name, price: p.price, image: p.image, quantity: quantityFromPivot(p) })),
        components: componentes,
    });
    openCart();
};

const combosOrdenados = computed(() => {
    return [...props.combos].sort((a, b) => (a.start_date || '').localeCompare(b.start_date || ''));
});
</script>

<template>
    <Head title="Combos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Combos</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Arma y ofrece combinaciones de productos con descuento.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="lg" @click="openCart">Ver carrito</Button>
                    <Link :href="route('menu')">
                        <Button variant="outline" size="lg">Volver al menú</Button>
                    </Link>
                </div>
            </div>

            <div v-if="props.canCreate" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Nuevo combo</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Define los productos que componen el combo y su descuento.</p>
                    </div>
                </div>
                <form @submit.prevent="crearCombo" class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-900"
                            placeholder="Ej. Combo Familiar"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                        <select
                            v-model="form.type"
                            class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-900"
                        >
                            <option v-for="tipo in tipoOpciones" :key="tipo" :value="tipo">{{ tipo }}</option>
                        </select>
                        <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
                        <select
                            v-model="form.estado"
                            class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-900"
                        >
                            <option v-for="estado in estadoOpciones" :key="estado" :value="estado">{{ estado }}</option>
                        </select>
                        <p v-if="form.errors.estado" class="mt-1 text-sm text-red-600">{{ form.errors.estado }}</p>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descuento (%)</label>
                        <input
                            v-model.number="form.descuento"
                            type="number"
                            min="0"
                            max="100"
                            step="0.5"
                            class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-900"
                            placeholder="0"
                        />
                        <p v-if="form.errors.descuento" class="mt-1 text-sm text-red-600">{{ form.errors.descuento }}</p>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de inicio</label>
                        <input
                            v-model="form.fecha_inicio"
                            type="date"
                            class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-900"
                        />
                        <p v-if="form.errors.fecha_inicio" class="mt-1 text-sm text-red-600">{{ form.errors.fecha_inicio }}</p>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de vencimiento</label>
                        <input
                            v-model="form.fecha_fin"
                            type="date"
                            class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-900"
                        />
                        <p v-if="form.errors.fecha_fin" class="mt-1 text-sm text-red-600">{{ form.errors.fecha_fin }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                        <textarea
                            v-model="form.description"
                            rows="2"
                            class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-900"
                            placeholder="Detalles del combo"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Productos incluidos</label>
                        <div class="mt-2 grid gap-2 md:grid-cols-3">
                            <div
                                v-for="producto in props.productos"
                                :key="producto.id"
                                class="flex items-center gap-2 rounded-md border border-gray-200 bg-white p-3 text-sm shadow-sm transition hover:border-orange-500 dark:border-gray-700 dark:bg-gray-900"
                            >
                                <input
                                    type="checkbox"
                                    :checked="!!creaSeleccion[producto.id]"
                                    @change="toggleSelect(creaSeleccion, creaCantidades, producto.id, ($event.target as HTMLInputElement).checked)"
                                    class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                />
                                <span class="flex-1">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ producto.name }}</span>
                                    <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">Bs. {{ producto.price }}</span>
                                </span>
                                <input
                                    type="number"
                                    min="1"
                                    class="h-10 w-16 rounded-md border border-gray-300 px-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900"
                                    :disabled="!creaSeleccion[producto.id]"
                                    :value="getCantidad(creaCantidades, producto.id)"
                                    @input="setCantidad(creaCantidades, producto.id, Number(($event.target as HTMLInputElement).value))"
                                />
                            </div>
                        </div>
                        <p v-if="form.errors.productos" class="mt-1 text-sm text-red-600">{{ form.errors.productos }}</p>
                    </div>

                    <div class="md:col-span-2 flex flex-wrap items-center gap-3">
                        <Button type="submit" :disabled="form.processing">
                            <Plus class="mr-2 h-4 w-4" />
                            Crear combo
                        </Button>
                        <Button type="button" variant="outline" :disabled="form.processing" @click="resetForm">
                            Limpiar
                        </Button>
                    </div>
                </form>
            </div>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="combo in combosOrdenados"
                    :key="combo.id"
                    class="flex h-full flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="relative h-40 overflow-hidden bg-gradient-to-br from-[#ec1c24] to-[#ff9ea3]">
                        <img
                            v-if="combo.products[0]?.image"
                            :src="asset(`storage/${combo.products[0].image}`)"
                            :alt="combo.name"
                            class="h-full w-full object-cover"
                        />
                        <div class="absolute left-3 top-3 flex flex-wrap gap-2">
                            <span class="rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-gray-800">{{ combo.type || 'INDEFINIDO' }}</span>
                            <span
                                :class="[
                                    'rounded-full px-3 py-1 text-xs font-semibold',
                                    combo.state === 'activo' ? 'bg-green-600 text-white' : 'bg-gray-400 text-white',
                                ]"
                            >
                                {{ combo.state || 'activo' }}
                            </span>
                            <span v-if="(combo.discount || 0) > 0" class="rounded-full bg-orange-600 px-3 py-1 text-xs font-semibold text-white">
                                -{{ combo.discount }}%
                            </span>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ combo.name }}</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400" v-if="combo.description">{{ combo.description }}</p>
                            </div>
                            <div v-if="props.canCreate" class="flex items-center gap-2">
                                <Button variant="ghost" size="sm" @click="startEdit(combo)">
                                    <Edit class="h-4 w-4" />
                                    Editar
                                </Button>
                                <Button variant="ghost" size="sm" @click="deleteCombo(combo.id)">
                                    <Trash2 class="h-4 w-4 text-red-600" />
                                </Button>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                            <div class="flex items-center justify-between">
                                <span>Vigencia:</span>
                                <span class="font-medium">{{ combo.start_date || '—' }} - {{ combo.end_date || '—' }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">Productos:</p>
                                <ul class="mt-1 space-y-3 text-gray-600 dark:text-gray-300">
                                    <li
                                        v-for="prod in combo.products"
                                        :key="prod.id"
                                        class="flex items-center justify-between rounded border border-gray-200 px-3 py-2 text-sm dark:border-gray-700"
                                    >
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ prod.name }}</p>
                                            <p class="text-xs text-gray-500">Bs. {{ prod.price }}</p>
                                        </div>
                                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">x{{ quantityFromPivot(prod) }}</div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 line-through" v-if="precioCombo(combo).discount > 0">Bs. {{ precioCombo(combo).base }}</p>
                                <p class="text-xl font-bold text-green-600">Bs. {{ precioCombo(combo).discounted }}</p>
                            </div>
                            <Button
                                :disabled="!comboDisponible(combo)"
                                class="whitespace-nowrap"
                                @click="agregarAlCarrito(combo)"
                            >
                                <ShoppingCart class="mr-2 h-4 w-4" />
                                {{ comboDisponible(combo) ? 'Agregar combo' : 'No disponible' }}
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="!combosOrdenados.length" class="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                Aún no se registraron combos.
            </div>

            <div v-if="editingId" class="rounded-lg border border-orange-200 bg-orange-50 p-6 dark:border-orange-900 dark:bg-orange-950">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-orange-800 dark:text-orange-100">Editar combo</h2>
                        <p class="text-sm text-orange-700 dark:text-orange-200">Actualiza los datos del combo seleccionado.</p>
                    </div>
                    <Button variant="ghost" size="sm" @click="cancelEdit">Cancelar</Button>
                </div>

                <form @submit.prevent="updateCombo" class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                        <input v-model="editForm.name" type="text" class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-900" />
                        <p v-if="editForm.errors.name" class="mt-1 text-sm text-red-600">{{ editForm.errors.name }}</p>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                        <select v-model="editForm.type" class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-900">
                            <option v-for="tipo in tipoOpciones" :key="tipo" :value="tipo">{{ tipo }}</option>
                        </select>
                        <p v-if="editForm.errors.type" class="mt-1 text-sm text-red-600">{{ editForm.errors.type }}</p>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
                        <select v-model="editForm.estado" class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-900">
                            <option v-for="estado in estadoOpciones" :key="estado" :value="estado">{{ estado }}</option>
                        </select>
                        <p v-if="editForm.errors.estado" class="mt-1 text-sm text-red-600">{{ editForm.errors.estado }}</p>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descuento (%)</label>
                        <input v-model.number="editForm.descuento" type="number" min="0" max="100" step="0.5" class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-900" />
                        <p v-if="editForm.errors.descuento" class="mt-1 text-sm text-red-600">{{ editForm.errors.descuento }}</p>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha inicio</label>
                        <input v-model="editForm.fecha_inicio" type="date" class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-900" />
                        <p v-if="editForm.errors.fecha_inicio" class="mt-1 text-sm text-red-600">{{ editForm.errors.fecha_inicio }}</p>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha fin</label>
                        <input v-model="editForm.fecha_fin" type="date" class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-900" />
                        <p v-if="editForm.errors.fecha_fin" class="mt-1 text-sm text-red-600">{{ editForm.errors.fecha_fin }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                        <textarea v-model="editForm.description" rows="2" class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 dark:border-gray-700 dark:bg-gray-900"></textarea>
                        <p v-if="editForm.errors.description" class="mt-1 text-sm text-red-600">{{ editForm.errors.description }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Productos</label>
                        <div class="mt-2 grid gap-2 md:grid-cols-3">
                            <div
                                v-for="producto in props.productos"
                                :key="producto.id"
                                class="flex items-center gap-2 rounded-md border border-gray-200 bg-white p-3 text-sm shadow-sm transition hover:border-orange-500 dark:border-gray-700 dark:bg-gray-900"
                            >
                                <input
                                    type="checkbox"
                                    :checked="!!editSeleccion[producto.id]"
                                    @change="toggleSelect(editSeleccion, editCantidades, producto.id, ($event.target as HTMLInputElement).checked)"
                                    class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                />
                                <span class="flex-1">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ producto.name }}</span>
                                    <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">Bs. {{ producto.price }}</span>
                                </span>
                                <input
                                    type="number"
                                    min="1"
                                    class="h-10 w-16 rounded-md border border-gray-300 px-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900"
                                    :disabled="!editSeleccion[producto.id]"
                                    :value="getCantidad(editCantidades, producto.id)"
                                    @input="setCantidad(editCantidades, producto.id, Number(($event.target as HTMLInputElement).value))"
                                />
                            </div>
                        </div>
                        <p v-if="editForm.errors.productos" class="mt-1 text-sm text-red-600">{{ editForm.errors.productos }}</p>
                    </div>

                    <div class="md:col-span-2 flex items-center gap-3">
                        <Button type="submit" :disabled="editForm.processing">Guardar cambios</Button>
                        <Button type="button" variant="outline" :disabled="editForm.processing" @click="cancelEdit">Cancelar</Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>

    <CartSidebar />
</template>
