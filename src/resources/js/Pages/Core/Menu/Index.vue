<template>
    <AppLayout :breadcrumb="breadcrumb">
        <div class="menu-index">
            <div class="menu-index__header">
                <div>
                    <h1 class="menu-index__title">Menu do Sistema</h1>
                    <p class="menu-index__subtitle">Gerencie e reordene os itens de navegação.</p>
                </div>
                <Button
                    label="Novo Item"
                    icon="pi pi-plus"
                    @click="router.visit(route('core.menus.create'))"
                />
            </div>

            <Message v-if="flash?.error" severity="error" :closable="false" style="margin-bottom: 1rem;">
                {{ flash.error }}
            </Message>
            <Message v-if="flash?.success" severity="success" :closable="false" style="margin-bottom: 1rem;">
                {{ flash.success }}
            </Message>

            <div class="menu-index__hint">
                <i class="pi pi-info-circle" />
                Arraste os itens para reordenar. Itens filhos só podem estar um nível abaixo.
            </div>

            <MenuTree :arvore="arvore" @reordenar="salvarOrdem" />

            <div v-if="!arvore.length" class="menu-index__vazio">
                <i class="pi pi-list" style="font-size: 2rem; color: #9ca3af;" />
                <p>Nenhum item de menu cadastrado.</p>
                <Button
                    label="Criar primeiro item"
                    icon="pi pi-plus"
                    severity="secondary"
                    @click="router.visit(route('core.menus.create'))"
                />
            </div>
        </div>

        <Toast />
    </AppLayout>
</template>

<script setup>
import axios from 'axios'
import { usePage, router } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from 'primevue/button'
import Message from 'primevue/message'
import Toast from 'primevue/toast'
import MenuTree from './Components/MenuTree.vue'

const props = defineProps({
    arvore: { type: Array, default: () => [] },
})

const flash = usePage().props.flash
const toast = useToast()

const breadcrumb = [
    { label: 'Configurações', icon: 'pi pi-cog' },
    { label: 'Menu do Sistema' },
]

async function salvarOrdem(itens) {
    try {
        await axios.patch(route('core.menus.reordenar'), { itens })
        toast.add({ severity: 'success', summary: 'Menu reordenado.', life: 2000 })
    } catch {
        toast.add({ severity: 'error', summary: 'Erro ao reordenar o menu.', life: 3000 })
    }
}
</script>

<style scoped>
.menu-index {
    max-width: 800px;
}

.menu-index__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 1.25rem;
}

.menu-index__title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.25rem;
}

.menu-index__subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

.menu-index__hint {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8125rem;
    color: #6b7280;
    background: #f0fdfa;
    border: 1px solid #99f6e4;
    border-radius: 6px;
    padding: 0.625rem 0.875rem;
    margin-bottom: 1rem;
}

.menu-index__vazio {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}

.dark .menu-index__title { color: #f1f5f9; }
.dark .menu-index__hint { background: #134e4a; border-color: #0f766e; color: #99f6e4; }
</style>
