import { ref, computed } from 'vue';

export interface CartItem {
    food_id: number;
    name: string;
    price: number;
    quantity: number;
    image?: string;
}

const cartItems = ref<CartItem[]>([]);
const isCartOpen = ref(false);

// Cargar carrito desde localStorage
const loadCart = () => {
    try {
        const saved = localStorage.getItem('cart');
        if (saved) {
            cartItems.value = JSON.parse(saved);
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

export function useCart() {
    // Cargar al iniciar
    if (cartItems.value.length === 0) {
        loadCart();
    }

    const addToCart = (item: Omit<CartItem, 'quantity'>) => {
        const existingItem = cartItems.value.find(i => i.food_id === item.food_id);
        
        if (existingItem) {
            existingItem.quantity++;
        } else {
            cartItems.value.push({
                ...item,
                quantity: 1,
            });
        }
        
        saveCart();
    };

    const removeFromCart = (foodId: number) => {
        cartItems.value = cartItems.value.filter(item => item.food_id !== foodId);
        saveCart();
    };

    const updateQuantity = (foodId: number, quantity: number) => {
        const item = cartItems.value.find(i => i.food_id === foodId);
        if (item) {
            if (quantity <= 0) {
                removeFromCart(foodId);
            } else {
                item.quantity = quantity;
                saveCart();
            }
        }
    };

    const incrementQuantity = (foodId: number) => {
        const item = cartItems.value.find(i => i.food_id === foodId);
        if (item) {
            item.quantity++;
            saveCart();
        }
    };

    const decrementQuantity = (foodId: number) => {
        const item = cartItems.value.find(i => i.food_id === foodId);
        if (item) {
            if (item.quantity > 1) {
                item.quantity--;
                saveCart();
            } else {
                removeFromCart(foodId);
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
