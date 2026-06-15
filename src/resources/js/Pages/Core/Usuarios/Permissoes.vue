<template>
    <AppLayout :breadcrumb="breadcrumb">
        <div class="page-container">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Permissões — {{ usuario.name }}</h1>
                    <p class="page-subtitle">
                        Permissões herdadas do perfil <strong>{{ perfilNome }}</strong> são mostradas em cinza.
                        Use as colunas "Extra" e "Bloqueada" para personalizar o acesso deste usuário.
                    </p>
                </div>
            </div>

            <Message v-if="flash?.error"   severity="error"   :closable="false" class="mb-4">{{ flash.error }}</Message>
            <Message v-if="flash?.success" severity="success" :closable="false" class="mb-4">{{ flash.success }}</Message>

            <!-- Legenda -->
            <div class="legenda">
                <span class="legenda__item legenda__item--herdada">
                    <span class="legenda__dot legenda__dot--herdada"></span> Herdada do perfil
                </span>
                <span class="legenda__item">
                    <span class="legenda__dot legenda__dot--extra"></span> Permissão extra
                </span>
                <span class="legenda__item">
                    <span class="legenda__dot legenda__dot--bloqueada"></span> Permissão bloqueada
                </span>
            </div>

            <MatrizPermissoes
                :agrupadas="agrupadas"
                :acoes="acoes"
                :model-value="extras"
                @update:model-value="() => {}"
            >
                <template #cell="{ permission }">
                    <div class="cell-custom">
                        <!-- Herdada -->
                        <span
                            v-if="permissoesPerfil.includes(permission)"
                            class="cell-herdada"
                            v-tooltip.top="'Herdada do perfil'"
                        >
                            <i class="pi pi-check-circle" />
                        </span>

                        <!-- Extra -->
                        <Checkbox
                            v-else
                            :model-value="extras.includes(permission)"
                            :binary="true"
                            v-tooltip.top="'Permissão extra'"
                            @update:model-value="(v) => toggleExtra(permission, v)"
                        />

                        <!-- Bloqueada (só aparece se herdada) -->
                        <span
                            v-if="permissoesPerfil.includes(permission)"
                            v-tooltip.top="'Bloquear esta permissão'"
                        >
                            <Checkbox
                                :model-value="bloqueadas.includes(permission)"
                                :binary="true"
                                class="checkbox-bloqueada"
                                @update:model-value="(v) => toggleBloqueada(permission, v)"
                            />
                        </span>
                    </div>
                </template>
            </MatrizPermissoes>

            <div class="form-actions">
                <Button
                    label="Voltar"
                    severity="secondary"
                    @click="router.visit(route('core.usuarios.index'))"
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
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from 'primevue/button'
import Message from 'primevue/message'
import Checkbox from 'primevue/checkbox'
import MatrizPermissoes from '@/Components/Core/MatrizPermissoes.vue'

const props = defineProps({
    usuario:          { type: Object, required: true },
    agrupadas:        { type: Object, required: true },
    acoes:            { type: Array,  required: true },
    permissoesPerfil: { type: Array,  default: () => [] },
    permissoesExtra:  { type: Array,  default: () => [] },
    bloqueadas:       { type: Array,  default: () => [] },
})

const flash    = usePage().props.flash
const salvando = ref(false)
const extras   = ref([...props.permissoesExtra])
const bloqueadas = ref([...props.bloqueadas])

const perfilNome = computed(() => props.usuario.roles?.[0]?.label ?? props.usuario.roles?.[0]?.name ?? 'sem perfil')

const breadcrumb = [
    { label: 'Administração', icon: 'pi pi-cog' },
    { label: 'Usuários', url: route('core.usuarios.index') },
    { label: props.usuario.name },
    { label: 'Permissões' },
]

function toggleExtra(permission, checked) {
    if (checked) {
        if (!extras.value.includes(permission)) extras.value.push(permission)
    } else {
        extras.value = extras.value.filter(p => p !== permission)
    }
}

function toggleBloqueada(permission, checked) {
    if (checked) {
        if (!bloqueadas.value.includes(permission)) bloqueadas.value.push(permission)
    } else {
        bloqueadas.value = bloqueadas.value.filter(p => p !== permission)
    }
}

async function salvar() {
    salvando.value = true
    try {
        await axios.put(route('core.usuarios.permissoes.salvar', props.usuario.id), {
            extras:    extras.value,
            bloqueadas: bloqueadas.value,
        })
        router.visit(route('core.usuarios.permissoes', props.usuario.id), {
            preserveState: false,
        })
    } catch {
        salvando.value = false
    }
}
</script>

<style scoped>
.page-container { max-width: 1100px; }

.page-header { margin-bottom: 1rem; }

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
    max-width: 700px;
}

.dark .page-title { color: #f1f5f9; }

.mb-4 { margin-bottom: 1rem; }

.legenda {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.legenda__item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.8125rem;
    color: #6b7280;
}

.legenda__dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.legenda__dot--herdada  { background: #3b82f6; }
.legenda__dot--extra    { background: #10b981; }
.legenda__dot--bloqueada { background: #ef4444; }

.cell-custom {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.cell-herdada {
    color: #3b82f6;
    font-size: 1rem;
    display: flex;
    align-items: center;
}

:deep(.checkbox-bloqueada .p-checkbox-box) {
    border-color: #ef4444;
}

:deep(.checkbox-bloqueada.p-checkbox-checked .p-checkbox-box) {
    background: #ef4444;
    border-color: #ef4444;
}

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
