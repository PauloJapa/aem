<template>
    <Dialog
        :visible="visible"
        modal
        header="Selecionar Ícone"
        :style="{ width: '560px' }"
        @update:visible="$emit('update:visible', $event)"
    >
        <div style="margin-bottom: 0.75rem;">
            <InputText
                v-model="filtro"
                placeholder="Filtrar ícones..."
                style="width: 100%;"
            >
                <template #prefix>
                    <i class="pi pi-search" />
                </template>
            </InputText>
        </div>

        <div class="icon-grid">
            <button
                v-for="icone in iconesFiltrados"
                :key="icone"
                type="button"
                class="icon-grid__item"
                :class="{ 'icon-grid__item--selected': icone === selecionado }"
                @click="selecionado = icone"
            >
                <i :class="icone" class="icon-grid__icon" />
                <span class="icon-grid__nome">{{ icone.replace('pi pi-', '') }}</span>
            </button>
        </div>

        <template #footer>
            <Button
                label="Cancelar"
                severity="secondary"
                text
                @click="$emit('update:visible', false)"
            />
            <Button
                label="Confirmar"
                icon="pi pi-check"
                :disabled="!selecionado"
                @click="confirmar"
            />
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'

const props = defineProps({
    modelValue: { type: String, default: 'pi pi-circle' },
    visible: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'update:visible'])

const filtro = ref('')
const selecionado = ref(props.modelValue)

watch(() => props.modelValue, (v) => { selecionado.value = v })
watch(() => props.visible, (v) => { if (v) filtro.value = '' })

const icones = [
    'pi pi-home', 'pi pi-database', 'pi pi-users', 'pi pi-user',
    'pi pi-truck', 'pi pi-box', 'pi pi-shopping-cart', 'pi pi-shopping-bag',
    'pi pi-wallet', 'pi pi-chart-bar', 'pi pi-chart-line', 'pi pi-chart-pie',
    'pi pi-list', 'pi pi-file', 'pi pi-file-pdf', 'pi pi-file-excel',
    'pi pi-inbox', 'pi pi-send', 'pi pi-bell', 'pi pi-cog',
    'pi pi-warehouse', 'pi pi-arrows-v', 'pi pi-clipboard', 'pi pi-calendar',
    'pi pi-dollar', 'pi pi-credit-card', 'pi pi-arrow-up-right',
    'pi pi-arrow-down-left', 'pi pi-percentage', 'pi pi-tag',
    'pi pi-tags', 'pi pi-map-marker', 'pi pi-phone', 'pi pi-envelope',
    'pi pi-shield', 'pi pi-lock', 'pi pi-key', 'pi pi-check-circle',
    'pi pi-times-circle', 'pi pi-exclamation-triangle', 'pi pi-info-circle',
    'pi pi-star', 'pi pi-heart', 'pi pi-bookmark', 'pi pi-print',
    'pi pi-download', 'pi pi-upload', 'pi pi-refresh', 'pi pi-search',
    'pi pi-filter', 'pi pi-sort', 'pi pi-table', 'pi pi-th-large',
    'pi pi-bars', 'pi pi-ellipsis-v', 'pi pi-plus', 'pi pi-minus',
    'pi pi-pencil', 'pi pi-trash', 'pi pi-eye', 'pi pi-circle',
]

const iconesFiltrados = computed(() => {
    const q = filtro.value.trim().toLowerCase()
    if (!q) return icones
    return icones.filter(i => i.includes(q))
})

function confirmar() {
    emit('update:modelValue', selecionado.value)
    emit('update:visible', false)
}
</script>

<style scoped>
.icon-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 4px;
    max-height: 320px;
    overflow-y: auto;
    padding: 4px;
}

.icon-grid__item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 8px 4px;
    border: 1px solid transparent;
    border-radius: 6px;
    background: none;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s;
}

.icon-grid__item:hover {
    background: #f0fdfa;
    border-color: #99f6e4;
}

.icon-grid__item--selected {
    background: #ccfbf1;
    border-color: #0d9488;
}

.icon-grid__icon {
    font-size: 1.125rem;
    color: #0f766e;
}

.icon-grid__nome {
    font-size: 0.625rem;
    color: #6b7280;
    text-align: center;
    word-break: break-all;
    line-height: 1.2;
}
</style>
