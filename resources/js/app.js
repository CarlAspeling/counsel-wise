import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { Ziggy } from './ziggy.js';

// Import route function and configure it with our Ziggy data
import { route as ziggyRoute } from 'ziggy-js';

// Create route function with our Ziggy configuration
const route = (name, params, absolute) => {
    return ziggyRoute(name, params, absolute, Ziggy);
};

// Make route function globally available  
window.route = route;
globalThis.route = route;

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        // Apply theme on initial load and when it changes
        const applyTheme = (theme) => {
            const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        };

        // Apply theme on initial load
        applyTheme(props.initialPage.props.theme);

        // Listen for system theme changes if using 'system' preference
        if (props.initialPage.props.theme === 'system') {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                applyTheme('system');
            });
        }

        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy);

        // Watch for theme changes from Inertia
        app.mixin({
            watch: {
                '$page.props.theme': {
                    handler(newTheme) {
                        if (newTheme) {
                            applyTheme(newTheme);
                        }
                    },
                    immediate: false,
                },
            },
        });

        return app.mount(el);
    },
    progress: {
        color: '#4F46E5',
    },
});
