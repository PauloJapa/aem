<template>
    <AppLayout :breadcrumb="breadcrumb">
        <div class="menu-form">
            <form @submit.prevent="submit">
                <div class="menu-form__card">
                    <!-- Label -->
                    <div class="menu-form__field">
                        <label class="menu-form__label">
                            Label <span class="menu-form__required">*</span>
                        </label>
                        <InputText
                            v-model="form.label"
                            placeholder="Ex: Financeiro"
                            :invalid="!!form.errors.label"
                            style="width: 100%;"
                        />
                        <small v-if="form.errors.label" class="menu-form__error">{{ form.errors.label }}</small>
                    </div>

                    <!-- Ícone -->
                    <div class="menu-form__field">
                        <label class="menu-form__label">
                            Ícone <span class="menu-form__required">*</span>
                        </label>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <div class="menu-form__icon-preview">
                                <i :class="form.icon" style="font-size: 1.25rem; color: #0f766e;" />
                            </div>
                            <InputText
                                :model-value="form.icon"
                                readonly
                                :invalid="!!form.errors.icon"
                                style="flex: 1;"
                            />
                            <Button
                                type="button"
                                label="Escolher"
                                icon="pi pi-palette"
                                severity="secondary"
                                @click="iconPickerVisivel = true"
                            />
                        </div>
                        <small v-if="form.errors.icon" class="menu-form__error">{{ form.errors.icon }}</small>
                    </div>

                    <!-- Item pai -->
                    <div class="menu-form__field">
                        <label class="menu-form__label">Item pai</label>
                        <Select
                            v-model="form.parent_id"
                            :options="opcoesPai"
                            option-label="label"
                            option-value="id"
                            placeholder="Nenhum (item principal)"
                            :show-clear="true"
                            :invalid="!!form.errors.parent_id"
                            style="width: 100%;"
                        />
                        <small v-if="form.errors.parent_id" class="menu-form__error">{{ form.errors.parent_id }}</small>
                    </div>

                    <!-- Rota -->
                    <div class="menu-form__field">
                        <label class="menu-form__label">Rota</label>
                        <InputText
                            v-model="form.rota"
                            placeholder="Ex: financeiro.contas-pagar.index"
                            :invalid="!!form.errors.rota"
                            style="width: 100%;"
                        />
                        <small class="menu-form__hint">Nome da rota Laravel. Deixe vazio para grupos sem link.</small>
                        <small v-if="form.errors.rota" class="menu-form__error">{{ form.errors.rota }}</small>
                    </div>

                    <!-- Permission -->
                    <div class="menu-form__field">
                        <label class="menu-form__label">Permissão</label>
                        <InputText
                            v-model="form.permission"
                            placeholder="Ex: financeiro.contas-pagar.visualizar"
                            :invalid="!!form.errors.permission"
                            style="width: 100%;"
                        />
                        <small class="menu-form__hint">Deixe vazio para exibir a todos os usuários autenticados.</small>
                        <small v-if="form.errors.permission" class="menu-form__error">{{ form.errors.permission }}</small>
                    </div>

                    <!-- Ativo -->
                    <div class="menu-form__field menu-form__field--inline">
                        <label class="menu-form__label">Ativo</label>
                        <ToggleSwitch v-model="form.ativo" />
                    </div>
                </div>

                <!-- Botões -->
                <div class="menu-form__footer">
                    <Button
                        type="button"
                        label="Cancelar"
                        severity="secondary"
                        text
                        @click="router.visit(route('core.menus.index'))"
                    />
                    <Button
                        type="submit"
                        :label="isEdicao ? 'Salvar alterações' : 'Criar item'"
                        icon="pi pi-check"
                        :loading="form.processing"
                    />
                </div>
            </form>
        </div>

        <IconPicker
            v-model="form.icon"
            v-model:visible="iconPickerVisivel"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Button from 'primevue/button'
import ToggleSwitch from 'primevue/toggleswitch'
import IconPicker from './Components/IconPicker.vue'

const props = defineProps({
    menu: { type: Object, default: null },
    pais: { type: Array, default: () => [] },
})

const isEdicao = computed(() => !!props.menu)

const breadcrumb = computed(() => [
    { label: 'Configurações', icon: 'pi pi-cog' },
    { label: 'Menu do Sistema', url: route('core.menus.index') },
    { label: isEdicao.value ? `Editar: ${props.menu.label}` : 'Novo Item' },
])

const opcoesPai = computed(() => props.pais)

const iconPickerVisivel = ref(false)

const form = useForm({
    label:      props.menu?.label ?? '',
    icon:       props.menu?.icon ?? 'pi pi-circle',
    parent_id:  props.menu?.parent_id ?? null,
    rota:       props.menu?.rota ?? '',
    permission: props.menu?.permission ?? '',
    ativo:      props.menu?.ativo ?? true,
})

function submit() {
    if (isEdicao.value) {
        form.put(route('core.menus.update', props.menu.id), {
            onSuccess: () => router.visit(route('core.menus.index')),
        })
    } else {
        form.post(route('core.menus.store'), {
            onSuccess: () => router.visit(route('core.menus.index')),
        })
    }
}
</script>

<style scoped>
.menu-form {
    max-width: 600px;
}

.menu-form__card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    margin-bottom: 1rem;
}

.menu-form__field {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.menu-form__field--inline {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
}

.menu-form__label {
    font-size: 0.8125rem;
    font-weight: 500;
    color: #374151;
}

.menu-form__required {
    color: #ef4444;
}

.menu-form__hint {
    font-size: 0.75rem;
    color: #9ca3af;
}

.menu-form__error {
    font-size: 0.75rem;
    color: #ef4444;
}

.menu-form__icon-preview {
    width: 42px;
    height: 42px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f9fafb;
    flex-shrink: 0;
}

.menu-form__footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

.dark .menu-form__card {
    background: #1e293b;
    border-color: #334155;
}

.dark .menu-form__label { color: #cbd5e1; }
.dark .menu-form__icon-preview { background: #0f172a; border-color: #334155; }
</style>
