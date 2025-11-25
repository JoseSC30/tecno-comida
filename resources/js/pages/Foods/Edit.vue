<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    producto: {
        id: number;
        name: string;
        description: string;
        price: number;
        category_id: number;
        image: string;
        available: boolean;
    };
    categorias: Array<{
        id: number;
        name: string;
    }>;
}>();

const producto = computed(() => props.producto);
const categorias = computed(() => props.categorias);

const form = useForm({
    name: props.producto.name,
    description: props.producto.description || '',
    price: props.producto.price,
    category_id: props.producto.category_id,
    image: null as File | null,
    available: props.producto.available,
    _method: 'PUT',
});

const submit = () => {
    form.post(route('foods.update', props.producto.id));
};

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Comidas', href: route('foods.index') },
    { title: 'Editar Comida', href: route('foods.edit', props.producto.id) },
];
</script>

<template>
    <Head title="Editar Comida" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <Button variant="ghost" as-child class="mb-4">
                <Link :href="route('foods.index')">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Volver
                </Link>
            </Button>

            <div class="mx-auto max-w-2xl">
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h1 class="mb-6 text-2xl font-bold text-gray-900 dark:text-gray-100">
                        Editar Comida
                    </h1>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="name">Nombre *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1"
                                required
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <div>
                            <Label for="description">Descripción</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="mt-1"
                            />
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <Label for="price">Precio (Bs.) *</Label>
                                <Input
                                    id="price"
                                    v-model="form.price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="mt-1"
                                    required
                                />
                                <InputError :message="form.errors.price" class="mt-2" />
                            </div>

                            <div>
                                <Label for="category_id">Categoría *</Label>
                                <select
                                    id="category_id"
                                    v-model="form.category_id"
                                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    required
                                >
                                    <option value="">Seleccionar categoría</option>
                                    <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                        {{ categoria.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.category_id" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <Label>Imagen actual</Label>
                            <div v-if="producto.image" class="mt-2">
                                <img :src="`/storage/${producto.image}`" alt="Imagen actual" class="h-32 w-32 rounded object-cover" />
                            </div>
                            <div v-else class="mt-2 text-sm text-gray-500">Sin imagen</div>
                        </div>

                        <div>
                            <Label for="image">Cambiar imagen</Label>
                            <Input
                                id="image"
                                type="file"
                                accept="image/*"
                                class="mt-1"
                                @change="(e: Event) => {
                                    const target = e.target as HTMLInputElement;
                                    form.image = target.files?.[0] || null;
                                }"
                            />
                            <InputError :message="form.errors.image" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">Formatos: JPG, PNG, WEBP (Max: 2MB)</p>
                        </div>

                        <div class="flex items-center">
                            <input
                                id="available"
                                v-model="form.available"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                            />
                            <Label for="available" class="ml-2 !mb-0">Disponible para la venta</Label>
                        </div>

                        <div class="flex justify-end gap-4">
                            <Button type="button" variant="outline" as-child>
                                <Link :href="route('foods.index')">Cancelar</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Guardando...' : 'Actualizar Comida' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
