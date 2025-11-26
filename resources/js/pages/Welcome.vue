<script setup lang="ts">
import VisitCounterBar from '@/components/VisitCounterBar.vue';
import { useTheme } from '@/composables/useTheme';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { UtensilsCrossed, ShoppingCart, ChefHat, Users, BarChart3, Clock } from 'lucide-vue-next';
import { onMounted } from 'vue';

defineOptions({ layout: null });

defineProps<{
    canRegister: boolean;
}>();

const { applyTheme, effectiveMode } = useTheme();

onMounted(() => {
    applyTheme();
});

function login() {
    return route('login');
}

function register() {
    return route('register');
}

function dashboard() {
    return route('dashboard');
}
</script>

<template>
    <Head title="Bienvenido" />
    <div class="min-h-screen bg-background text-foreground">
        <!-- Navigation -->
        <header class="fixed top-0 left-0 right-0 z-50 bg-background/95 backdrop-blur-sm border-b border-border">
            <nav class="container mx-auto px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary/10 rounded-lg">
                        <UtensilsCrossed class="h-8 w-8 text-primary" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">Restaurante Tina</h1>
                        <!-- <p class="text-xs text-muted-foreground">Sistema de Gestión</p> -->
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="px-6 py-2 bg-primary text-primary-foreground rounded-md font-medium hover:bg-primary/90 transition-colors"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="px-6 py-2 text-foreground hover:text-primary transition-colors font-medium"
                        >
                            Iniciar Sesión
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="px-6 py-2 bg-primary text-primary-foreground rounded-md font-medium hover:bg-primary/90 transition-colors"
                        >
                            Registrarse
                        </Link>
                    </template>
                </div>
            </nav>
        </header>

        <!-- Hero Section -->
        <section class="pt-32 pb-20 px-6">
            <div class="container mx-auto max-w-6xl">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Hero Text -->
                    <!-- <div class="space-y-6">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 rounded-full text-sm font-medium text-primary">
                            <ChefHat class="h-4 w-4" />
                            <span>Gestión Profesional de Comidas</span>
                        </div>
                        <h1 class="text-5xl lg:text-6xl font-bold leading-tight">
                            Restaurante Tina
                        </h1>
                        <p class="text-xl text-muted-foreground leading-relaxed">
                            Sistema completo para gestionar pedidos, inventario, clientes y estadísticas de tu negocio gastronómico en un solo lugar.
                        </p>
                        <div class="flex flex-wrap gap-4 pt-4">
                            <Link
                                :href="$page.props.auth.user ? dashboard() : login()"
                                class="px-8 py-4 bg-card text-card-foreground border border-border rounded-lg font-semibold hover:bg-accent transition-all"
                            >
                                {{ $page.props.auth.user ? 'Ir al Dashboard' : 'Iniciar Sesión' }}
                            </Link>
                        </div>
                    </div> -->
                    
                    <!-- Logo Grande -->
                    <div class="flex items-center justify-center lg:col-span-1">
                        <img 
                            :src="effectiveMode === 'dark' ? '/images/Logo2.png' : '/images/Logo1.png'" 
                            alt="Logo Restaurante Tina" 
                            class="w-120 h-120 object-contain"
                        />
                    </div>

                    <!-- Hero Visual -->
                    <div class="relative">
                        <div class="aspect-square bg-primary/5 rounded-3xl p-8 relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-transparent"></div>
                            <div class="relative z-10 h-full flex items-center justify-center">
                                <div class="grid grid-cols-2 gap-6 w-full">
                                    <div class="bg-card p-6 rounded-xl shadow-lg border border-border hover:scale-105 transition-transform">
                                        <ShoppingCart class="h-10 w-10 text-primary mb-3" />
                                        <h3 class="font-bold text-lg">Pedidos</h3>
                                        <p class="text-sm text-muted-foreground">Gestión en tiempo real</p>
                                    </div>
                                    <div class="bg-card p-6 rounded-xl shadow-lg border border-border hover:scale-105 transition-transform mt-8">
                                        <ChefHat class="h-10 w-10 text-primary mb-3" />
                                        <h3 class="font-bold text-lg">Menú</h3>
                                        <p class="text-sm text-muted-foreground">Control total</p>
                                    </div>
                                    <div class="bg-card p-6 rounded-xl shadow-lg border border-border hover:scale-105 transition-transform -mt-4">
                                        <Users class="h-10 w-10 text-primary mb-3" />
                                        <h3 class="font-bold text-lg">Reservas</h3>
                                        <p class="text-sm text-muted-foreground">Gestión de reservas</p>
                                    </div>
                                    <div class="bg-card p-6 rounded-xl shadow-lg border border-border hover:scale-105 transition-transform mt-4">
                                        <BarChart3 class="h-10 w-10 text-primary mb-3" />
                                        <h3 class="font-bold text-lg">Pago</h3>
                                        <p class="text-sm text-muted-foreground">Gestión de pagos</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 px-6 bg-accent/30">
            <div class="container mx-auto max-w-6xl">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold mb-4">Características Principales</h2>
                    <!-- <p class="text-xl text-muted-foreground">Todo lo que necesitas para gestionar tu negocio</p> -->
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-card p-8 rounded-xl shadow-md border border-border hover:shadow-xl transition-all hover:-translate-y-1">
                        <div class="p-3 bg-primary/10 rounded-lg w-fit mb-4">
                            <ShoppingCart class="h-8 w-8 text-primary" />
                        </div>
                        <h3 class="text-xl font-bold mb-3">Gestión de Pedidos</h3>
                        <p class="text-muted-foreground leading-relaxed">
                            Procesa y administra pedidos en tiempo real con sistema de carrito inteligente y seguimiento completo.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-card p-8 rounded-xl shadow-md border border-border hover:shadow-xl transition-all hover:-translate-y-1">
                        <div class="p-3 bg-primary/10 rounded-lg w-fit mb-4">
                            <ChefHat class="h-8 w-8 text-primary" />
                        </div>
                        <h3 class="text-xl font-bold mb-3">Control de Menú</h3>
                        <p class="text-muted-foreground leading-relaxed">
                            Administra tu catálogo de comidas, categorías, precios e inventario de forma sencilla e intuitiva.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-card p-8 rounded-xl shadow-md border border-border hover:shadow-xl transition-all hover:-translate-y-1">
                        <div class="p-3 bg-primary/10 rounded-lg w-fit mb-4">
                            <Users class="h-8 w-8 text-primary" />
                        </div>
                        <h3 class="text-xl font-bold mb-3">Base de Clientes</h3>
                        <p class="text-muted-foreground leading-relaxed">
                            Gestiona clientes, vendedores y administradores con sistema de roles y permisos personalizado.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="bg-card p-8 rounded-xl shadow-md border border-border hover:shadow-xl transition-all hover:-translate-y-1">
                        <div class="p-3 bg-primary/10 rounded-lg w-fit mb-4">
                            <BarChart3 class="h-8 w-8 text-primary" />
                        </div>
                        <h3 class="text-xl font-bold mb-3">Estadísticas</h3>
                        <p class="text-muted-foreground leading-relaxed">
                            Analiza ventas, rendimiento y métricas clave con reportes visuales y datos en tiempo real.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="bg-card p-8 rounded-xl shadow-md border border-border hover:shadow-xl transition-all hover:-translate-y-1">
                        <div class="p-3 bg-primary/10 rounded-lg w-fit mb-4">
                            <Clock class="h-8 w-8 text-primary" />
                        </div>
                        <h3 class="text-xl font-bold mb-3">Gestión en Tiempo Real</h3>
                        <p class="text-muted-foreground leading-relaxed">
                            Actualiza y sincroniza información instantáneamente entre todos los módulos del sistema.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="bg-card p-8 rounded-xl shadow-md border border-border hover:shadow-xl transition-all hover:-translate-y-1">
                        <div class="p-3 bg-primary/10 rounded-lg w-fit mb-4">
                            <UtensilsCrossed class="h-8 w-8 text-primary" />
                        </div>
                        <h3 class="text-xl font-bold mb-3">Interfaz Adaptable</h3>
                        <p class="text-muted-foreground leading-relaxed">
                            Temas personalizables (Niños, Jóvenes, Adultos) con modo claro/oscuro y opciones de accesibilidad.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-12 px-6 border-t border-border bg-accent/20">
            <div class="container mx-auto max-w-6xl">
                <div class="grid md:grid-cols-3 gap-8 mb-8">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 bg-primary/10 rounded-lg">
                                <UtensilsCrossed class="h-6 w-6 text-primary" />
                            </div>
                            <h3 class="text-lg font-bold">Restaurante Tina</h3>
                        </div>
                        <p class="text-muted-foreground">
                            Sistema completo de gestión para negocios gastronómicos
                        </p>
                    </div>
                    
                    <div>
                        <h4 class="font-bold mb-4">Desarrolladores</h4>
                        <ul class="space-y-2 text-muted-foreground">
                            <li>Edith Georgina Sossa Castro</li>
                            <li>Jose Luis Sossa Castro</li>
                            <li>Jessica Rivero Morales </li>
 
                        </ul>
                    </div>
                </div>
                <div class="text-center pt-8 border-t border-border">
                    <VisitCounterBar class="mx-auto mb-6 max-w-4xl" />
                    <p class="text-muted-foreground">
                        © 2025 Restaurante Tina. Sistema de Gestión de Comidas.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
