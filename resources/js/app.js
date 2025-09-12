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
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .mount(el);
    },
    progress: {
        color: '#4F46E5',
    },
});
