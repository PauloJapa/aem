<template>
    <div class="erp-layout" :class="{ dark: themeStore.isDark }">
        <AppSidebar :collapsed="themeStore.sidebarCollapsed" />

        <div class="erp-main" :class="{ 'erp-main--collapsed': themeStore.sidebarCollapsed }">
            <AppHeader :breadcrumb="breadcrumb" />

            <main class="erp-content">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useThemeStore } from '@/stores/useThemeStore'
import AppSidebar from '@/Components/Layout/AppSidebar.vue'
import AppHeader from '@/Components/Layout/AppHeader.vue'

defineProps({
    breadcrumb: { type: Array, default: () => [] },
})

const themeStore = useThemeStore()

onMounted(() => {
    themeStore.init()
})
</script>
