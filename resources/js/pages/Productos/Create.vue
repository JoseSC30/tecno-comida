<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    categorias: Array<{
        id: number;
        name: string;
    }>;
}>();

const categorias = computed(() => props.categorias);

const form = useForm({
    name: '',
    description: '',
    price: '',
    cost: '',
    category_id: '',
    image: null as File | null,
    available: true,
});

const selectedFileName = ref<string>('');

const submit = () => {
    form.post(route('productos.store'), {
        forceFormData: true,
        onSuccess: () => form.reset(),
    });
};

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Productos', href: route('productos.index') },
    { title: 'Nuevo Producto', href: route('productos.create') },
];
</script>

<template>
    <Head title="Nuevo Producto" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <Button variant="ghost" as-child class="mb-4">
                <Link :href="route('productos.index')">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Volver
                </Link>
            </Button>

            <div class="mx-auto max-w-2xl">
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h1 class="mb-6 text-2xl font-bold text-gray-900 dark:text-gray-100">
                        Nuevo Producto
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
                            <Input
                                id="description"
                                v-model="form.description"
                                type="text"
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
                                <Label for="cost">Costo (Bs.)</Label>
                                <Input
                                    id="cost"
                                    v-model="form.cost"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="mt-1"
                                />
                                <InputError :message="form.errors.cost" class="mt-2" />
                            </div>
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

                        <div>
                            <Label for="image">Imagen</Label>
                            <div class="mt-1">
                                <input
                                    id="image"
                                    type="file"
                                    accept="image/*"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary/90 cursor-pointer"
                                    @change="(e: Event) => {
                                        const target = e.target as HTMLInputElement;
                                        form.image = target.files?.[0] || null;
                                    }"
                                />
                            </div>
                            <InputError :message="form.errors.image" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">Formatos: JPG, PNG, WEBP (Max: 50MB)</p>
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
                                <Link :href="route('productos.index')">Cancelar</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Guardando...' : 'Guardar Producto' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
