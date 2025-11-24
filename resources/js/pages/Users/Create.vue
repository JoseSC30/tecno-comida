<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{
    roles: Array<{
        id: number;
        name: string;
        description: string;
    }>;
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_id: '',
});

const submit = () => {
    form.post('/admin/users', {
        onSuccess: () => form.reset(),
    });
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Usuarios', href: '/admin/users' },
    { title: 'Nuevo Usuario', href: '/admin/users/create' },
];
</script>

<template>
    <Head title="Nuevo Usuario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <Button variant="ghost" as-child class="mb-4">
                <Link href="/admin/users">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Volver
                </Link>
            </Button>

            <div class="mx-auto max-w-2xl">
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h1 class="mb-6 text-2xl font-bold text-gray-900 dark:text-gray-100">
                        Nuevo Usuario
                    </h1>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="name">Nombre completo *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1"
                                required
                                autofocus
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <div>
                            <Label for="email">Correo electrónico *</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1"
                                required
                            />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>

                        <div>
                            <Label for="role_id">Rol *</Label>
                            <select
                                id="role_id"
                                v-model="form.role_id"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                required
                            >
                                <option value="">Seleccionar rol</option>
                                <option v-for="role in roles" :key="role.id" :value="role.id">
                                    {{ role.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.role_id" class="mt-2" />
                            <p v-if="form.role_id" class="mt-1 text-sm text-gray-500">
                                {{ roles.find(r => r.id == form.role_id)?.description }}
                            </p>
                        </div>

                        <div>
                            <Label for="password">Contraseña *</Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-1"
                                required
                            />
                            <InputError :message="form.errors.password" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">Mínimo 8 caracteres</p>
                        </div>

                        <div>
                            <Label for="password_confirmation">Confirmar contraseña *</Label>
                            <Input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="mt-1"
                                required
                            />
                            <InputError :message="form.errors.password_confirmation" class="mt-2" />
                        </div>

                        <div class="flex justify-end gap-4">
                            <Button type="button" variant="outline" as-child>
                                <Link href="/admin/users">Cancelar</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Creando...' : 'Crear Usuario' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
