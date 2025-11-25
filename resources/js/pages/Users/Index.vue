<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2, Plus } from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    usuarios: Array<{
        id: number;
        name: string;
        email: string;
        role: {
            id: number;
            name: string;
        };
        created_at: string;
    }>;
}>();

const usuarios = computed(() => props.usuarios);

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Usuarios', href: route('users.index') },
];
</script>

<template>
    <Head title="Gestionar Usuarios" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Gestionar Usuarios
                </h1>
                <Button as-child>
                    <Link :href="route('users.create')">
                        <Plus class="mr-2 h-4 w-4" />
                        Nuevo Usuario
                    </Link>
                </Button>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Nombre
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Email
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Rol
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Fecha de registro
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        <tr v-for="usuario in usuarios" :key="usuario.id">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ usuario.name }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                {{ usuario.email }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                    :class="{
                                        'bg-purple-100 text-purple-800': usuario.role.name === 'Administrador',
                                        'bg-blue-100 text-blue-800': usuario.role.name === 'Vendedor',
                                        'bg-green-100 text-green-800': usuario.role.name === 'Cliente',
                                    }"
                                >
                                    {{ usuario.role.name }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                {{ new Date(usuario.created_at).toLocaleDateString() }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <Button variant="ghost" size="sm" as-child class="mr-2">
                                    <Link :href="route('users.edit', usuario.id)">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    as="button"
                                    @click="() => {
                                        if (window.confirm('¿Estás seguro de eliminar este usuario?')) {
                                            router.delete(route('users.destroy', usuario.id));
                                        }
                                    }"
                                >
                                    <Trash2 class="h-4 w-4 text-red-600" />
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
