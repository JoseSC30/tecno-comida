import { ref, computed } from 'vue';

export type CartItemType = 'product' | 'combo';

export interface CartItem {
    key: string;
    food_id: number | null;
    name: string;
    price: number;
    quantity: number;
    image?: string;
    type: CartItemType;
    comboId?: number | null;
    discount?: number | null;
    originalPrice?: number | null;
    products?: Array<{ id: number; name: string; price: number; quantity?: number; image?: string }>;
    components?: Array<{ product_id: number; quantity: number }>;
}

type CartItemInput = Omit<CartItem, 'quantity' | 'key' | 'type'> & Partial<Pick<CartItem, 'type' | 'key'>>;

const cartItems = ref<CartItem[]>([]);
const isCartOpen = ref(false);

const buildKey = (item: Partial<CartItemInput>, index?: number) => {
    const type = (item.type as CartItemType) || 'product';
    const idPart = item.food_id ?? item.comboId ?? index ?? Date.now();
    return `${type}-${idPart}`;
};

const normalizeItem = (item: any, index: number): CartItem => {
    return {
        key: item.key ?? buildKey(item, index),
        food_id: item.food_id ?? null,
        name: item.name ?? '',
        price: Number(item.price ?? 0),
        quantity: item.quantity && item.quantity > 0 ? item.quantity : 1,
        image: item.image,
        type: (item.type as CartItemType) || 'product',
        comboId: item.comboId ?? item.combo_id ?? null,
        discount: item.discount ?? null,
        originalPrice: item.originalPrice ?? item.original_price ?? null,
        products: item.products ?? [],
        components: item.components ?? [],
    };
};

// Cargar carrito desde localStorage
const loadCart = () => {
    try {
        const saved = localStorage.getItem('cart');
        if (saved) {
            const parsed = JSON.parse(saved);
            cartItems.value = Array.isArray(parsed)
                ? parsed.map((item, index) => normalizeItem(item, index))
                : [];
        }
    } catch (error) {
        console.error('Error loading cart:', error);
    }
};

// Guardar carrito en localStorage
const saveCart = () => {
    try {
        localStorage.setItem('cart', JSON.stringify(cartItems.value));
    } catch (error) {
        console.error('Error saving cart:', error);
    }
};

const resolveKey = (keyOrId: string | number) => {
    const existing = cartItems.value.find(item => item.key === keyOrId);
    if (existing) return existing.key;
    const byId = cartItems.value.find(item => item.food_id === keyOrId);
    return byId ? byId.key : String(keyOrId);
};

export function useCart() {
    // Cargar al iniciar
    if (cartItems.value.length === 0) {
        loadCart();
    }

    const addToCart = (item: CartItemInput) => {
        const key = item.key ?? buildKey(item);
        const existingItem = cartItems.value.find(i => i.key === key);

        if (existingItem) {
            existingItem.quantity++;
        } else {
            cartItems.value.push({
                key,
                food_id: item.food_id ?? null,
                name: item.name,
                price: Number(item.price),
                quantity: 1,
                image: item.image,
                type: item.type ?? 'product',
                comboId: item.comboId ?? null,
                discount: item.discount ?? null,
                originalPrice: item.originalPrice ?? null,
                products: item.products ?? [],
                components: item.components ?? [],
            });
        }

        saveCart();
    };

    const removeFromCart = (keyOrId: string | number) => {
        const key = resolveKey(keyOrId);
        cartItems.value = cartItems.value.filter(item => item.key !== key);
        saveCart();
    };

    const updateQuantity = (keyOrId: string | number, quantity: number) => {
        const key = resolveKey(keyOrId);
        const item = cartItems.value.find(i => i.key === key);
        if (item) {
            if (quantity <= 0) {
                removeFromCart(key);
            } else {
                item.quantity = quantity;
                saveCart();
            }
        }
    };

    const incrementQuantity = (keyOrId: string | number) => {
        const key = resolveKey(keyOrId);
        const item = cartItems.value.find(i => i.key === key);
        if (item) {
            item.quantity++;
            saveCart();
        }
    };

    const decrementQuantity = (keyOrId: string | number) => {
        const key = resolveKey(keyOrId);
        const item = cartItems.value.find(i => i.key === key);
        if (item) {
            if (item.quantity > 1) {
                item.quantity--;
                saveCart();
            } else {
                removeFromCart(key);
            }
        }
    };

    const clearCart = () => {
        cartItems.value = [];
        saveCart();
    };

    const toggleCart = () => {
        isCartOpen.value = !isCartOpen.value;
    };

    const openCart = () => {
        isCartOpen.value = true;
    };

    const closeCart = () => {
        isCartOpen.value = false;
    };

    const total = computed(() => {
        return cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    });

    const itemCount = computed(() => {
        return cartItems.value.reduce((sum, item) => sum + item.quantity, 0);
    });

    const hasItems = computed(() => cartItems.value.length > 0);

    return {
        cartItems,
        isCartOpen,
        addToCart,
        removeFromCart,
        updateQuantity,
        incrementQuantity,
        decrementQuantity,
        clearCart,
        toggleCart,
        openCart,
        closeCart,
        total,
        itemCount,
        hasItems,
    };
}
