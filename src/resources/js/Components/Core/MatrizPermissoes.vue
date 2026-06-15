<template>
    <div class="matriz">
        <div v-for="(recursos, modulo) in agrupadas" :key="modulo" class="matriz__modulo">
            <div class="matriz__modulo-header">
                <span class="matriz__modulo-nome">{{ modulo }}</span>
            </div>

            <div class="matriz__tabela-wrap">
                <table class="matriz__tabela">
                    <thead>
                        <tr>
                            <th class="matriz__th-recurso">Recurso</th>
                            <th
                                v-for="acao in acoes"
                                :key="acao"
                                class="matriz__th-acao"
                            >{{ acao }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(permsPorAcao, recurso) in recursos" :key="recurso">
                            <td class="matriz__recurso">{{ recurso }}</td>
                            <td
                                v-for="acao in acoes"
                                :key="acao"
                                class="matriz__cell"
                            >
                                <template v-if="permsPorAcao[acao]">
                                    <slot
                                        name="cell"
                                        :permission="permsPorAcao[acao]"
                                        :modulo="modulo"
                                        :recurso="recurso"
                                        :acao="acao"
                                    >
                                        <!-- Default: checkbox simples -->
                                        <Checkbox
                                            :model-value="isChecked(permsPorAcao[acao])"
                                            :binary="true"
                                            @update:model-value="(v) => toggle(permsPorAcao[acao], v)"
                                        />
                                    </slot>
                                </template>
                                <span v-else class="matriz__cell-empty">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import Checkbox from 'primevue/checkbox'

const props = defineProps({
    agrupadas:  { type: Object, required: true },
    acoes:      { type: Array,  required: true },
    modelValue: { type: Array,  default: () => [] },
})

const emit = defineEmits(['update:modelValue'])

function isChecked(permission) {
    return props.modelValue.includes(permission)
}

function toggle(permission, checked) {
    const atual = [...props.modelValue]
    if (checked) {
        if (!atual.includes(permission)) atual.push(permission)
    } else {
        const idx = atual.indexOf(permission)
        if (idx >= 0) atual.splice(idx, 1)
    }
    emit('update:modelValue', atual)
}
</script>

<style scoped>
.matriz {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.matriz__modulo-header {
    background: #f1f5f9;
    border-radius: 6px 6px 0 0;
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-bottom: none;
}

.dark .matriz__modulo-header {
    background: #1e293b;
    border-color: #334155;
}

.matriz__modulo-nome {
    font-weight: 600;
    font-size: 0.875rem;
    color: #0f172a;
}

.dark .matriz__modulo-nome { color: #f1f5f9; }

.matriz__tabela-wrap {
    overflow-x: auto;
    border: 1px solid #e2e8f0;
    border-radius: 0 0 6px 6px;
}

.dark .matriz__tabela-wrap { border-color: #334155; }

.matriz__tabela {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8125rem;
}

.matriz__th-recurso {
    text-align: left;
    padding: 0.5rem 0.75rem;
    font-weight: 600;
    color: #475569;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    min-width: 160px;
}

.dark .matriz__th-recurso {
    background: #0f172a;
    color: #94a3b8;
    border-color: #334155;
}

.matriz__th-acao {
    text-align: center;
    padding: 0.5rem 0.5rem;
    font-weight: 600;
    color: #475569;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    text-transform: capitalize;
    min-width: 90px;
}

.dark .matriz__th-acao {
    background: #0f172a;
    color: #94a3b8;
    border-color: #334155;
}

.matriz__recurso {
    padding: 0.5rem 0.75rem;
    color: #334155;
    border-bottom: 1px solid #f1f5f9;
}

.dark .matriz__recurso {
    color: #cbd5e1;
    border-color: #1e293b;
}

.matriz__cell {
    text-align: center;
    padding: 0.5rem;
    border-bottom: 1px solid #f1f5f9;
}

.dark .matriz__cell { border-color: #1e293b; }

.matriz__cell-empty {
    color: #cbd5e1;
    font-size: 0.75rem;
}

tr:last-child .matriz__recurso,
tr:last-child .matriz__cell {
    border-bottom: none;
}
</style>
