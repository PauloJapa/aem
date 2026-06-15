<template>
    <AppLayout :breadcrumb="breadcrumb">
        <div class="page-container">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Permissões — {{ perfil.label }}</h1>
                    <p class="page-subtitle">
                        Marque as permissões que este perfil deve ter.
                        Todos os usuários com este perfil herdarão estas permissões.
                    </p>
                </div>
            </div>

            <Message v-if="flash?.error"   severity="error"   :closable="false" class="mb-4">{{ flash.error }}</Message>
            <Message v-if="flash?.success" severity="success" :closable="false" class="mb-4">{{ flash.success }}</Message>

            <MatrizPermissoes
                :agrupadas="agrupadas"
                :acoes="acoes"
                v-model="selecionadas"
            />

            <div class="form-actions">
                <Button
                    label="Voltar"
                    severity="secondary"
                    @click="router.visit(route('core.perfis.index'))"
                />
                <Button
                    label="Salvar permissões"
                    icon="pi pi-save"
                    :loading="salvando"
                    @click="salvar"
                />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from 'primevue/button'
import Message from 'primevue/message'
import MatrizPermissoes from '@/Components/Core/MatrizPermissoes.vue'

const props = defineProps({
    perfil:          { type: Object, required: true },
    agrupadas:       { type: Object, required: true },
    acoes:           { type: Array,  required: true },
    permissoesAtivas: { type: Array,  default: () => [] },
})

const flash      = usePage().props.flash
const salvando   = ref(false)
const selecionadas = ref([...props.permissoesAtivas])

const breadcrumb = [
    { label: 'Administração', icon: 'pi pi-cog' },
    { label: 'Perfis', url: route('core.perfis.index') },
    { label: props.perfil.label },
    { label: 'Permissões' },
]

async function salvar() {
    salvando.value = true
    try {
        await axios.put(route('core.perfis.permissoes.salvar', props.perfil.id), {
            permissions: selecionadas.value,
        })
        router.visit(route('core.perfis.permissoes', props.perfil.id), {
            preserveState: false,
        })
    } catch {
        salvando.value = false
    }
}
</script>

<style scoped>
.page-container { max-width: 1100px; }

.page-header { margin-bottom: 1.5rem; }

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

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.dark .form-actions { border-color: #334155; }
</style>
