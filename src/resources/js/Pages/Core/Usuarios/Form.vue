<template>
    <AppLayout :breadcrumb="breadcrumb">
        <div class="page-container">
            <div class="page-header">
                <h1 class="page-title">{{ usuario ? 'Editar Usuário' : 'Novo Usuário' }}</h1>
            </div>

            <div class="form-card">
                <form @submit.prevent="submit">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="name">Nome completo <span class="required">*</span></label>
                            <InputText
                                id="name"
                                v-model="form.name"
                                placeholder="Nome do usuário"
                                :class="{ 'p-invalid': form.errors.name }"
                                class="w-full"
                            />
                            <small v-if="form.errors.name" class="p-error">{{ form.errors.name }}</small>
                        </div>

                        <div class="form-field">
                            <label for="email">E-mail <span class="required">*</span></label>
                            <InputText
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="usuario@empresa.com"
                                :class="{ 'p-invalid': form.errors.email }"
                                class="w-full"
                            />
                            <small v-if="form.errors.email" class="p-error">{{ form.errors.email }}</small>
                        </div>

                        <div class="form-field">
                            <label for="telefone">Telefone</label>
                            <InputText
                                id="telefone"
                                v-model="form.telefone"
                                placeholder="(11) 99999-9999"
                                class="w-full"
                            />
                        </div>

                        <div class="form-field">
                            <label for="perfil">Perfil <span v-if="!usuario" class="required">*</span></label>
                            <Select
                                id="perfil"
                                v-model="form.perfil"
                                :options="perfis"
                                option-label="label"
                                option-value="name"
                                placeholder="Selecionar perfil"
                                :class="{ 'p-invalid': form.errors.perfil }"
                                class="w-full"
                            />
                            <small v-if="form.errors.perfil" class="p-error">{{ form.errors.perfil }}</small>
                        </div>

                        <div class="form-field">
                            <label for="password">
                                Senha <span v-if="!usuario" class="required">*</span>
                                <small v-else class="help-text">(deixe em branco para não alterar)</small>
                            </label>
                            <Password
                                id="password"
                                v-model="form.password"
                                :feedback="true"
                                toggle-mask
                                :class="{ 'p-invalid': form.errors.password }"
                                class="w-full"
                                input-class="w-full"
                            />
                            <small v-if="form.errors.password" class="p-error">{{ form.errors.password }}</small>
                        </div>

                        <div class="form-field">
                            <label for="password_confirmation">Confirmar senha</label>
                            <Password
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :feedback="false"
                                toggle-mask
                                class="w-full"
                                input-class="w-full"
                            />
                        </div>

                        <div class="form-field form-field--inline">
                            <ToggleSwitch v-model="form.ativo" input-id="ativo" />
                            <label for="ativo">Usuário ativo</label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <Button
                            label="Cancelar"
                            severity="secondary"
                            type="button"
                            @click="router.visit(route('core.usuarios.index'))"
                        />
                        <Button
                            :label="usuario ? 'Salvar alterações' : 'Criar usuário'"
                            type="submit"
                            :loading="form.processing"
                        />
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Password from 'primevue/password'
import ToggleSwitch from 'primevue/toggleswitch'

const props = defineProps({
    usuario: { type: Object, default: null },
    perfis:  { type: Array,  default: () => [] },
})

const breadcrumb = [
    { label: 'Administração', icon: 'pi pi-cog' },
    { label: 'Usuários', url: route('core.usuarios.index') },
    { label: props.usuario ? 'Editar' : 'Novo' },
]

const form = useForm({
    name:                 props.usuario?.name      ?? '',
    email:                props.usuario?.email     ?? '',
    telefone:             props.usuario?.telefone  ?? '',
    perfil:               props.usuario?.roles?.[0]?.name ?? null,
    password:             '',
    password_confirmation: '',
    ativo:                props.usuario?.ativo ?? true,
})

function submit() {
    if (props.usuario) {
        form.put(route('core.usuarios.update', props.usuario.id))
    } else {
        form.post(route('core.usuarios.store'))
    }
}
</script>

<style scoped>
.page-container { max-width: 700px; }
.page-header { margin-bottom: 1.25rem; }

.page-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.dark .page-title { color: #f1f5f9; }

.form-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
}

.dark .form-card {
    background: #1e293b;
    border-color: #334155;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.form-field--inline {
    flex-direction: row;
    align-items: center;
    gap: 0.75rem;
}

.form-field label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

.dark .form-field label { color: #cbd5e1; }

.required { color: #ef4444; }
.help-text { font-size: 0.75rem; color: #9ca3af; font-weight: 400; }
.p-error { font-size: 0.75rem; color: #ef4444; }
.w-full { width: 100%; }

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding-top: 1rem;
    border-top: 1px solid #f3f4f6;
}

.dark .form-actions { border-color: #334155; }

@media (max-width: 600px) {
    .form-grid { grid-template-columns: 1fr; }
}
</style>
