import './bootstrap'
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { ZiggyVue } from 'ziggy-js'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import ConfirmationService from 'primevue/confirmationservice'
import DialogService from 'primevue/dialogservice'
import Tooltip from 'primevue/tooltip'
import 'primeicons/primeicons.css'
import ErpPreset from './theme/erp-preset'

const pinia = createPinia()

const ptBR = {
    dayNames: ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'],
    dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
    dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
    monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
    monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
    today: 'Hoje',
    clear: 'Limpar',
    dateFormat: 'dd/mm/yy',
    firstDayOfWeek: 0,
    weak: 'Fraco',
    medium: 'Médio',
    strong: 'Forte',
    passwordPrompt: 'Digite uma senha',
    emptyMessage: 'Nenhum resultado encontrado',
    emptyFilterMessage: 'Nenhum resultado encontrado',
    accept: 'Sim',
    reject: 'Não',
    choose: 'Escolher',
    upload: 'Enviar',
    cancel: 'Cancelar',
    fileSizeTypes: ['B', 'KB', 'MB', 'GB', 'TB'],
}

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
            .use(PrimeVue, {
                theme: {
                    preset: ErpPreset,
                    options: {
                        darkModeSelector: '.dark',
                        cssLayer: false,
                    },
                },
                locale: ptBR,
                ripple: true,
            })
            .use(ToastService)
            .use(ConfirmationService)
            .use(DialogService)
            .directive('tooltip', Tooltip)
            .mount(el)
    },

    progress: {
        color: '#14b8a6',
    },
})
