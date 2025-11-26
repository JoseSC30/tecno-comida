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
    categoria: {
        id: number;
        name: string;
        description: string;
    };
}>();

const categoria = computed(() => props.categoria);

const form = useForm({
    name: props.categoria.name,
    description: props.categoria.description || '',
});

const submit = () => {
    form.put(route('categorias.update', props.categoria.id));
};

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Categorías', href: route('categorias.index') },
    { title: 'Editar Categoría', href: route('categorias.edit', props.categoria.id) },
];
</script>

<template>
    <Head title="Editar Categoría" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <Button variant="ghost" as-child class="mb-4">
                <Link :href="route('categorias.index')">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Volver
                </Link>
            </Button>

            <div class="mx-auto max-w-2xl">
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h1 class="mb-6 text-2xl font-bold text-gray-900 dark:text-gray-100">
                        Editar Categoría
                    </h1>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="name">Nombre *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1"
                                placeholder="Ej: Entradas, Platos Fuertes, Bebidas..."
                                required
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <div>
                            <Label for="description">Descripción</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                class="mt-1"
                                placeholder="Descripción opcional de la categoría"
                                rows="4"
                            />
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>

                        <div class="flex justify-end gap-4">
                            <Button type="button" variant="outline" as-child>
                                <Link :href="route('categorias.index')">Cancelar</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Guardando...' : 'Actualizar Categoría' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
