<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { UtensilsCrossed, ChefHat } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthBase
        title="Sistema de Gestión de Comidas"
        description="Ingresa tus credenciales para acceder al sistema"
    >
        <Head title="Iniciar Sesión" />

        <!-- Logo/Banner -->
        <div class="mb-6 flex flex-col items-center">
            <div class="mb-3 flex items-center justify-center rounded-full bg-gradient-to-br from-orange-400 to-red-500 p-4 shadow-lg">
                <UtensilsCrossed class="h-12 w-12 text-white" />
            </div>
            <h1 class="text-2xl font-bold text-foreground">¡Bienvenido!</h1>
            <p class="text-sm text-muted-foreground">Gestiona tu negocio de comidas</p>
        </div>

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Correo Electrónico</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="tu@email.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Contraseña</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm"
                            :tabindex="5"
                        >
                            ¿Olvidaste tu contraseña?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Recordarme</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    <ChefHat v-else class="mr-2 h-4 w-4" />
                    Iniciar Sesión
                </Button>
            </div>

            <div
                class="text-center text-sm text-muted-foreground"
                v-if="canRegister"
            >
                ¿No tienes una cuenta?
                <TextLink :href="register()" :tabindex="5">Regístrate</TextLink>
            </div>
        </Form>

        <!-- Footer informativo -->
        <div class="mt-6 rounded-lg bg-muted/50 p-4 text-center">
            <p class="text-xs text-muted-foreground">
                🍔 Sistema de Gestión de Comidas 🍕
            </p>
            <p class="mt-1 text-xs text-muted-foreground">
                Administra pedidos, menú y clientes en un solo lugar
            </p>
        </div>
    </AuthBase>
</template>
