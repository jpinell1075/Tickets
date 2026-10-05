import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createHead } from '@vueuse/head';
import { ZiggyVue } from 'ziggy-js';

const pages = import.meta.glob('./Pages/**/*.vue');

createInertiaApp({
    resolve: name => {
        const path = `./Pages/${name.replace(/\./g, '/')}.vue`;
        const importPage = pages[path];
        if (!importPage) {
            throw new Error(`Unknown page: ${name}`);
        }
        return importPage();
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        const head = createHead();

        app.use(plugin)
            .use(head)
            .use(ZiggyVue)
            .mount(el);
    },
});