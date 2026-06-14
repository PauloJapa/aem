import './bootstrap'
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from 'ziggy-js'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import ConfirmationService from 'primevue/confirmationservice'
import 'primeicons/primeicons.css'

const pinia = createPinia()

createInertiaApp({
    title: (title) => `${title} — ERP`,

    resolve: (name) => {
        const globalPages = import.meta.glob('./Pages/**/*.vue')
        const modulePages = import.meta.glob('../../Modules/*/resources/js/Pages/**/*.vue')

        const globalKey = `./Pages/${name}.vue`
        if (globalKey in globalPages) return globalPages[globalKey]()

        const [module, ...rest] = name.split('/')
        const moduleKey = `../../Modules/${module}/resources/js/Pages/${rest.join('/')}.vue`
        if (moduleKey in modulePages) return modulePages[moduleKey]()

        throw new Error(`Página Inertia não encontrada: "${name}"`)
    },

    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(pinia)
            .use(PrimeVue, { ripple: true })
            .use(ToastService)
            .use(ConfirmationService)
            .mount(el)
    },

    progress: {
        color: '#4F46E5',
    },
})