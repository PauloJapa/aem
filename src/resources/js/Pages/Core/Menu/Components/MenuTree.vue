<template>
    <div>
        <VueDraggable
            v-model="itensRaiz"
            handle=".drag-root"
            :animation="150"
            :group="{ name: 'raiz', pull: false, put: false }"
            @end="emitirReordenacao"
        >
            <div v-for="element in itensRaiz" :key="element.id" class="menu-item">
                <div class="menu-item__row">
                    <span class="drag-root menu-item__grip" title="Arraste para reordenar">
                        <i class="pi pi-th-large" />
                    </span>
                    <i :class="element.icon" class="menu-item__icon" />
                    <span class="menu-item__label">{{ element.label }}</span>
                    <div class="menu-item__actions">
                        <Button
                            icon="pi pi-pencil"
                            text
                            rounded
                            size="small"
                            severity="secondary"
                            v-tooltip="'Editar'"
                            @click="editar(element)"
                        />
                        <Button
                            icon="pi pi-trash"
                            text
                            rounded
                            size="small"
                            severity="danger"
                            v-tooltip="'Excluir'"
                            @click="confirmarDeletar(element)"
                        />
                        <ToggleSwitch
                            :model-value="element.ativo"
                            v-tooltip="element.ativo ? 'Ativo' : 'Inativo'"
                            @update:model-value="toggleAtivo(element, $event)"
                        />
                    </div>
                </div>

                <div v-if="element.filhos?.length" class="menu-item__children">
                    <VueDraggable
                        v-model="element.filhos"
                        handle=".drag-child"
                        :animation="150"
                        :group="{ name: 'filhos-' + element.id, pull: false, put: false }"
                        @end="emitirReordenacao"
                    >
                        <div v-for="filho in element.filhos" :key="filho.id" class="menu-item menu-item--filho">
                            <div class="menu-item__row">
                                <span class="drag-child menu-item__grip" title="Arraste para reordenar">
                                    <i class="pi pi-th-large" />
                                </span>
                                <i :class="filho.icon" class="menu-item__icon menu-item__icon--small" />
                                <span class="menu-item__label">{{ filho.label }}</span>
                                <div class="menu-item__actions">
                                    <Button
                                        icon="pi pi-pencil"
                                        text
                                        rounded
                                        size="small"
                                        severity="secondary"
                                        v-tooltip="'Editar'"
                                        @click="editar(filho)"
                                    />
                                    <Button
                                        icon="pi pi-trash"
                                        text
                                        rounded
                                        size="small"
                                        severity="danger"
                                        v-tooltip="'Excluir'"
                                        @click="confirmarDeletar(filho)"
                                    />
                                    <ToggleSwitch
                                        :model-value="filho.ativo"
                                        v-tooltip="filho.ativo ? 'Ativo' : 'Inativo'"
                                        @update:model-value="toggleAtivo(filho, $event)"
                                    />
                                </div>
                            </div>
                        </div>
                    </VueDraggable>
                </div>
            </div>
        </VueDraggable>

        <Dialog
            v-model:visible="dialogo.visivel"
            modal
            header="Confirmar exclusão"
            :style="{ width: '380px' }"
        >
            <p style="margin: 0;">
                Deseja excluir o item <strong>{{ dialogo.item?.label }}</strong>?
                Esta ação não pode ser desfeita.
            </p>
            <template #footer>
                <Button label="Cancelar" severity="secondary" text @click="dialogo.visivel = false" />
                <Button label="Excluir" severity="danger" icon="pi pi-trash" @click="deletar" />
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { VueDraggable } from 'vue-draggable-plus'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import ToggleSwitch from 'primevue/toggleswitch'

const props = defineProps({
    arvore: { type: Array, required: true },
})

const emit = defineEmits(['reordenar'])

const itensRaiz = ref(JSON.parse(JSON.stringify(props.arvore)))

watch(() => props.arvore, (v) => {
    itensRaiz.value = JSON.parse(JSON.stringify(v))
}, { deep: true })

const dialogo = ref({ visivel: false, item: null })

function editar(item) {
    router.visit(route('core.menus.edit', item.id))
}

function confirmarDeletar(item) {
    dialogo.value = { visivel: true, item }
}

function deletar() {
    router.delete(route('core.menus.destroy', dialogo.value.item.id), {
        preserveScroll: true,
        onSuccess: () => { dialogo.value.visivel = false },
    })
}

function toggleAtivo(item, valor) {
    router.patch(route('core.menus.update', item.id), {
        label:      item.label,
        icon:       item.icon,
        parent_id:  item.parent_id ?? null,
        rota:       item.rota ?? '',
        permission: item.permission ?? '',
        ativo:      valor,
        _method:    'PUT',
    }, { preserveScroll: true })
}

function emitirReordenacao() {
    const flat = []
    itensRaiz.value.forEach((pai, iPai) => {
        flat.push({ id: pai.id, ordem: iPai, parent_id: null })
        if (pai.filhos?.length) {
            pai.filhos.forEach((filho, iFilho) => {
                flat.push({ id: filho.id, ordem: iFilho, parent_id: pai.id })
            })
        }
    })
    emit('reordenar', flat)
}
</script>

<style scoped>
.menu-item {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    margin-bottom: 4px;
}

.menu-item--filho {
    border-radius: 4px;
    margin-bottom: 2px;
}

.menu-item__row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
}

.menu-item__grip {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 28px;
    color: #6b7280;
    cursor: grab;
    flex-shrink: 0;
    user-select: none;
    touch-action: none;
    border-radius: 4px;
    transition: color 0.15s, background 0.15s;
}

.menu-item__grip:hover {
    color: #0f766e;
    background: #f0fdfa;
    cursor: grab;
}

.menu-item__grip:active {
    cursor: grabbing;
    color: #0f766e;
}

.menu-item__grip i {
    pointer-events: none;
    font-size: 0.875rem;
}

.menu-item__icon {
    color: #0f766e;
    font-size: 1rem;
    flex-shrink: 0;
}

.menu-item__icon--small {
    font-size: 0.875rem;
}

.menu-item__label {
    flex: 1;
    font-size: 0.875rem;
    font-weight: 500;
    color: #111827;
}

.menu-item__actions {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
}

.menu-item__children {
    padding: 0 12px 8px 32px;
}

.dark .menu-item {
    background: #1e293b;
    border-color: #334155;
}

.dark .menu-item__label {
    color: #f1f5f9;
}

.dark .menu-item__grip {
    color: #64748b;
}
</style>
