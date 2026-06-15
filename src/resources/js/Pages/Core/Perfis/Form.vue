<template>
    <AppLayout :breadcrumb="breadcrumb">
        <div class="page-container">
            <div class="page-header">
                <h1 class="page-title">{{ perfil ? 'Editar Perfil' : 'Novo Perfil' }}</h1>
            </div>

            <div class="form-card">
                <form @submit.prevent="submit">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="name">Nome técnico <span class="required">*</span></label>
                            <InputText
                                id="name"
                                v-model="form.name"
                                placeholder="ex: gerente-financeiro"
                                :disabled="!!perfil"
                                :class="{ 'p-invalid': form.errors.name }"
                                class="w-full"
                            />
                            <small v-if="perfil" class="help-text">O nome técnico não pode ser alterado após a criação.</small>
                            <small v-if="form.errors.name" class="p-error">{{ form.errors.name }}</small>
                        </div>

                        <div class="form-field">
                            <label for="label">Nome de exibição <span class="required">*</span></label>
                            <InputText
                                id="label"
                                v-model="form.label"
                                placeholder="ex: Gerente Financeiro"
                                :class="{ 'p-invalid': form.errors.label }"
                                class="w-full"
                            />
                            <small v-if="form.errors.label" class="p-error">{{ form.errors.label }}</small>
                        </div>
                    </div>

                    <div class="form-actions">
                        <Button
                            label="Cancelar"
                            severity="secondary"
                            type="button"
                            @click="router.visit(route('core.perfis.index'))"
                        />
                        <Button
                            :label="perfil ? 'Salvar alterações' : 'Criar perfil'"
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
import { computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'

const props = defineProps({
    perfil: { type: Object, default: null },
})

const breadcrumb = [
    { label: 'Administração', icon: 'pi pi-cog' },
    { label: 'Perfis', url: route('core.perfis.index') },
    { label: props.perfil ? 'Editar' : 'Novo' },
]

const form = useForm({
    name:  props.perfil?.name  ?? '',
    label: props.perfil?.label ?? '',
})

function submit() {
    if (props.perfil) {
        form.put(route('core.perfis.update', props.perfil.id))
    } else {
        form.post(route('core.perfis.store'))
    }
}
</script>

<style scoped>
.page-container { max-width: 600px; }

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
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.form-field label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

.dark .form-field label { color: #cbd5e1; }

.required { color: #ef4444; }

.help-text { font-size: 0.75rem; color: #9ca3af; }

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
</style>
