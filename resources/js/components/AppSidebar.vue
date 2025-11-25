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
import { BookOpen, Folder, LayoutGrid, ShoppingBag, UtensilsCrossed, Users, Package, List } from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const mainNavItems = computed((): NavItem[] => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: route('dashboard'),
            icon: LayoutGrid,
        },
        {
            title: 'Menú',
            href: route('menu'),
            icon: UtensilsCrossed,
        },
        {
            title: 'Mis Pedidos',
            href: route('orders.index'),
            icon: ShoppingBag,
        },
    ];

    // Admin: acceso completo
    if (user.value?.is_admin) {
        items.push(
            {
                title: 'Gestionar Comidas',
                href: route('foods.index'),
                icon: UtensilsCrossed,
            },
            {
                title: 'Categorías',
                href: route('categories.index'),
                icon: List,
            },
            {
                title: 'Gestionar Usuarios',
                href: route('users.index'),
                icon: Users,
            }
        );
    }

    // Vendedor: gestión de comidas y pedidos
    if (user.value?.is_vendedor && !user.value?.is_admin) {
        items.push(
            {
                title: 'Gestionar Comidas',
                href: route('seller.foods.index'),
                icon: Package,
            },
            {
                title: 'Categorías',
                href: route('seller.categories.index'),
                icon: List,
            }
        );
    }

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
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
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
