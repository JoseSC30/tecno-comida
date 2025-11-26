<script setup lang="ts">
import { useTheme, type ThemeVariant, type ColorMode, type FontSize } from '@/composables/useTheme';
import { Button } from '@/components/ui/button';
import { Palette, Sun, Moon, Monitor, Settings, Type, Contrast } from 'lucide-vue-next';
import { ref } from 'vue';

const { 
    currentVariant, 
    currentMode,
    currentFontSize,
    highContrast,
    effectiveMode, 
    setVariant, 
    setMode,
    setFontSize,
    toggleHighContrast,
    getVariantLabel, 
    getModeLabel,
    getFontSizeLabel
} = useTheme();

const isThemeMenuOpen = ref(false);
const isModeMenuOpen = ref(false);
const isAccessibilityMenuOpen = ref(false);

const themeVariants: ThemeVariant[] = ['kids', 'youth', 'adults'];
const colorModes: ColorMode[] = ['light', 'dark', 'auto'];
const fontSizes: FontSize[] = ['small', 'normal', 'large', 'extra-large'];

const getModeIcon = (mode: ColorMode) => {
    switch (mode) {
        case 'light': return Sun;
        case 'dark': return Moon;
        case 'auto': return Monitor;
    }
};

const getVariantDescription = (variant: ThemeVariant): string => {
    const descriptions: Record<ThemeVariant, string> = {
        kids: 'Colores vibrantes y divertidos',
        youth: 'Estilo moderno y energético',
        adults: 'Diseño profesional y elegante'
    };
    return descriptions[variant];
};

const selectVariant = (variant: ThemeVariant) => {
    setVariant(variant);
    isThemeMenuOpen.value = false;
};

const selectMode = (mode: ColorMode) => {
    setMode(mode);
    isModeMenuOpen.value = false;
};

const selectFontSize = (size: FontSize) => {
    setFontSize(size);
};
</script>

<template>
    <div class="flex items-center gap-2">
        <!-- Selector de Modo (Día/Noche) -->
        <div class="relative">
            <Button
                variant="outline"
                size="sm"
                @click="isModeMenuOpen = !isModeMenuOpen"
                class="gap-2"
            >
                <component :is="getModeIcon(currentMode)" class="h-4 w-4" />
                <span class="hidden sm:inline">{{ getModeLabel(currentMode) }}</span>
            </Button>
            
            <!-- Dropdown de Modo -->
            <Transition
                enter-active-class="transition duration-100 ease-out"
                enter-from-class="transform scale-95 opacity-0"
                enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in"
                leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0"
            >
                <div
                    v-if="isModeMenuOpen"
                    class="absolute right-0 top-full z-50 mt-2 w-48 rounded-lg border border-border bg-background p-1 shadow-lg"
                    @click.stop
                >
                    <button
                        v-for="mode in colorModes"
                        :key="mode"
                        @click="selectMode(mode)"
                        :class="[
                            'flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors',
                            currentMode === mode
                                ? 'bg-accent text-accent-foreground font-medium'
                                : 'hover:bg-accent/50 text-popover-foreground'
                        ]"
                    >
                        <component :is="getModeIcon(mode)" class="h-4 w-4" />
                        <span>{{ getModeLabel(mode) }}</span>
                    </button>
                </div>
            </Transition>
        </div>

        <!-- Selector de Tema -->
        <div class="relative">
            <Button
                variant="outline"
                size="sm"
                @click="isThemeMenuOpen = !isThemeMenuOpen"
                class="gap-2"
            >
                <Palette class="h-4 w-4" />
                <span class="hidden sm:inline">{{ getVariantLabel(currentVariant) }}</span>
            </Button>
            
            <!-- Dropdown de Tema -->
            <Transition
                enter-active-class="transition duration-100 ease-out"
                enter-from-class="transform scale-95 opacity-0"
                enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in"
                leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0"
            >
                <div
                    v-if="isThemeMenuOpen"
                    class="absolute right-0 top-full z-50 mt-2 w-64 rounded-lg border border-border bg-background p-2 shadow-lg"
                    @click.stop
                >
                    <div class="mb-2 px-2 py-1">
                        <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                            Seleccionar Tema
                        </p>
                    </div>
                    <button
                        v-for="variant in themeVariants"
                        :key="variant"
                        @click="selectVariant(variant)"
                        :class="[
                            'flex w-full flex-col items-start gap-1 rounded-md px-3 py-2.5 text-left transition-colors',
                            currentVariant === variant
                                ? 'bg-accent text-accent-foreground'
                                : 'hover:bg-accent/50 text-popover-foreground'
                        ]"
                    >
                        <div class="flex items-center justify-between w-full">
                            <span class="font-medium">{{ getVariantLabel(variant) }}</span>
                            <div
                                v-if="currentVariant === variant"
                                class="h-2 w-2 rounded-full bg-primary"
                            />
                        </div>
                        <span class="text-xs text-muted-foreground">
                            {{ getVariantDescription(variant) }}
                        </span>
                    </button>
                </div>
            </Transition>
        </div>

        <!-- Selector de Accesibilidad -->
        <div class="relative">
            <Button
                variant="outline"
                size="sm"
                @click="isAccessibilityMenuOpen = !isAccessibilityMenuOpen"
                class="gap-2"
            >
                <Settings class="h-4 w-4" />
                <span class="hidden sm:inline">Accesibilidad</span>
            </Button>
            
            <!-- Dropdown de Accesibilidad -->
            <Transition
                enter-active-class="transition duration-100 ease-out"
                enter-from-class="transform scale-95 opacity-0"
                enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in"
                leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0"
            >
                <div
                    v-if="isAccessibilityMenuOpen"
                    class="absolute right-0 top-full z-50 mt-2 w-64 rounded-lg border border-border bg-background p-3 shadow-lg"
                    @click.stop
                >
                    <!-- Tamaño de Fuente -->
                    <div class="mb-4">
                        <div class="mb-2 flex items-center gap-2">
                            <Type class="h-4 w-4 text-muted-foreground" />
                            <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                                Tamaño de Letra
                            </p>
                        </div>
                        <div class="space-y-1">
                            <button
                                v-for="size in fontSizes"
                                :key="size"
                                @click="selectFontSize(size)"
                                :class="[
                                    'flex w-full items-center justify-between rounded-md px-3 py-2 text-sm transition-colors',
                                    currentFontSize === size
                                        ? 'bg-accent text-accent-foreground font-medium'
                                        : 'hover:bg-accent/50 text-popover-foreground'
                                ]"
                            >
                                <span>{{ getFontSizeLabel(size) }}</span>
                                <div
                                    v-if="currentFontSize === size"
                                    class="h-2 w-2 rounded-full bg-primary"
                                />
                            </button>
                        </div>
                    </div>

                    <!-- Contraste Alto -->
                    <div class="border-t border-border pt-3">
                        <button
                            @click="toggleHighContrast"
                            :class="[
                                'flex w-full items-center justify-between rounded-md px-3 py-2.5 text-sm transition-colors',
                                highContrast
                                    ? 'bg-accent text-accent-foreground font-medium'
                                    : 'hover:bg-accent/50 text-popover-foreground'
                            ]"
                        >
                            <div class="flex items-center gap-2">
                                <Contrast class="h-4 w-4" />
                                <span>Contraste Alto</span>
                            </div>
                            <div
                                v-if="highContrast"
                                class="h-2 w-2 rounded-full bg-primary"
                            />
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </div>

    <!-- Overlay para cerrar al hacer clic fuera -->
    <Teleport to="body">
        <div
            v-if="isThemeMenuOpen || isModeMenuOpen || isAccessibilityMenuOpen"
            class="fixed inset-0 z-40"
            @click="isThemeMenuOpen = false; isModeMenuOpen = false; isAccessibilityMenuOpen = false"
        />
    </Teleport>
</template>
