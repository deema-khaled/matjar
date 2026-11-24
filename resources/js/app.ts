import '../css/app.css';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

createInertiaApp({
    title: (title) => (title ? `${title} · Matjar` : 'Matjar'),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#0f766e',
    },
});

// Keep <html lang/dir> in sync when the language is switched without a full reload.
router.on('navigate', (event) => {
    const props = event.detail.page.props as { locale?: string; dir?: string };
    if (props.locale) document.documentElement.lang = props.locale;
    if (props.dir) document.documentElement.dir = props.dir;
});
