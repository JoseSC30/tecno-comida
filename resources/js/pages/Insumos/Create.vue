<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { route } from 'ziggy-js';

const form = useForm({
    name: '',
    unidad: '',
    stock: '',
});

const submit = () => {
    form.post(route('insumos.store'), {
        onSuccess: () => form.reset(),
    });
};

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Insumos', href: route('insumos.index') },
    { title: 'Nuevo Insumo', href: route('insumos.create') },
];
</script>

<template>
    <Head title="Nuevo Insumo" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <Button variant="ghost" as-child class="mb-4">
                <Link :href="route('insumos.index')">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Volver
                </Link>
            </Button>

            <div class="mx-auto max-w-2xl">
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h1 class="mb-6 text-2xl font-bold text-gray-900 dark:text-gray-100">
                        Nuevo Insumo
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

                        <div>
                            <Label for="stock">Stock *</Label>
                            <Input
                                id="stock"
                                v-model="form.stock"
                                type="number"
                                step="0.01"
                                min="0"
                                class="mt-1"
                                required
                            />
                            <InputError :message="form.errors.stock" class="mt-2" />
                        </div>

                        <div class="flex gap-4">
                            <Button type="submit" :disabled="form.processing">
                                Guardar
                            </Button>
                            <Button variant="outline" as-child>
                                <Link :href="route('insumos.index')">
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
