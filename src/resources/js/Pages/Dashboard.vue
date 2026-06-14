<template>
    <div>
        <h1 style="font-size:1.25rem; font-weight:700; margin-bottom:1.5rem;">Dashboard</h1>

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:1rem;">
            <Card v-for="card in cards" :key="card.label">
                <template #content>
                    <div style="display:flex; align-items:center; gap:1rem;">
                        <div :style="{ background: card.cor, borderRadius: '0.5rem', padding: '0.75rem', flexShrink: 0 }">
                            <i :class="card.icon" style="font-size:1.25rem; color:#fff;" />
                        </div>
                        <div>
                            <div style="font-size:0.75rem; color:var(--p-surface-500); margin-bottom:0.25rem;">{{ card.label }}</div>
                            <div style="font-size:1.5rem; font-weight:700;">{{ card.valor }}</div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from 'primevue/card'

defineOptions({
    layout: (h, page) =>
        h(AppLayout, { breadcrumb: [{ label: 'Dashboard', icon: 'pi pi-home' }] }, () => page),
})

const cards = [
    { label: 'Vendas do mês',      valor: 'R$ 84.320',  icon: 'pi pi-shopping-cart',    cor: '#0d9488' },
    { label: 'Contas a pagar',     valor: 'R$ 12.450',  icon: 'pi pi-arrow-up-right',    cor: '#ef4444' },
    { label: 'Contas a receber',   valor: 'R$ 31.800',  icon: 'pi pi-arrow-down-left',   cor: '#22c55e' },
    { label: 'Estoque baixo',      valor: '7 itens',    icon: 'pi pi-warehouse',          cor: '#f59e0b' },
]
</script>
