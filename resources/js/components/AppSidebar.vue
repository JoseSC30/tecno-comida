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
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, ShoppingBag, UtensilsCrossed, Users, Package, List } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const mainNavItems = computed((): NavItem[] => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Menú',
            href: '/menu',
            icon: UtensilsCrossed,
        },
        {
            title: 'Mis Pedidos',
            href: '/orders',
            icon: ShoppingBag,
        },
    ];

    // Admin: acceso completo
    if (user.value?.is_admin) {
        items.push(
            {
                title: 'Gestionar Comidas',
                href: '/admin/foods',
                icon: UtensilsCrossed,
            },
            {
                title: 'Categorías',
                href: '/admin/categories',
                icon: List,
            },
            {
                title: 'Gestionar Usuarios',
                href: '/admin/users',
                icon: Users,
            }
        );
    }

    // Vendedor: gestión de comidas y pedidos
    if (user.value?.is_vendedor && !user.value?.is_admin) {
        items.push(
            {
                title: 'Gestionar Comidas',
                href: '/seller/foods',
                icon: Package,
            },
            {
                title: 'Categorías',
                href: '/seller/categories',
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
                        <Link :href="dashboard()">
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
