<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, ShoppingBag, UtensilsCrossed, Users, Package, List, ArrowRightLeft, CalendarDays, ClipboardList } from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const mainNavItems = computed((): NavItem[] => {
    const items: NavItem[] = [];

    // Dashboard - Todos tienen acceso
    items.push({
        title: 'Dashboard',
        href: route('dashboard'),
        icon: LayoutGrid,
    });

    // Menú - Todos tienen acceso
    items.push({
        title: 'Menú',
        href: route('menu'),
        icon: UtensilsCrossed,
    });

    // Mis Pedidos - Todos tienen acceso
    items.push({
        title: 'Mis Pedidos',
        href: route('orders.index'),
        icon: ShoppingBag,
    });

    // Reservas - Solo Admin, Cajero y Cliente
    if (user.value?.is_admin || user.value?.is_cajero || user.value?.is_cliente) {
        items.push(
            {
                title: 'Reservar Mesa',
                href: route('reservas.index'),
                icon: CalendarDays,
            },
            {
                title: 'Mis Reservas',
                href: route('reservas.list'),
                icon: ClipboardList,
            }
        );
    }

    // Admin: acceso completo a gestión
    if (user.value?.is_admin) {
        items.push(
            {
                title: 'Gestionar Productos',
                href: route('productos.index'),
                icon: UtensilsCrossed,
            },
            {
                title: 'Categorías',
                href: route('categorias.index'),
                icon: List,
            },
            {
                title: 'Gestionar Insumos',
                href: route('insumos.index'),
                icon: Package,
            },
            {
                title: 'Gestionar Movimientos',
                href: route('movimientos.index'),
                icon: ArrowRightLeft,
            },
            {
                title: 'Gestionar Usuarios',
                href: route('users.index'),
                icon: Users,
            }
        );
    }

    // Cajero: todo menos usuarios
    if (user.value?.is_cajero && !user.value?.is_admin) {
        items.push(
            {
                title: 'Gestionar Productos',
                href: route('seller.productos.index'),
                icon: UtensilsCrossed,
            },
            {
                title: 'Categorías',
                href: route('seller.categorias.index'),
                icon: List,
            }
        );
    }

    return items;
});

// const footerNavItems: NavItem[] = [
//     {
//         title: 'Github Repo',
//         href: 'https://github.com/laravel/vue-starter-kit',
//         icon: Folder,
//     },
//     {
//         title: 'Documentation',
//         href: 'https://laravel.com/docs/starter-kits#vue',
//         icon: BookOpen,
//     },
// ];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <!-- <NavFooter :items="footerNavItems" /> -->
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
