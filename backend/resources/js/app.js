import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

// Import styles and scripts directly from the vue-app directory
import '../../../vue-app/src/assets/styles/styles.css';
import '../../../vue-app/src/assets/styles/pm-custom.css';
import '../../../vue-app/src/assets/styles/main.css';
import '../../../vue-app/src/assets/styles/theme.css';
import '../../../vue-app/src/assets/styles/finance.css';
import '../../../vue-app/src/assets/js/preline.js';

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('../../../vue-app/src/pages/**/*.vue', { eager: true });
        return pages[`../../../vue-app/src/pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el)
    },
});
