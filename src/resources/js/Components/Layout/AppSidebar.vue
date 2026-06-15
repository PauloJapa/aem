<template>
    <aside
        class="erp-sidebar"
        :class="{ 'erp-sidebar--collapsed': collapsed }"
        @click="collapsed && themeStore.toggleSidebar()"
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
                <!-- Item simples -->
                <template v-if="!item.filhos">
                    <Link
                        v-tooltip.right="collapsed ? item.label : null"
                        :href="item.rota === '#' ? '#' : route(item.rota)"
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
                        v-tooltip.right="collapsed ? item.label : null"
                        class="erp-sidebar__item erp-sidebar__item--pai"
                        :class="{ 'erp-sidebar__item--active': isGrupoAtivo(item) }"
                        @click="!collapsed && toggleGrupo(item.label)"
                    >
                        <i :class="item.icon" class="erp-sidebar__icon" />
                        <span class="erp-sidebar__label">{{ item.label }}</span>
                        <i
                            v-if="!collapsed"
                            class="pi erp-sidebar__chevron"
                            :class="grupoAberto === item.label ? 'pi-chevron-up' : 'pi-chevron-down'"
                        />
                    </button>

                    <Transition name="submenu">
                        <div v-if="!collapsed && grupoAberto === item.label" class="erp-sidebar__sub">
                            <Link
                                v-for="filho in item.filhos"
                                :key="filho.label"
                                :href="filho.rota === '#' ? '#' : route(filho.rota)"
                                class="erp-sidebar__item erp-sidebar__item--filho"
                                :class="{ 'erp-sidebar__item--active': isAtivo(filho) }"
                            >
                                <i :class="filho.icon" class="erp-sidebar__icon" style="font-size:0.8rem;" />
                                <span class="erp-sidebar__label">{{ filho.label }}</span>
                            </Link>
                        </div>
                    </Transition>
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
const menu = computed(() => usePage().props.auth?.menu ?? [])

defineProps({
    collapsed: { type: Boolean, default: false },
})

const page = usePage()
const nomeUsuario = computed(() => page.props.auth?.user?.name ?? 'Usuário')

const grupoAberto = ref(null)

function toggleGrupo(label) {
    grupoAberto.value = grupoAberto.value === label ? null : label
}

function isAtivo(item) {
    if (!item.rota || item.rota === '#') return false
    try { return page.url.startsWith('/' + item.rota.replace('.', '/')) } catch { return false }
}

function isGrupoAtivo(item) {
    return item.filhos?.some(f => isAtivo(f)) ?? false
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

/* Animação submenu */
.submenu-enter-active,
.submenu-leave-active {
    transition: max-height 0.2s ease, opacity 0.2s ease;
    overflow: hidden;
    max-height: 300px;
}
.submenu-enter-from,
.submenu-leave-to {
    max-height: 0;
    opacity: 0;
}
</style>
