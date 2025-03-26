import './bootstrap';
import '../css/app.css';
// import 'primevue/resources/themes/lara-light-green/theme.css'
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import PrimeVue from "primevue/config";
// import Lara from '@primevue/themes/lara';
// import Nora from '@primevue/themes/nora';
import Aura from '@primevue/themes/aura';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import DatePicker from 'primevue/datepicker';
import AutoComplete from 'primevue/autocomplete';
import Toast from 'primevue/toast';
import ToastService from 'primevue/toastservice';
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button';
import Card from 'primevue/card';
import ScrollPanel from 'primevue/scrollpanel';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import ConfirmDialog from 'primevue/confirmdialog';
import ConfirmationService from 'primevue/confirmationservice';

import 'primeicons/primeicons.css'
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            // .use(PrimeVue)
            .use(PrimeVue, {
                theme: {
                    preset: Aura
                }
            })
            .use(ToastService)
            .use(ConfirmationService)
            .component('Button', Button)
            .component('InputText', InputText)
            .component('Textarea', Textarea)
            .component('AutoComplete', AutoComplete)
            .component('DatePicker', DatePicker)
            .component('DataTable', DataTable)
            .component('Card', Card)
            .component('Tag', Tag)
            .component('Select', Select)
            .component('Tabs', Tabs)
            .component('Tab', Tab)
            .component('TabList', TabList)
            .component('TabPanels', TabPanels)
            .component('TabPanel', TabPanel)
            .component('ScrollPanel', ScrollPanel)
            .component('ConfirmDialog', ConfirmDialog)
            .component('Toast', Toast)
            .component('Column', Column)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
