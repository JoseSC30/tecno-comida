import { ref, computed, watch, onMounted } from 'vue';

export type ThemeVariant = 'kids' | 'youth' | 'adults';
export type ColorMode = 'light' | 'dark' | 'auto';
export type FontSize = 'small' | 'normal' | 'large' | 'extra-large';

interface Theme {
    variant: ThemeVariant;
    mode: ColorMode;
    fontSize: FontSize;
    highContrast: boolean;
}

const STORAGE_KEY = 'app-theme';
const MODE_KEY = 'color-mode';
const FONT_SIZE_KEY = 'font-size';
const HIGH_CONTRAST_KEY = 'high-contrast';

// Estado reactivo del tema
const currentVariant = ref<ThemeVariant>('adults');
const currentMode = ref<ColorMode>('auto');
const currentFontSize = ref<FontSize>('normal');
const highContrast = ref<boolean>(false);

// Detectar modo del sistema
const getSystemMode = (): 'light' | 'dark' => {
    if (typeof window === 'undefined') return 'light';
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
};

// Modo efectivo (considerando auto)
const effectiveMode = computed(() => {
    if (currentMode.value === 'auto') {
        return getSystemMode();
    }
    return currentMode.value;
});

// Aplicar tema al DOM
const applyTheme = () => {
    const html = document.documentElement;
    
    // Remover clases anteriores
    html.classList.remove('theme-kids', 'theme-youth', 'theme-adults');
    html.classList.remove('dark', 'light');
    html.classList.remove('font-size-small', 'font-size-normal', 'font-size-large', 'font-size-extra-large');
    html.classList.remove('high-contrast');
    
    // Aplicar variante de tema
    html.classList.add(`theme-${currentVariant.value}`);
    
    // Aplicar modo de color
    if (effectiveMode.value === 'dark') {
        html.classList.add('dark');
    } else {
        html.classList.add('light');
    }
    
    // Aplicar tamaño de fuente
    html.classList.add(`font-size-${currentFontSize.value}`);
    
    // Aplicar contraste alto
    if (highContrast.value) {
        html.classList.add('high-contrast');
    }
    
    // Guardar en localStorage
    localStorage.setItem(STORAGE_KEY, currentVariant.value);
    localStorage.setItem(MODE_KEY, currentMode.value);
    localStorage.setItem(FONT_SIZE_KEY, currentFontSize.value);
    localStorage.setItem(HIGH_CONTRAST_KEY, String(highContrast.value));
};

// Cargar tema desde localStorage
const loadTheme = () => {
    const savedVariant = localStorage.getItem(STORAGE_KEY) as ThemeVariant | null;
    const savedMode = localStorage.getItem(MODE_KEY) as ColorMode | null;
    const savedFontSize = localStorage.getItem(FONT_SIZE_KEY) as FontSize | null;
    const savedHighContrast = localStorage.getItem(HIGH_CONTRAST_KEY);
    
    if (savedVariant && ['kids', 'youth', 'adults'].includes(savedVariant)) {
        currentVariant.value = savedVariant;
    }
    
    if (savedMode && ['light', 'dark', 'auto'].includes(savedMode)) {
        currentMode.value = savedMode;
    }
    
    if (savedFontSize && ['small', 'normal', 'large', 'extra-large'].includes(savedFontSize)) {
        currentFontSize.value = savedFontSize;
    }
    
    if (savedHighContrast !== null) {
        highContrast.value = savedHighContrast === 'true';
    }
    
    applyTheme();
};

// Escuchar cambios del sistema
const setupSystemModeListener = () => {
    if (typeof window === 'undefined') return;
    
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    const handler = () => {
        if (currentMode.value === 'auto') {
            applyTheme();
        }
    };
    
    mediaQuery.addEventListener('change', handler);
    
    return () => mediaQuery.removeEventListener('change', handler);
};

export const useTheme = () => {
    onMounted(() => {
        loadTheme();
        const cleanup = setupSystemModeListener();
        
        return () => {
            if (cleanup) cleanup();
        };
    });
    
    // Watch para cambios reactivos
    watch([currentVariant, currentMode, currentFontSize, highContrast], () => {
        applyTheme();
    });
    
    const setVariant = (variant: ThemeVariant) => {
        currentVariant.value = variant;
    };
    
    const setMode = (mode: ColorMode) => {
        currentMode.value = mode;
    };
    
    const setFontSize = (size: FontSize) => {
        currentFontSize.value = size;
    };
    
    const toggleHighContrast = () => {
        highContrast.value = !highContrast.value;
    };
    
    const toggleMode = () => {
        if (currentMode.value === 'light') {
            currentMode.value = 'dark';
        } else if (currentMode.value === 'dark') {
            currentMode.value = 'auto';
        } else {
            currentMode.value = 'light';
        }
    };
    
    const getVariantLabel = (variant: ThemeVariant): string => {
        const labels: Record<ThemeVariant, string> = {
            kids: 'Infantil',
            youth: 'Juvenil',
            adults: 'Adultos'
        };
        return labels[variant];
    };
    
    const getModeLabel = (mode: ColorMode): string => {
        const labels: Record<ColorMode, string> = {
            light: 'Claro',
            dark: 'Oscuro',
            auto: 'Automático'
        };
        return labels[mode];
    };
    
    const getFontSizeLabel = (size: FontSize): string => {
        const labels: Record<FontSize, string> = {
            small: 'Pequeña',
            normal: 'Normal',
            large: 'Grande',
            'extra-large': 'Muy Grande'
        };
        return labels[size];
    };
    
    return {
        currentVariant: computed(() => currentVariant.value),
        currentMode: computed(() => currentMode.value),
        currentFontSize: computed(() => currentFontSize.value),
        highContrast: computed(() => highContrast.value),
        effectiveMode,
        setVariant,
        setMode,
        setFontSize,
        toggleHighContrast,
        toggleMode,
        getVariantLabel,
        getModeLabel,
        getFontSizeLabel,
        applyTheme
    };
};
