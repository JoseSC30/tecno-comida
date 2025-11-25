<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Loader2, Search, X } from 'lucide-vue-next';
import { route } from 'ziggy-js';

type SearchResults = {
    productos: Array<{
        id: number;
        name: string;
        price: number;
        category?: { id: number; name: string } | null;
    }>;
    categorias: Array<{ id: number; name: string; description?: string | null }>;
    pedidos: Array<{
        id: number;
        status: string;
        total: number;
        created_at?: string;
        user?: { id: number; name: string } | null;
    }>;
    usuarios: Array<{ id: number; name: string; email: string }>;
};

const emptyResults = (): SearchResults => ({
    productos: [],
    categorias: [],
    pedidos: [],
    usuarios: [],
});

const searchQuery = ref('');
const isLoading = ref(false);
const errorMessage = ref('');
const results = ref<SearchResults>(emptyResults());
const showDropdown = ref(false);
const containerRef = ref<HTMLElement | null>(null);
let debounceId: number | undefined;
let abortController: AbortController | null = null;

const buildSearchUrl = (term: string): string | null => {
    try {
        return route('search.global', { q: term }, true);
    } catch (error) {
        console.error('No se pudo construir la URL de búsqueda', error);
        return null;
    }
};

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const canSeeOrders = computed(() => Boolean(user.value?.is_admin || user.value?.is_vendedor));
const canSeeUsers = computed(() => Boolean(user.value?.is_admin));

const hasResults = computed(() =>
    results.value.productos.length > 0 ||
    results.value.categorias.length > 0 ||
    results.value.pedidos.length > 0 ||
    results.value.usuarios.length > 0,
);

const placeholder = computed(() => {
    if (canSeeUsers.value) {
        return 'Buscar pedidos, platos, clientes...';
    }
    if (canSeeOrders.value) {
        return 'Buscar pedidos o platos...';
    }
    return 'Buscar platos o categorías...';
});

const resetResults = () => {
    results.value = emptyResults();
    errorMessage.value = '';
};

const closeDropdown = () => {
    showDropdown.value = false;
};

const openDropdown = () => {
    showDropdown.value = true;
};

const performSearch = async (term: string) => {
    abortController?.abort();
    abortController = new AbortController();
    isLoading.value = true;
    errorMessage.value = '';

    try {
        const url = buildSearchUrl(term);
        if (!url) return;

        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            signal: abortController.signal,
        });

        if (!response.ok) {
            const message = await response.text();
            throw new Error(message || 'No se pudo realizar la búsqueda');
        }

        const data = (await response.json()) as Partial<SearchResults>;
        results.value = {
            ...emptyResults(),
            ...data,
        };
        openDropdown();
    } catch (error) {
        if ((error as DOMException).name === 'AbortError') {
            return;
        }
        console.error(error);
        errorMessage.value = error instanceof Error && error.message
            ? error.message
            : 'Hubo un problema al buscar. Intenta nuevamente.';
        results.value = emptyResults();
        openDropdown();
    } finally {
        isLoading.value = false;
    }
};

watch(searchQuery, value => {
    if (debounceId) {
        window.clearTimeout(debounceId);
    }

    const term = value.trim();

    if (term.length < 2) {
        resetResults();
        closeDropdown();
        return;
    }

    debounceId = window.setTimeout(() => {
        performSearch(term);
    }, 350);
});

const clearSearch = () => {
    searchQuery.value = '';
    resetResults();
    closeDropdown();
};

const formatDate = (value?: string | null): string => {
    if (!value) return 'Sin fecha';
    const parsed = new Date(value);
    return Number.isNaN(parsed.getTime()) ? 'Sin fecha' : parsed.toLocaleDateString();
};

const handleFocus = () => {
    if (hasResults.value) {
        openDropdown();
    }
};

const handleClickOutside = (event: MouseEvent) => {
    if (!containerRef.value) return;
    if (!containerRef.value.contains(event.target as Node)) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
    abortController?.abort();
});
</script>

<template>
    <div ref="containerRef" class="relative w-full max-w-xl">
        <div
            class="flex items-center gap-2 rounded-full border border-gray-200 bg-white/80 px-3 py-1.5 text-sm shadow-sm backdrop-blur transition focus-within:border-orange-500 focus-within:ring-2 focus-within:ring-orange-200 dark:border-gray-700 dark:bg-gray-900/70"
        >
            <Search class="h-4 w-4 text-gray-500" />
            <input
                v-model="searchQuery"
                type="search"
                :placeholder="placeholder"
                class="flex-1 bg-transparent text-sm text-gray-700 placeholder:text-gray-400 focus:outline-none dark:text-gray-100" 
                @focus="handleFocus"
            />
            <button
                v-if="searchQuery"
                type="button"
                class="rounded-full p-1 text-gray-400 transition hover:text-gray-600"
                @click="clearSearch"
            >
                <X class="h-4 w-4" />
            </button>
        </div>

        <div
            v-if="showDropdown"
            class="absolute right-0 z-50 mt-2 w-[32rem] rounded-xl border border-gray-200 bg-white/95 p-4 shadow-2xl backdrop-blur dark:border-gray-700 dark:bg-gray-900"
        >
            <div v-if="isLoading" class="flex items-center gap-2 text-sm text-gray-500">
                <Loader2 class="h-4 w-4 animate-spin" />
                Buscando información...
            </div>

            <div v-else-if="errorMessage" class="text-sm text-red-600">
                {{ errorMessage }}
            </div>

            <div v-else-if="hasResults" class="space-y-4">
                <section v-if="results.productos.length" class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-orange-600">Platos</p>
                    <ul class="space-y-1">
                        <li
                            v-for="producto in results.productos"
                            :key="`producto-${producto.id}`"
                            class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-orange-50/70 dark:hover:bg-orange-500/10"
                        >
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ producto.name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ producto.category?.name ?? 'Sin categoría' }}
                                </p>
                            </div>
                            <span class="text-sm font-bold text-orange-600">
                                Bs. {{ producto.price.toFixed(2) }}
                            </span>
                        </li>
                    </ul>
                </section>

                <section v-if="results.categorias.length" class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-sky-600">Categorías</p>
                    <ul class="space-y-1">
                        <li
                            v-for="categoria in results.categorias"
                            :key="`categoria-${categoria.id}`"
                            class="rounded-lg px-3 py-2 hover:bg-sky-50/70 dark:hover:bg-sky-500/10"
                        >
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ categoria.name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ categoria.description || 'Sin descripción' }}
                            </p>
                        </li>
                    </ul>
                </section>

                <section v-if="canSeeOrders && results.pedidos.length" class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-green-600">Pedidos</p>
                    <ul class="space-y-1">
                        <li
                            v-for="pedido in results.pedidos"
                            :key="`pedido-${pedido.id}`"
                            class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-green-50/70 dark:hover:bg-green-500/10"
                        >
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    Pedido #{{ pedido.id }} · {{ pedido.user?.name || 'Cliente' }}
                                </p>
                                <p class="text-xs capitalize text-gray-500 dark:text-gray-400">
                                    {{ pedido.status }} · {{ formatDate(pedido.created_at) }}
                                </p>
                            </div>
                            <span class="text-sm font-bold text-green-600">
                                Bs. {{ pedido.total.toFixed(2) }}
                            </span>
                        </li>
                    </ul>
                </section>

                <section v-if="canSeeUsers && results.usuarios.length" class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-purple-600">Clientes / Usuarios</p>
                    <ul class="space-y-1">
                        <li
                            v-for="persona in results.usuarios"
                            :key="`usuario-${persona.id}`"
                            class="rounded-lg px-3 py-2 hover:bg-purple-50/70 dark:hover:bg-purple-500/10"
                        >
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ persona.name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ persona.email }}
                            </p>
                        </li>
                    </ul>
                </section>
            </div>

            <p v-else class="text-sm text-gray-500">
                No se encontraron resultados para "{{ searchQuery }}".
            </p>
        </div>
    </div>
</template>
