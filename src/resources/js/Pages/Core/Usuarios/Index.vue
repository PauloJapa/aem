<template>
    <AppLayout :breadcrumb="breadcrumb">
        <div class="page-container">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Usuários</h1>
                    <p class="page-subtitle">Gerencie os usuários do sistema.</p>
                </div>
                <Button
                    label="Novo Usuário"
                    icon="pi pi-plus"
                    @click="router.visit(route('core.usuarios.create'))"
                />
            </div>

            <Message v-if="flash?.error"   severity="error"   :closable="false" class="mb-4">{{ flash.error }}</Message>
            <Message v-if="flash?.success" severity="success" :closable="false" class="mb-4">{{ flash.success }}</Message>

            <!-- Filtros -->
            <div class="filtros">
                <InputText
                    v-model="filtros.busca"
                    placeholder="Buscar por nome ou e-mail..."
                    class="filtros__busca"
                    @keyup.enter="aplicarFiltros"
                />
                <Select
                    v-model="filtros.perfil"
                    :options="perfis"
                    option-label="label"
                    option-value="name"
                    placeholder="Todos os perfis"
                    :show-clear="true"
                    class="filtros__select"
                />
                <Select
                    v-model="filtros.ativo"
                    :options="opcoesAtivo"
                    option-label="label"
                    option-value="value"
                    placeholder="Todos os status"
                    :show-clear="true"
                    class="filtros__select"
                />
                <Button label="Filtrar" icon="pi pi-search" @click="aplicarFiltros" />
            </div>

            <DataTable :value="usuarios.data" striped-rows>
                <Column field="name"  header="Nome" />
                <Column field="email" header="E-mail" />
                <Column header="Perfil">
                    <template #body="{ data }">
                        <Tag
                            v-if="data.roles?.length"
                            :value="data.roles[0].label || data.roles[0].name"
                            severity="info"
                        />
                        <span v-else class="sem-perfil">—</span>
                    </template>
                </Column>
                <Column header="Status">
                    <template #body="{ data }">
                        <Tag
                            :value="data.ativo ? 'Ativo' : 'Inativo'"
                            :severity="data.ativo ? 'success' : 'danger'"
                        />
                    </template>
                </Column>
                <Column header="Ações" style="width: 180px;">
                    <template #body="{ data }">
                        <div style="display: flex; gap: 0.5rem;">
                            <Button
                                icon="pi pi-key"
                                severity="secondary"
                                size="small"
                                v-tooltip.top="'Permissões'"
                                @click="router.visit(route('core.usuarios.permissoes', data.id))"
                            />
                            <Button
                                icon="pi pi-pencil"
                                severity="secondary"
                                size="small"
                                v-tooltip.top="'Editar'"
                                @click="router.visit(route('core.usuarios.edit', data.id))"
                            />
                            <Button
                                v-if="data.ativo"
                                icon="pi pi-ban"
                                severity="danger"
                                size="small"
                                v-tooltip.top="'Desativar'"
                                @click="confirmarDesativar(data)"
                            />
                            <Button
                                v-else
                                icon="pi pi-check-circle"
                                severity="success"
                                size="small"
                                v-tooltip.top="'Reativar'"
                                @click="router.patch(route('core.usuarios.reativar', data.id))"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Paginação -->
            <Paginator
                v-if="usuarios.last_page > 1"
                :rows="usuarios.per_page"
                :total-records="usuarios.total"
                :first="(usuarios.current_page - 1) * usuarios.per_page"
                @page="irParaPagina"
                class="mt-4"
            />

            <ConfirmDialog />
        </div>
    </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { useConfirm } from 'primevue/useconfirm'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from 'primevue/button'
import Message from 'primevue/message'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Paginator from 'primevue/paginator'
import ConfirmDialog from 'primevue/confirmdialog'

const props = defineProps({
    usuarios: { type: Object, required: true },
    perfis:   { type: Array,  default: () => [] },
    filtros:  { type: Object, default: () => ({}) },
})

const flash   = usePage().props.flash
const confirm = useConfirm()

const opcoesAtivo = [
    { label: 'Ativo',   value: '1' },
    { label: 'Inativo', value: '0' },
]

const filtros = reactive({
    busca:  props.filtros.busca  ?? '',
    perfil: props.filtros.perfil ?? null,
    ativo:  props.filtros.ativo  ?? null,
})

const breadcrumb = [
    { label: 'Administração', icon: 'pi pi-cog' },
    { label: 'Usuários' },
]

function aplicarFiltros() {
    router.get(route('core.usuarios.index'), filtros, { preserveState: true })
}

function irParaPagina(event) {
    const pagina = event.page + 1
    router.get(route('core.usuarios.index'), { ...filtros, page: pagina }, { preserveState: true })
}

function confirmarDesativar(usuario) {
    confirm.require({
        message: `Desativar o usuário "${usuario.name}"?`,
        header:  'Confirmar desativação',
        icon:    'pi pi-exclamation-triangle',
        rejectLabel: 'Cancelar',
        acceptLabel: 'Desativar',
        acceptClass: 'p-button-danger',
        accept: () => router.delete(route('core.usuarios.destroy', usuario.id)),
    })
}
</script>

<style scoped>
.page-container { max-width: 1100px; }

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

.dark .page-title { color: #f1f5f9; }

.mb-4 { margin-bottom: 1rem; }

.filtros {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.filtros__busca { flex: 1; min-width: 220px; }
.filtros__select { width: 180px; }

.sem-perfil { color: #9ca3af; }

.mt-4 { margin-top: 1rem; }
</style>
