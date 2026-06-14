<template>
    <header class="erp-header">
        <!-- Esquerda: toggle + breadcrumb -->
        <div style="display:flex; align-items:center; gap:0.75rem; flex:1; min-width:0;">
            <button type="button" class="erp-header__icon-btn" @click="themeStore.toggleSidebar()" aria-label="Alternar sidebar">
                <i :class="themeStore.sidebarCollapsed ? 'pi pi-arrow-right' : 'pi pi-arrow-left'" style="font-size:1rem;" />
            </button>

            <Breadcrumb :model="breadcrumbItems" style="background:none; border:none; padding:0; font-size:0.8125rem;">
                <template #item="{ item }">
                    <span style="color:var(--p-surface-500);">
                        <i v-if="item.icon" :class="item.icon" style="margin-right:0.25rem;" />
                        {{ item.label }}
                    </span>
                </template>
                <template #separator>/</template>
            </Breadcrumb>
        </div>

        <!-- Direita: dark toggle, notificações, usuário -->
        <div style="display:flex; align-items:center; gap:0.25rem;">
            <!-- Dark / light toggle -->
            <button type="button" class="erp-header__icon-btn" @click="themeStore.toggleDark()" :aria-label="themeStore.isDark ? 'Modo claro' : 'Modo escuro'">
                <i :class="themeStore.isDark ? 'pi pi-sun' : 'pi pi-moon'" style="font-size:1rem;" />
            </button>

            <!-- Notificações -->
            <AppNotificacoes />

            <!-- Menu do usuário -->
            <button type="button" class="erp-header__icon-btn erp-header__user-btn" @click="menuUsuario.toggle($event)" aria-label="Menu do usuário">
                <AppAvatar :nome="nomeUsuario" tamanho="sm" />
                <span class="erp-header__user-nome">{{ nomeUsuario }}</span>
                <i class="pi pi-chevron-down" style="font-size:0.6rem; opacity:0.6;" />
            </button>

            <Menu ref="menuUsuario" :model="itemsUsuario" :popup="true" />
        </div>
    </header>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { useThemeStore } from '@/stores/useThemeStore'
import AppNotificacoes from '@/Components/Layout/AppNotificacoes.vue'
import AppAvatar from '@/Components/UI/AppAvatar.vue'
import Breadcrumb from 'primevue/breadcrumb'
import Menu from 'primevue/menu'

const props = defineProps({
    breadcrumb: { type: Array, default: () => [] },
})

const themeStore = useThemeStore()
const page       = usePage()
const menuUsuario = ref()

const nomeUsuario = computed(() => page.props.auth?.user?.name ?? 'Usuário')

const breadcrumbItems = computed(() =>
    props.breadcrumb.length
        ? props.breadcrumb
        : [{ label: 'Início', icon: 'pi pi-home' }]
)

const itemsUsuario = [
    { label: 'Meu Perfil',    icon: 'pi pi-user',     command: () => {} },
    { label: 'Configurações', icon: 'pi pi-cog',      command: () => {} },
    { separator: true },
    { label: 'Sair',          icon: 'pi pi-sign-out', command: logout },
]

function logout() {
    router.post(route('logout'))
}
</script>

<style>
.erp-header__user-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.5rem;
}

.erp-header__user-nome {
    font-size: 0.8125rem;
    font-weight: 500;
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

@media (max-width: 640px) {
    .erp-header__user-nome { display: none; }
}
</style>
