import "./bootstrap";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { ZiggyVue } from "ziggy-js";

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.vue");
        const page = pages[`./Pages/${name}.vue`];

        if (!page) {
            throw new Error(`Page not found: ./Pages/${name}.vue`);
        }

        return page().then((m) => m.default);
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, props.ziggy)
            .mount(el);
    },
});
