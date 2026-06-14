import { definePreset } from '@primevue/themes'
import Aura from '@primevue/themes/aura'

const teal = {
    50:  '#f0fdfa',
    100: '#ccfbf1',
    200: '#99f6e4',
    300: '#5eead4',
    400: '#2dd4bf',
    500: '#14b8a6',
    600: '#0d9488',
    700: '#0f766e',
    800: '#115e59',
    900: '#134e4a',
    950: '#042f2e',
}

const slate = {
    0:   '#ffffff',
    50:  '#f8fafc',
    100: '#f1f5f9',
    200: '#e2e8f0',
    300: '#cbd5e1',
    400: '#94a3b8',
    500: '#64748b',
    600: '#475569',
    700: '#334155',
    800: '#1e293b',
    900: '#0f172a',
    950: '#020617',
}

const ErpPreset = definePreset(Aura, {
    primitive: {
        teal,
    },
    semantic: {
        primary: {
            50:  '{teal.50}',
            100: '{teal.100}',
            200: '{teal.200}',
            300: '{teal.300}',
            400: '{teal.400}',
            500: '{teal.500}',
            600: '{teal.600}',
            700: '{teal.700}',
            800: '{teal.800}',
            900: '{teal.900}',
            950: '{teal.950}',
        },
        colorScheme: {
            light: {
                primary: {
                    color:           '{teal.600}',
                    contrastColor:   '#ffffff',
                    hoverColor:      '{teal.700}',
                    activeColor:     '{teal.800}',
                },
                surface: {
                    0:   slate[0],
                    50:  slate[50],
                    100: slate[100],
                    200: slate[200],
                    300: slate[300],
                    400: slate[400],
                    500: slate[500],
                    600: slate[600],
                    700: slate[700],
                    800: slate[800],
                    900: slate[900],
                    950: slate[950],
                },
            },
            dark: {
                primary: {
                    color:           '{teal.400}',
                    contrastColor:   '{teal.950}',
                    hoverColor:      '{teal.300}',
                    activeColor:     '{teal.200}',
                },
                surface: {
                    0:   slate[0],
                    50:  slate[950],
                    100: slate[900],
                    200: slate[800],
                    300: slate[700],
                    400: slate[600],
                    500: slate[500],
                    600: slate[400],
                    700: slate[300],
                    800: slate[200],
                    900: slate[100],
                    950: slate[50],
                },
            },
        },
    },
    components: {
        button: {
            borderRadius: '0.375rem',
        },
        inputtext: {
            borderRadius: '0.375rem',
        },
        card: {
            borderRadius: '0.375rem',
        },
        badge: {
            borderRadius: '0.375rem',
        },
    },
})

export default ErpPreset
