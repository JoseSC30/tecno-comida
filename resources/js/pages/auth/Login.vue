<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/login';
import { Form, Head } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { useTheme } from '@/composables/useTheme';
import { computed } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const { effectiveMode } = useTheme();
const logoSrc = computed(() => {
    return effectiveMode.value === 'dark' ? '/images/Logo2.png' : '/images/Logo1.png';
});

const loginForm = store.form();
loginForm.action = route('login.store');
</script>

<template>
    <AuthBase>
        <Head title="Iniciar Sesión" />

        <!-- Logo/Banner -->
        <div class="mb-6 flex flex-col items-center">
            <div class="mb-3 flex items-center justify-center rounded-lg overflow-hidden">
                <img :src="logoSrc" alt="Logo" class="h-70 w-70 object-cover" />
            </div>
            <!-- <h1 class="text-2xl font-bold text-foreground">¡Bienvenido!</h1> -->
        </div>

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <Form
            v-bind="loginForm"
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
                        placeholder=" "
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Contraseña</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="route('password.request')"
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
                        placeholder=" "
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
                    class="mt-4 w-full text-white hover:opacity-90"
                    style="background-color: #ec1c24;"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    Iniciar Sesión
                </Button>
            </div>

            <div
                class="text-center text-sm text-muted-foreground"
                v-if="canRegister"
            >
                ¿No tienes una cuenta?
                <TextLink :href="route('register')" :tabindex="5">Regístrate</TextLink>
            </div>
        </Form>

        <!-- Footer informativo -->
        <!-- <div class="mt-6 rounded-lg bg-muted/50 p-4 text-center">
            <p class="text-xs text-muted-foreground">
                🍔 Sistema de Gestión de Comidas 🍕
            </p>
            <p class="mt-1 text-xs text-muted-foreground">
                Administra pedidos, menú y clientes en un solo lugar
            </p>
        </div> -->
    </AuthBase>
</template>
