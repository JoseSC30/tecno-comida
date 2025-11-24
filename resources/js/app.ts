import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Inicializar tema antes de montar la aplicación
const initializeTheme = () => {
    const savedVariant = localStorage.getItem('app-theme') || 'adults';
    const savedMode = localStorage.getItem('color-mode') || 'auto';
    const savedFontSize = localStorage.getItem('font-size') || 'normal';
    const savedHighContrast = localStorage.getItem('high-contrast');
    
    const html = document.documentElement;
    html.classList.add(`theme-${savedVariant}`);
    html.classList.add(`font-size-${savedFontSize}`);
    
    if (savedHighContrast === 'true') {
        html.classList.add('high-contrast');
    }
    
    if (savedMode === 'auto') {
        const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        html.classList.add(isDark ? 'dark' : 'light');
    } else {
        html.classList.add(savedMode);
    }
};

// Inicializar tema inmediatamente
initializeTheme();

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
