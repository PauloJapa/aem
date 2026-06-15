<template>
    <AppLayout :breadcrumb="breadcrumb">
        <div class="page-container">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Perfis de Acesso</h1>
                    <p class="page-subtitle">Gerencie os perfis de usuários e suas permissões.</p>
                </div>
                <Button
                    label="Novo Perfil"
                    icon="pi pi-plus"
                    @click="router.visit(route('core.perfis.create'))"
                />
            </div>

            <Message v-if="flash?.error"   severity="error"   :closable="false" class="mb-4">{{ flash.error }}</Message>
            <Message v-if="flash?.success" severity="success" :closable="false" class="mb-4">{{ flash.success }}</Message>

            <DataTable :value="perfis" :rows="20" striped-rows>
                <Column field="name"  header="Nome técnico" />
                <Column field="label" header="Nome de exibição" />
                <Column header="Usuários" class="text-center">
                    <template #body="{ data }">
                        <Tag :value="data.users_count" severity="secondary" />
                    </template>
                </Column>
                <Column header="Ações" style="width: 160px;">
                    <template #body="{ data }">
                        <div style="display: flex; gap: 0.5rem;">
                            <Button
                                icon="pi pi-key"
                                severity="secondary"
                                size="small"
                                v-tooltip.top="'Permissões'"
                                @click="router.visit(route('core.perfis.permissoes', data.id))"
                            />
                            <Button
                                icon="pi pi-pencil"
                                severity="secondary"
                                size="small"
                                v-tooltip.top="'Editar'"
                                @click="router.visit(route('core.perfis.edit', data.id))"
                            />
                            <Button
                                icon="pi pi-trash"
                                severity="danger"
                                size="small"
                                v-tooltip.top="'Excluir'"
                                :disabled="data.name === 'admin' || data.users_count > 0"
                                @click="confirmarExclusao(data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <ConfirmDialog />
        </div>
    </AppLayout>
</template>

<script setup>
import { usePage, router } from '@inertiajs/vue3'
import { useConfirm } from 'primevue/useconfirm'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from 'primevue/button'
import Message from 'primevue/message'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import ConfirmDialog from 'primevue/confirmdialog'

defineProps({
    perfis: { type: Array, default: () => [] },
})

const flash   = usePage().props.flash
const confirm = useConfirm()

const breadcrumb = [
    { label: 'Administração', icon: 'pi pi-cog' },
    { label: 'Perfis de Acesso' },
]

function confirmarExclusao(perfil) {
    confirm.require({
        message: `Excluir o perfil "${perfil.label}"? Esta ação não pode ser desfeita.`,
        header:  'Confirmar exclusão',
        icon:    'pi pi-exclamation-triangle',
        rejectLabel: 'Cancelar',
        acceptLabel: 'Excluir',
        acceptClass: 'p-button-danger',
        accept: () => router.delete(route('core.perfis.destroy', perfil.id)),
    })
}
</script>

<style scoped>
.page-container { max-width: 900px; }

.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 1.25rem;
}

.page-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.25rem;
}

.page-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

.mb-4 { margin-bottom: 1rem; }

.dark .page-title { color: #f1f5f9; }
</style>
