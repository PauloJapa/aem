<template>
    <aside
        class="erp-sidebar"
        :class="{ 'erp-sidebar--collapsed': props.collapsed }"
        @click="props.collapsed && themeStore.toggleSidebar()"
    >
        <!-- Logo -->
        <div class="erp-sidebar__logo">
            <i class="pi pi-box" style="font-size:1.25rem; color:#5eead4; flex-shrink:0;" />
            <span class="erp-sidebar__label" style="margin-left:0.625rem; font-weight:700; font-size:1rem; letter-spacing:-0.01em;">
                ERP
            </span>
        </div>

        <!-- Navegação -->
        <nav class="erp-sidebar__nav">
            <template v-for="item in menu" :key="item.label">
                <!-- Item simples (sem filhos) -->
                <template v-if="!item.filhos">
                    <Link
                        v-tooltip.right="props.collapsed ? item.label : null"
                        :href="resolveHref(item.rota)"
                        class="erp-sidebar__item"
                        :class="{ 'erp-sidebar__item--active': isAtivo(item) }"
                    >
                        <i :class="item.icon" class="erp-sidebar__icon" />
                        <span class="erp-sidebar__label">{{ item.label }}</span>
                    </Link>
                </template>

                <!-- Item com submenu -->
                <template v-else>
                    <button
                        type="button"
                        v-tooltip.right="props.collapsed ? item.label : undefined"
                        class="erp-sidebar__item erp-sidebar__item--pai"
                        :class="{ 'erp-sidebar__item--active': isGrupoAtivo(item) }"
                        @click.stop="handlePaiClick(item.label)"
                    >
                        <i :class="item.icon" class="erp-sidebar__icon" />
                        <span class="erp-sidebar__label">{{ item.label }}</span>
                        <i
                            v-if="!props.collapsed"
                            class="pi erp-sidebar__chevron"
                            :class="grupoAberto === item.label ? 'pi-chevron-up' : 'pi-chevron-down'"
                        />
                    </button>

                    <div
                        v-show="!props.collapsed && grupoAberto === item.label"
                        class="erp-sidebar__sub"
                    >
                        <Link
                            v-for="filho in item.filhos"
                            :key="filho.label"
                            :href="resolveHref(filho.rota)"
                            class="erp-sidebar__item erp-sidebar__item--filho"
                            :class="{ 'erp-sidebar__item--active': isAtivo(filho) }"
                        >
                            <i :class="filho.icon" class="erp-sidebar__icon" style="font-size:0.8rem;" />
                            <span class="erp-sidebar__label">{{ filho.label }}</span>
                        </Link>
                    </div>
                </template>
            </template>
        </nav>

        <!-- Footer com usuário -->
        <div class="erp-sidebar__footer">
            <div style="display:flex; align-items:center; gap:0.625rem; overflow:hidden;">
                <AppAvatar :nome="nomeUsuario" tamanho="sm" />
                <span class="erp-sidebar__label" style="font-size:0.8125rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    {{ nomeUsuario }}
                </span>
            </div>
        </div>
    </aside>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useThemeStore } from '@/stores/useThemeStore'
import AppAvatar from '@/Components/UI/AppAvatar.vue'

const themeStore = useThemeStore()
const page = usePage()
const menu = computed(() => page.props.auth?.menu ?? [])
const nomeUsuario = computed(() => page.props.auth?.user?.name ?? 'Usuário')

const props = defineProps({
    collapsed: { type: Boolean, default: false },
})

const grupoAberto = ref(null)

function handlePaiClick(label) {
    if (props.collapsed) {
        // Expande o sidebar e já marca o grupo para abrir
        themeStore.toggleSidebar()
        grupoAberto.value = label
    } else {
        toggleGrupo(label)
    }
}

function toggleGrupo(label) {
    grupoAberto.value = grupoAberto.value === label ? null : label
}

function isAtivo(item) {
    if (!item.rota || item.rota === '#') return false
    try {
        // item.rota é nome de rota como "core.usuarios.index" → "/core/usuarios/index"
        // Compara com o início da URL atual
        const partes = item.rota.split('.')
        const path   = '/' + partes.slice(0, -1).join('/')
        return page.url.startsWith(path)
    } catch { return false }
}

function isGrupoAtivo(item) {
    return item.filhos?.some(f => isAtivo(f)) ?? false
}

function resolveHref(rota) {
    if (!rota || rota === '#') return '#'
    try { return route(rota) } catch { return '#' }
}
</script>

<style>
.erp-sidebar__item--pai {
    width: 100%;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    font-size: inherit;
    font-family: inherit;
}

.erp-sidebar__chevron {
    margin-left: auto;
    font-size: 0.65rem;
    opacity: 0.7;
    flex-shrink: 0;
}

.erp-sidebar__sub {
    background: rgba(0, 0, 0, 0.15);
}

.erp-sidebar__item--filho {
    padding-left: 2.25rem;
    font-size: 0.8125rem;
}

/* Submenu aberto/fechado */
.erp-sidebar__sub {
    transition: none;
}
</style>
