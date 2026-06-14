<template>
    <div style="position:relative; display:inline-flex; align-items:center;">
        <button
            type="button"
            class="erp-header__icon-btn"
            @click="toggle"
            aria-label="Notificações"
        >
            <i class="pi pi-bell" style="font-size:1.1rem;" />
            <span v-if="store.naoLidas > 0" class="erp-notif-badge">{{ store.naoLidas }}</span>
        </button>

        <Popover ref="popover">
            <div style="width:320px; max-height:420px; display:flex; flex-direction:column;">
                <!-- cabeçalho -->
                <div style="display:flex; align-items:center; justify-content:space-between; padding:0.75rem 1rem; border-bottom:1px solid var(--p-surface-200);">
                    <span style="font-weight:600; font-size:0.875rem;">Notificações</span>
                    <button type="button" class="erp-notif-limpar" @click="store.marcarTodasComoLidas()">
                        Limpar
                    </button>
                </div>

                <!-- lista -->
                <div style="overflow-y:auto; flex:1;">
                    <div
                        v-for="n in store.notificacoes"
                        :key="n.id"
                        class="erp-notif-item"
                        :class="{ 'erp-notif-item--lida': n.lida }"
                        @click="marcar(n.id)"
                    >
                        <span class="erp-notif-dot" :style="{ background: corTipo(n.tipo) }" />
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:0.8125rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ n.titulo }}
                            </div>
                            <div style="font-size:0.75rem; color:var(--p-surface-500); margin-top:2px;">
                                {{ n.mensagem }}
                            </div>
                        </div>
                        <span style="font-size:0.7rem; color:var(--p-surface-400); white-space:nowrap; margin-left:0.5rem;">
                            {{ tempoRelativo(n.criadoEm) }}
                        </span>
                    </div>

                    <div v-if="store.notificacoes.length === 0" style="padding:1.5rem; text-align:center; color:var(--p-surface-400); font-size:0.875rem;">
                        Nenhuma notificação
                    </div>
                </div>

                <!-- rodapé -->
                <div style="padding:0.5rem 1rem; border-top:1px solid var(--p-surface-200); text-align:center;">
                    <a href="#" style="font-size:0.8125rem; color:var(--p-primary-color); text-decoration:none;">
                        Ver todas
                    </a>
                </div>
            </div>
        </Popover>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import Popover from 'primevue/popover'
import { useNotificacaoStore } from '@/stores/useNotificacaoStore'

const store  = useNotificacaoStore()
const popover = ref()

function toggle(event) {
    popover.value.toggle(event)
}

function marcar(id) {
    store.marcarComoLida(id)
    popover.value.hide()
}

const cores = {
    info:    'var(--erp-badge-info)',
    sucesso: 'var(--erp-badge-sucesso)',
    alerta:  'var(--erp-badge-alerta)',
    erro:    'var(--erp-badge-erro)',
    confirm: 'var(--erp-badge-confirm)',
}

function corTipo(tipo) {
    return cores[tipo] ?? cores.info
}

function tempoRelativo(data) {
    const diff = Math.floor((Date.now() - new Date(data)) / 1000)
    if (diff < 60)         return 'agora'
    if (diff < 3600)       return `${Math.floor(diff / 60)}min`
    if (diff < 86400)      return `${Math.floor(diff / 3600)}h`
    return 'ontem'
}
</script>

<style>
.erp-header__icon-btn {
    position: relative;
    background: none;
    border: none;
    cursor: pointer;
    color: inherit;
    padding: 0.375rem;
    border-radius: 0.375rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s;
}
.erp-header__icon-btn:hover { background: rgba(0,0,0,0.06); }
.dark .erp-header__icon-btn:hover { background: rgba(255,255,255,0.08); }

.erp-notif-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    background: var(--erp-badge-erro);
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    min-width: 14px;
    height: 14px;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 3px;
    line-height: 1;
}

.erp-notif-limpar {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.75rem;
    color: var(--p-primary-color);
    padding: 0;
}

.erp-notif-item {
    display: flex;
    align-items: flex-start;
    gap: 0.625rem;
    padding: 0.625rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid var(--p-surface-100);
    transition: background 0.1s;
}
.erp-notif-item:hover { background: var(--p-surface-50); }
.erp-notif-item--lida { opacity: 0.55; }

.erp-notif-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 4px;
}
</style>
